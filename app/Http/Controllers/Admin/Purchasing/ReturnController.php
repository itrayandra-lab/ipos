<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturnController extends Controller
{
    public function index()
    {
        return view('admin.purchasing.returns.index')->with('sb', 'ReturnToSupplier');
    }

    public function getall()
    {
        $returns = SupplierReturn::with(['supplier', 'warehouse', 'user'])->orderBy('return_date', 'desc');

        return DataTables::of($returns)
            ->addIndexColumn()
            ->addColumn('supplier_name', function ($ret) {
                return $ret->supplier ? $ret->supplier->name : '-';
            })
            ->addColumn('warehouse_name', function ($ret) {
                return $ret->warehouse ? $ret->warehouse->name : '-';
            })
            ->addColumn('user_name', function ($ret) {
                return $ret->user ? $ret->user->name : '-';
            })
            ->editColumn('return_date', function ($ret) {
                return $ret->return_date ? date('d/m/Y', strtotime($ret->return_date)) : '-';
            })
            ->editColumn('total_amount', function ($ret) {
                return number_format($ret->total_amount, 0, ',', '.');
            })
            ->addColumn('action', function ($ret) {
                return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="' . route('admin.purchasing.returns.show', $ret->id) . '" class="dropdown-item">Detail</a></li>
                            <li><a href="' . route('admin.purchasing.returns.edit', $ret->id) . '" class="dropdown-item">Edit</a></li>
                            <li><a href="' . route('admin.purchasing.returns.print', $ret->id) . '" target="_blank" class="dropdown-item">Cetak Nota</a></li>
                            <li><a href="' . route('admin.purchasing.returns.print_sj', $ret->id) . '" target="_blank" class="dropdown-item">Cetak Surat Jalan</a></li>
                            <li><a href="#" data-id="' . $ret->id . '" class="dropdown-item btn-delete text-danger">Hapus</a></li>
                        </ul>
                    </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = Warehouse::where('status', 'active')->get();
        return view('admin.purchasing.returns.create', compact('suppliers', 'warehouses'))->with('sb', 'ReturnToSupplier');
    }

    public function getBatches(Request $request)
    {
        $warehouse_id = $request->warehouse_id;

        $batches = ProductBatch::with(['product.merek', 'variant.netto'])
            ->where('warehouse_id', $warehouse_id)
            ->where('qty', '>', 0)
            ->get();

        return response()->json($batches);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            // Pre-load batches with relations to avoid N+1 and repeated heavy stock calculations
            $itemBatchIds = collect($request->items)->pluck('product_batch_id')->toArray();
            $batchData = ProductBatch::whereIn('id', $itemBatchIds)
                ->withSum('transactionItems', 'qty')
                ->withSum('supplierReturnItems', 'qty')
                ->get()
                ->keyBy('id');

            foreach ($request->items as $item) {
                $batch = $batchData[$item['product_batch_id']];
                if ($batch->current_stock < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk batch " . $batch->batch_no . ". Stok saat ini: " . $batch->current_stock);
                }
            }

            $txDate = $request->return_date ? Carbon::parse($request->return_date) : Carbon::now();
            
            // Generate Return Number: RT/BL/[RomanMonth]/[YY]/[MonthlySequence]
            $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $romanMonth = $romanMonths[$txDate->month - 1];
            $year = $txDate->format('y');

            $lastReturn = SupplierReturn::whereYear('return_date', $txDate->year)
                ->whereMonth('return_date', $txDate->month)
                ->orderBy('id', 'desc')
                ->first();
            
            $lastNumber = 0;
            if ($lastReturn && $lastReturn->return_number) {
                $parts = explode('/', $lastReturn->return_number);
                if (count($parts) === 5) {
                    $lastNumber = (int) $parts[4];
                }
            }
            
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $returnNumber = "RT/BL/{$romanMonth}/{$year}/{$newNumber}";

            $return = SupplierReturn::create([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'user_id' => Auth::id(),
                'return_number' => $returnNumber,
                'return_date' => $request->return_date,
                'total_amount' => 0, // No longer tracked by user
                'notes' => $request->notes,
                'status' => 'completed',
            ]);

            foreach ($request->items as $item) {
                $batch = $batchData[$item['product_batch_id']];
                
                SupplierReturnItem::create([
                    'supplier_return_id' => $return->id,
                    'product_id' => $batch->product_id,
                    'product_variant_id' => $batch->product_variant_id,
                    'product_batch_id' => $batch->id,
                    'qty' => $item['qty'],
                    'buy_price' => $batch->buy_price, 
                    'total' => $batch->buy_price * $item['qty'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success', 
                'message' => 'Return barang berhasil disimpan dan stok telah dikurangi', 
                'redirect' => route('admin.purchasing.returns.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $return = SupplierReturn::with(['items.product.merek', 'items.variant.netto', 'items.batch'])->findOrFail($id);
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = Warehouse::where('status', 'active')->get();
        return view('admin.purchasing.returns.edit', compact('return', 'suppliers', 'warehouses'))->with('sb', 'ReturnToSupplier');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_batch_id' => 'required|exists:product_batches,id',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $return = SupplierReturn::findOrFail($id);
            
            // Collect affected products for sync later
            $old_product_ids = $return->items->pluck('product_id')->toArray();

            // Pre-load batches
            $itemBatchIds = collect($request->items)->pluck('product_batch_id')->toArray();
            $batchData = ProductBatch::whereIn('id', $itemBatchIds)
                ->withSum('transactionItems', 'qty')
                ->withSum('supplierReturnItems', 'qty')
                ->get()
                ->keyBy('id');

            // Check stock availability for new/updated items
            foreach ($request->items as $item) {
                $batch = $batchData[$item['product_batch_id']];
                
                // Get old qty if this item already existed in this return
                $oldItem = $return->items()->where('product_batch_id', $item['product_batch_id'])->first();
                $oldQty = $oldItem ? $oldItem->qty : 0;
                
                // Effective stock = current stock + what was already returned in this transaction
                $effectiveStock = $batch->current_stock + $oldQty;

                if ($effectiveStock < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk batch " . $batch->batch_no . ". Stok tersedia: " . $effectiveStock);
                }
            }

            // Update main return record
            $return->update([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'return_date' => $request->return_date,
                'total_amount' => 0,
                'notes' => $request->notes,
            ]);

            // Hapus items lama
            $return->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $batch = $batchData[$item['product_batch_id']];
                
                SupplierReturnItem::create([
                    'supplier_return_id' => $return->id,
                    'product_id' => $batch->product_id,
                    'product_variant_id' => $batch->product_variant_id,
                    'product_batch_id' => $batch->id,
                    'qty' => $item['qty'],
                    'buy_price' => $batch->buy_price,
                    'total' => $batch->buy_price * $item['qty'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success', 
                'message' => 'Return barang berhasil diperbarui dan stok telah disinkronkan', 
                'redirect' => route('admin.purchasing.returns.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $return = SupplierReturn::with(['supplier', 'warehouse', 'user', 'items.product.merek', 'items.variant.netto', 'items.batch'])->findOrFail($id);
        return view('admin.purchasing.returns.show', compact('return'))->with('sb', 'ReturnToSupplier');
    }

    public function print($id)
    {
        $return = SupplierReturn::with(['user', 'supplier', 'warehouse', 'items.product.merek', 'items.batch.variant.netto'])
            ->findOrFail($id);
        $setting = StoreSetting::getActiveSetting();
        return view('admin.purchasing.returns.print', compact('return', 'setting'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $return = SupplierReturn::findOrFail($id);
            $return->delete();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Return barang berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus return: ' . $e->getMessage()], 500);
        }
    }

    public function printSJ($id)
    {
        $return = SupplierReturn::with(['user', 'supplier', 'warehouse', 'items.product.merek', 'items.batch.variant.netto'])
            ->findOrFail($id);
        $storeSetting = StoreSetting::getActiveSetting();
        return view('admin.purchasing.returns.print_surat_jalan', compact('return', 'storeSetting'));
    }
}
