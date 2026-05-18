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
        $sales = BranchSale::with(['user', 'warehouse'])
            ->when($user->warehouse_id, fn($q) => $q->where('branch_warehouse_id', $user->warehouse_id))
            ->orderByDesc('sale_date');

        return DataTables::of($sales)
            ->addIndexColumn()
            ->addColumn('branch_name', fn($s) => $s->warehouse->name ?? '-')
            ->editColumn('sale_date', fn($s) => $s->sale_date->format('d/m/Y'))
            ->editColumn('total_amount', fn($s) => 'Rp ' . number_format($s->total_amount, 0, ',', '.'))
            ->addColumn('total_items', fn($s) => $s->items()->count() . ' item')
            ->addColumn('action', fn($s) => '
                <div class="dropdown d-inline">
                    <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                        Aksi
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="' . route('branch.sales.show', $s->id) . '"><i class="fas fa-eye text-info mr-2"></i> Detail</a>
                        <a class="dropdown-item" href="' . route('branch.sales.edit', $s->id) . '"><i class="fas fa-edit text-warning mr-2"></i> Edit</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger btn-delete" href="javascript:void(0)" data-id="' . $s->id . '"><i class="fas fa-trash text-danger mr-2"></i> Hapus</a>
                    </div>
                </div>
            ')
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $channels = \App\Models\ChannelSetting::all();

        if ($user->isSuperAdmin() || $user->isStoreManager()) {
            $warehouses = \App\Models\Warehouse::where('status', 'active')->orderBy('name')->get();
        } else {
            $warehouses = $user->warehouses()->where('status', 'active')->orderBy('name')->get();
        }

        if ($warehouses->isEmpty()) {
            return view('branch.sales.create', compact('warehouses', 'channels'))
                ->with('variants', [])
                ->with('batchesByVariant', [])
                ->with('sb', 'BranchSale');
        }

        $warehouseId = $request->query('warehouse_id', $warehouses->first()->id);

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
            ->values()
            ->toArray();

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
            ]))
            ->toArray();

        return view('branch.sales.create', compact('warehouses', 'channels', 'variants', 'batchesByVariant'))->with('sb', 'BranchSale');
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
            'source'                         => 'nullable|string|max:50',
            'warehouse_id'                   => 'nullable|exists:warehouses,id',
            'customer_name'                  => 'nullable|string|max:200',
            'external_order_id'              => 'nullable|string|max:100',
            'notes'                          => 'nullable|string',
            'payment_receipt'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
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

            $receiptPath = null;
            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                $filename = 'branch_sale_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/receipts'), $filename);
                $receiptPath = 'uploads/receipts/' . $filename;
            }

            $warehouseId = $request->warehouse_id ?? $warehouse->id;
            $warehouseModel = \App\Models\Warehouse::find($warehouseId);
            $warehouseCode = $warehouseModel ? ($warehouseModel->code ?? '') : '';

            $sale = BranchSale::create([
                'reference_number'   => BranchSale::generateReferenceNumber($warehouseId, $warehouseCode),
                'branch_warehouse_id' => $warehouseId,
                'user_id'            => $user->id,
                'sale_date'          => $request->sale_date,
                'source'             => $request->source,
                'customer_name'      => $request->customer_name,
                'external_order_id'  => $request->external_order_id,
                'notes'              => $request->notes,
                'payment_receipt'    => $receiptPath,
                'total_amount'       => $totalAmount,
            ]);

            $source = $request->source ?: 'branch';
            $customerName = $request->customer_name ?: ($request->source ? 'Customer ' . ucfirst($request->source) : 'Branch Customer');

            // Sync ke tabel transaksi pusat untuk laporan & dashboard
            $transaction = Transaction::create([
                'transaction_code' => $sale->reference_number,
                'transaction_type' => 'produk',
                'user_id'          => $user->id,
                'warehouse_id'     => $warehouseId,
                'customer_name'    => $customerName,
                'source'           => $source,
                'notes'            => $request->notes,
                'total_amount'     => $totalAmount,
                'payment_status'   => 'paid',
                'payment_method'   => 'cash',
                'payment_receipt'  => $receiptPath,
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

    public function edit($id)
    {
        $user = Auth::user();
        $channels = \App\Models\ChannelSetting::all();

        if ($user->isSuperAdmin() || $user->isStoreManager()) {
            $warehouses = \App\Models\Warehouse::where('status', 'active')->orderBy('name')->get();
        } else {
            $warehouses = $user->warehouses()->where('status', 'active')->orderBy('name')->get();
        }

        $sale = BranchSale::with([
            'items.product.merek',
            'items.variant.netto',
            'items.batch',
            'warehouse',
        ])->findOrFail($id);

        if ($warehouses->isEmpty()) {
            return view('branch.sales.edit', compact('sale', 'warehouses', 'channels'))
                ->with('variants', [])
                ->with('batchesByVariant', [])
                ->with('sb', 'BranchSale');
        }

        $warehouseId = $sale->branch_warehouse_id;

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
            ->values()
            ->toArray();

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
            ]))
            ->toArray();

        $existingItems = $sale->items->map(fn($i) => [
            'product_variant_id' => $i->product_variant_id,
            'product_id'         => $i->product_id,
            'qty_sold'           => $i->qty_sold,
            'sell_price'         => $i->sell_price,
        ])->toArray();

        return view('branch.sales.edit', compact('sale', 'warehouses', 'channels', 'variants', 'batchesByVariant', 'existingItems'))->with('sb', 'BranchSale');
    }

    public function update(Request $request, $id)
    {
        $user      = Auth::user();
        $warehouse = $user->warehouse;
        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum memiliki cabang aktif.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'sale_date'                      => 'required|date',
            'source'                         => 'nullable|string|max:50',
            'warehouse_id'                   => 'nullable|exists:warehouses,id',
            'customer_name'                  => 'nullable|string|max:200',
            'external_order_id'              => 'nullable|string|max:100',
            'notes'                          => 'nullable|string',
            'payment_receipt'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
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

            $sale = BranchSale::with('items')->findOrFail($id);

            // 1. Restore old stock
            foreach ($sale->items as $item) {
                if ($item->product_batch_id) {
                    $batch = ProductBatch::find($item->product_batch_id);
                    if ($batch) {
                        $batch->increment('qty', $item->qty_sold);
                    }
                }
            }

            // 2. Delete old items & transaction
            $sale->items()->delete();
            Transaction::where('transaction_code', $sale->reference_number)
                ->where('source', 'branch')
                ->each(function ($transaction) {
                    $transaction->items()->delete();
                    $transaction->delete();
                });

            // 3. Calculate new total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['qty_sold'] * $item['sell_price'];
            }

            $receiptPath = $sale->payment_receipt;
            if ($request->hasFile('payment_receipt')) {
                if ($receiptPath && \Illuminate\Support\Facades\File::exists(public_path($receiptPath))) {
                    \Illuminate\Support\Facades\File::delete(public_path($receiptPath));
                }
                $file = $request->file('payment_receipt');
                $filename = 'branch_sale_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/receipts'), $filename);
                $receiptPath = 'uploads/receipts/' . $filename;
            }

            $warehouseId = $request->warehouse_id ?? $sale->branch_warehouse_id;

            // 4. Update sale record
            $sale->update([
                'sale_date'          => $request->sale_date,
                'branch_warehouse_id' => $warehouseId,
                'source'             => $request->source,
                'customer_name'      => $request->customer_name,
                'external_order_id'  => $request->external_order_id,
                'notes'              => $request->notes,
                'payment_receipt'    => $receiptPath,
                'total_amount'       => $totalAmount,
            ]);

            $source = $request->source ?: 'branch';
            $customerName = $request->customer_name ?: ($request->source ? 'Customer ' . ucfirst($request->source) : 'Branch Customer');

            // 5. Re-create transaction
            $transaction = Transaction::create([
                'transaction_code' => $sale->reference_number,
                'transaction_type' => 'produk',
                'user_id'          => $user->id,
                'warehouse_id'     => $warehouseId,
                'customer_name'    => $customerName,
                'source'           => $source,
                'notes'            => $request->notes,
                'total_amount'     => $totalAmount,
                'payment_status'   => 'paid',
                'payment_method'   => 'cash',
                'payment_receipt'  => $receiptPath,
                'created_at'       => $request->sale_date . ' ' . now()->format('H:i:s'),
            ]);

            // 6. Re-create items & deduct stock
            foreach ($request->items as $item) {
                $qtyNeeded = $item['qty_sold'];

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
                'message'  => 'Penjualan harian berhasil diperbarui',
                'redirect' => route('branch.sales.show', $sale->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse;

        $sale = BranchSale::with('items')
            ->when($warehouse, fn($q) => $q->where('branch_warehouse_id', $warehouse->id))
            ->findOrFail($id);

        try {
            DB::transaction(function () use ($sale) {
                foreach ($sale->items as $item) {
                    if ($item->product_batch_id) {
                        $batch = ProductBatch::find($item->product_batch_id);
                        if ($batch) {
                            $batch->increment('qty', $item->qty_sold);
                        }
                    }
                }

                $sale->items()->delete();

                Transaction::where('transaction_code', $sale->reference_number)
                    ->where('source', 'branch')
                    ->each(function ($transaction) {
                        $transaction->items()->delete();
                        $transaction->delete();
                    });

                $sale->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Penjualan berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
