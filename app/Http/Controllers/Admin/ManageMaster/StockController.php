<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::with('merek')->orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::orderBy('type')->orderBy('name')->get();
        return view('admin.manage_master.stock.index', compact('products', 'warehouses'))->with('sb', 'Stock');
    }

    public function getall(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        // Query dengan Join agar kolom nama bisa dicari oleh Datatables
        $batches = ProductBatch::query()
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->join('warehouses', 'product_batches.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('product_variants', 'product_batches.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->select('product_batches.product_id', 'product_batches.product_variant_id', 'product_batches.warehouse_id')
            ->selectRaw('products.name as p_name, merek.name as m_name, warehouses.name as w_name')
            ->selectRaw('product_nettos.netto_value, product_nettos.satuan')
            ->selectRaw('COUNT(*) as batch_count')
            ->selectRaw('SUM(product_batches.qty) as total_initial_qty')
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where('product_batches.warehouse_id', $warehouseId);
            })
            ->groupBy('product_batches.product_id', 'product_batches.product_variant_id', 'product_batches.warehouse_id', 
                      'products.name', 'merek.name', 'warehouses.name', 'product_nettos.netto_value', 'product_nettos.satuan');

        return DataTables::of($batches)
            ->addIndexColumn()
            ->addColumn('product_name', function ($row) {
                return ($row->m_name ? $row->m_name . ' ' : '') . $row->p_name;
            })
            ->filterColumn('product_name', function($query, $keyword) {
                $query->whereRaw("CONCAT(IFNULL(merek.name,''), ' ', products.name) LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('warehouse_name', function ($row) {
                return $row->w_name;
            })
            ->filterColumn('warehouse_name', function($query, $keyword) {
                $query->where('warehouses.name', 'LIKE', "%{$keyword}%");
            })
            ->addColumn('netto', function ($row) {
                return $row->netto_value ? $row->netto_value . $row->satuan : '-';
            })
            ->addColumn('total_current_stock', function ($row) {
                $batchIds = ProductBatch::where('product_id', $row->product_id)
                    ->where('product_variant_id', $row->product_variant_id)
                    ->where('warehouse_id', $row->warehouse_id)
                    ->pluck('id');
                
                $sold = \App\Models\TransactionItem::whereIn('product_batch_id', $batchIds)->sum('qty');
                return $row->total_initial_qty - $sold;
            })
            ->addColumn('action', function ($row) {
                return '
                    <button type="button" 
                        data-product_id="' . $row->product_id . '" 
                        data-variant_id="' . $row->product_variant_id . '" 
                        data-warehouse_id="' . $row->warehouse_id . '" 
                        class="btn btn-primary btn-sm btn-detail">
                        <i class="fas fa-eye"></i> Detail Audit
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'qty' => 'required|integer|min:1',
            'buy_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $warehouseId = $request->warehouse_id;
        if (!$warehouseId) {
            $mainWarehouse = \App\Models\Warehouse::where('type', 'main')->first();
            $warehouseId = $mainWarehouse ? $mainWarehouse->id : null;
        }

        ProductBatch::create(array_merge($request->all(), ['warehouse_id' => $warehouseId]));

        return response()->json(['success' => true, 'message' => 'Batch stok berhasil ditambahkan']);
    }

    public function get(Request $request)
    {
        $batch = ProductBatch::with(['product.merek', 'variant'])->findOrFail($request->id);
        return response()->json(['success' => true, 'data' => $batch]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:product_batches,id',
            'batch_no' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'qty' => 'required|integer|min:0',
            'buy_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $batch = ProductBatch::findOrFail($request->id);
        $batch->update($request->only(['batch_no', 'expiry_date', 'qty', 'buy_price']));

        return response()->json(['success' => true, 'message' => 'Data batch berhasil diperbarui']);
    }

    public function delete(Request $request)
    {
        $batch = ProductBatch::findOrFail($request->id);
        if ($batch->transactionItems()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Batch tidak dapat dihapus karena sudah digunakan'], 422);
        }
        $batch->delete();
        return response()->json(['success' => true, 'message' => 'Batch berhasil dihapus']);
    }

    public function addNetto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'   => 'required|exists:products,id',
            'netto_value'  => 'required|string|max:50',
            'satuan'       => 'nullable|string|max:50',
            'variant_name' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $product = Product::with('merek')->findOrFail($request->product_id);

        // Buat ProductNetto baru
        $netto = \App\Models\ProductNetto::create([
            'product_id'  => $product->id,
            'netto_value' => $request->netto_value,
            'satuan'      => $request->satuan,
        ]);

        // Generate SKU otomatis
        $merekCode    = $product->merek ? strtoupper(substr(preg_replace('/\s+/', '', $product->merek->name), 0, 3)) : 'PRD';
        $productCode  = strtoupper(substr(preg_replace('/\s+/', '', $product->name), 0, 3));
        $nettoCode    = strtoupper(preg_replace('/\s+/', '', $request->netto_value . ($request->satuan ?? '')));
        $baseSku      = $merekCode . '-' . $productCode . '-' . $nettoCode;
        $sku          = $baseSku;
        $counter      = 1;
        while (\App\Models\ProductVariant::where('sku_code', $sku)->exists()) {
            $sku = $baseSku . '-' . $counter++;
        }

        // Buat ProductVariant baru
        $variant = \App\Models\ProductVariant::create([
            'product_netto_id' => $netto->id,
            'variant_name'     => $request->variant_name,
            'sku_code'         => $sku,
            'price'            => $request->price,
        ]);

        // Assign variant ke semua batch produk ini di warehouse ini yang belum punya variant
        ProductBatch::where('product_id', $product->id)
            ->where('warehouse_id', $request->warehouse_id)
            ->whereNull('product_variant_id')
            ->update(['product_variant_id' => $variant->id]);

        return response()->json(['success' => true, 'message' => 'Netto berhasil ditambahkan dan dihubungkan ke batch']);
    }

    public function getNetto(Request $request)
    {
        $variant = ProductVariant::with('netto')->findOrFail($request->variant_id);
        return response()->json([
            'success' => true,
            'data' => [
                'variant_id'   => $variant->id,
                'variant_name' => $variant->variant_name,
                'sku_code'     => $variant->sku_code,
                'price'        => $variant->price,
                'netto_id'     => $variant->netto ? $variant->netto->id : null,
                'netto_value'  => $variant->netto ? $variant->netto->netto_value : '',
                'satuan'       => $variant->netto ? $variant->netto->satuan : '',
            ]
        ]);
    }

    public function updateNetto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_id'   => 'required|exists:product_variants,id',
            'variant_name' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'netto_value'  => 'required|string|max:50',
            'satuan'       => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $variant = ProductVariant::with('netto')->findOrFail($request->variant_id);

        // Update variant
        $variant->update([
            'variant_name' => $request->variant_name,
            'price'        => $request->price,
        ]);

        // Update netto
        if ($variant->netto) {
            $variant->netto->update([
                'netto_value' => $request->netto_value,
                'satuan'      => $request->satuan,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Netto & harga jual berhasil diperbarui']);
    }

    public function getVariants(Request $request)
    {
        $variants = ProductVariant::whereHas('netto', function ($q) use ($request) {
            $q->where('product_id', $request->product_id);
        })->with('netto')->get()->map(function ($variant) {
            return [
                'id' => $variant->id,
                'sku_code' => $variant->sku_code,
                'netto_value' => $variant->netto ? $variant->netto->netto_value : '-',
                'satuan' => $variant->netto ? $variant->netto->satuan : '',
            ];
        });
        return response()->json(['success' => true, 'data' => $variants]);
    }

    public function getDetail(Request $request)
    {
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $warehouseId = $request->warehouse_id;

        // 1. Get All Batches for this group
        $batches = ProductBatch::with(['variant.netto'])
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->map(function($batch) {
                $sold = $batch->transactionItems()->sum('qty');
                $batch->current_qty = $batch->qty - $sold;
                return $batch;
            });

        $product = Product::with('merek')->find($productId);
        $warehouse = \App\Models\Warehouse::find($warehouseId);
        
        $batchIds = $batches->pluck('id');

        // 2. Incoming History (Supplier + Mutasi Masuk)
        $batchNos = $batches->pluck('batch_no');
        
        $fromSupplier = \App\Models\GoodsReceipt::with(['purchaseOrder', 'supplier'])
            ->whereIn('delivery_note_number', $batchNos)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'Supplier',
                    'ref_no' => $item->delivery_note_number,
                    'source' => $item->supplier ? $item->supplier->name : '-',
                    'date' => $item->received_date
                ];
            });

        $fromMovement = \App\Models\StockMovementItem::with(['stockMovement.fromWarehouse'])
            ->whereIn('product_batch_id', $batchIds)
            ->whereHas('stockMovement', function($q) use ($warehouseId) {
                $q->where('to_warehouse_id', $warehouseId)
                  ->where('status', 'completed');
            })
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'Mutasi Masuk',
                    'ref_no' => $item->stockMovement->reference_number,
                    'source' => 'Dari: ' . ($item->stockMovement->fromWarehouse->name ?? '-'),
                    'date' => $item->stockMovement->received_at
                ];
            });

        $incoming = $fromSupplier->concat($fromMovement)->sortByDesc('date')->values();

        // 3. Outgoing History (Transactions + Stock Movements)
        $transactions = \App\Models\TransactionItem::with(['transaction.customer'])
            ->whereIn('product_batch_id', $batchIds)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'Penjualan',
                    'ref_no' => $item->transaction->invoice_number ?? $item->transaction->transaction_number ?? '-',
                    'destination' => $item->transaction->customer ? $item->transaction->customer->name : 'Customer Umum',
                    'qty' => $item->qty,
                    'date' => $item->transaction->created_at
                ];
            });

        $movements = \App\Models\StockMovementItem::with(['stockMovement.toWarehouse'])
            ->whereIn('product_batch_id', $batchIds)
            ->whereHas('stockMovement', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'Mutasi Stok',
                    'ref_no' => $item->stockMovement->reference_number,
                    'destination' => 'Ke: ' . ($item->stockMovement->toWarehouse->name ?? '-'),
                    'qty' => $item->qty,
                    'date' => $item->stockMovement->created_at
                ];
            });

        // Gabungkan dan urutkan berdasarkan tanggal terbaru
        $outgoing = $transactions->concat($movements)->sortByDesc('date')->values();

        return response()->json([
            'success' => true,
            'product' => [
                'id'        => $product->id,
                'name'      => ($product->merek ? $product->merek->name . ' ' : '') . $product->name,
                'warehouse' => $warehouse->name
            ],
            'batches' => $batches,
            'incoming' => $incoming,
            'outgoing' => $outgoing
        ]);
    }
}
