<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchSale;
use App\Models\BranchSaleItem;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BranchSaleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->warehouse_id) {
            $count = $user->warehouses->count();
            if ($count === 1) {
                $user->update(['warehouse_id' => $user->warehouses->first()->id]);
            } elseif ($count > 1) {
                return redirect()->route('branch.dashboard')->with('error', 'Pilih cabang terlebih dahulu.');
            }
        }
        $warehouse = $user->warehouse;
        return view('branch.sales.index', compact('warehouse'))->with('sb', 'BranchSale');
    }

    public function getall()
    {
        $user = Auth::user();
        $sales = BranchSale::with('user')
            ->when($user->warehouse_id, fn($q) => $q->where('branch_warehouse_id', $user->warehouse_id))
            ->orderByDesc('sale_date');

        return DataTables::of($sales)
            ->addIndexColumn()
            ->editColumn('sale_date', fn($s) => $s->sale_date->format('d/m/Y'))
            ->editColumn('total_amount', fn($s) => 'Rp ' . number_format($s->total_amount, 0, ',', '.'))
            ->addColumn('total_items', fn($s) => $s->items()->count() . ' item')
            ->addColumn('action', fn($s) => '
                <a href="' . route('branch.sales.show', $s->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
            ')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $warehouse = Auth::user()->warehouse;

        if (!$warehouse) {
            return view('branch.sales.create', compact('warehouse'))
                ->with('variants', collect())
                ->with('batchesByVariant', collect())
                ->with('sb', 'BranchSale');
        }

        $warehouseId = $warehouse->id;

        $variants = ProductVariant::with(['netto.product.merek'])
            ->whereHas('netto.product.batches', fn($q) => $q->where('warehouse_id', $warehouseId)->where('qty', '>', 0))
            ->get()
            ->map(fn($v) => [
                'id'         => $v->id,
                'product_id' => $v->netto->product_id,
                'label'      => trim(implode(' ', array_filter([
                    $v->netto->product->merek->name ?? '',
                    $v->netto->product->name ?? '',
                    trim(($v->netto->netto_value ?? '') . ' ' . ($v->netto->satuan ?? '')),
                ]))),
                'selling_price' => $v->getSellingPrice(),
            ])
            ->values();

        $batchesByVariant = ProductBatch::where('warehouse_id', $warehouseId)
            ->where('qty', '>', 0)
            ->whereHas('variant.netto.product')
            ->orderBy('product_variant_id')
            ->orderBy('expiry_date')
            ->orderBy('created_at')
            ->get(['id', 'product_variant_id', 'batch_no', 'qty', 'expiry_date'])
            ->groupBy('product_variant_id')
            ->map(fn($batches) => $batches->map(fn($b) => [
                'id'          => $b->id,
                'batch_no'    => $b->batch_no,
                'qty'         => $b->qty,
                'expiry_date' => $b->expiry_date?->format('d/m/Y'),
            ]));

        return view('branch.sales.create', compact('warehouse', 'variants', 'batchesByVariant'))->with('sb', 'BranchSale');
    }

    public function store(Request $request)
    {
        $user      = Auth::user();
        $warehouse = $user->warehouse;
        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum memiliki cabang aktif.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'sale_date'                      => 'required|date',
            'notes'                          => 'nullable|string',
            'items'                          => 'required|array|min:1',
            'items.*.product_variant_id'     => 'required|exists:product_variants,id',
            'items.*.product_id'             => 'required|exists:products,id',
            'items.*.qty_sold'               => 'required|integer|min:1',
            'items.*.sell_price'             => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['qty_sold'] * $item['sell_price'];
            }

            $sale = BranchSale::create([
                'reference_number'   => BranchSale::generateReferenceNumber(),
                'branch_warehouse_id' => $warehouse->id,
                'user_id'            => $user->id,
                'sale_date'          => $request->sale_date,
                'notes'              => $request->notes,
                'total_amount'       => $totalAmount,
            ]);

            // Sync ke tabel transaksi pusat untuk laporan & dashboard
            $transaction = Transaction::create([
                'transaction_code' => $sale->reference_number,
                'transaction_type' => 'produk',
                'user_id'          => $user->id,
                'warehouse_id'     => $warehouse->id,
                'customer_name'    => 'Branch Customer',
                'source'           => 'branch',
                'notes'            => $request->notes,
                'total_amount'     => $totalAmount,
                'payment_status'   => 'paid',
                'payment_method'   => 'cash',
                'created_at'       => $request->sale_date . ' ' . now()->format('H:i:s'),
            ]);

            foreach ($request->items as $item) {
                $qtyNeeded = $item['qty_sold'];

                // Ambil batch FEFO (expiry_date ASC, null expiry last)
                $batches = ProductBatch::where('warehouse_id', $warehouse->id)
                    ->where('product_id', $item['product_id'])
                    ->where('product_variant_id', $item['product_variant_id'])
                    ->where('qty', '>', 0)
                    ->orderByRaw('COALESCE(expiry_date, ?) ASC', ['9999-12-31'])
                    ->orderBy('created_at')
                    ->get();

                $totalAvailable = $batches->sum('qty');
                if ($totalAvailable < $qtyNeeded) {
                    throw new \Exception("Stok tidak cukup untuk {$item['product_id']}. Tersedia: {$totalAvailable}");
                }

                foreach ($batches as $batch) {
                    if ($qtyNeeded <= 0) break;
                    $take = min($batch->qty, $qtyNeeded);

                    BranchSaleItem::create([
                        'branch_sale_id'     => $sale->id,
                        'product_id'         => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'],
                        'product_batch_id'   => $batch->id,
                        'qty_sold'           => $take,
                        'sell_price'         => $item['sell_price'],
                        'subtotal'           => $take * $item['sell_price'],
                    ]);

                    TransactionItem::create([
                        'transaction_id'     => $transaction->id,
                        'product_id'         => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'],
                        'product_batch_id'   => $batch->id,
                        'qty'                => $take,
                        'price'              => $item['sell_price'],
                        'subtotal'           => $take * $item['sell_price'],
                    ]);

                    $batch->decrement('qty', $take);
                    $qtyNeeded -= $take;
                }
            }

            DB::commit();
            return response()->json([
                'status'   => 'success',
                'message'  => 'Penjualan harian berhasil disimpan',
                'redirect' => route('branch.sales.show', $sale->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $warehouse = Auth::user()->warehouse;
        $sale      = BranchSale::with([
            'items.product.merek',
            'items.variant.netto',
            'items.batch',
            'user',
            'warehouse',
        ])->when($warehouse, fn($q) => $q->where('branch_warehouse_id', $warehouse->id))
            ->findOrFail($id);

        return view('branch.sales.show', compact('sale', 'warehouse'))->with('sb', 'BranchSale');
    }
}
