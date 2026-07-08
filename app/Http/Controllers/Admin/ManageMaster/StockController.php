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
        $user = auth()->user();
        $products = Product::with('merek')->orderBy('name')->get();

        $userWarehouseIds = $user->warehouses->pluck('id')->toArray();

        $warehouses = $user->warehouses->isNotEmpty()
            ? $user->warehouses
            : \App\Models\Warehouse::orderBy('type')->orderBy('name')->get();

        $batches = ProductBatch::withSum('transactionItems', 'qty')
            ->withSum('supplierReturnItems', 'qty')
            ->when($userWarehouseIds, function ($q) use ($userWarehouseIds) {
                $q->whereIn('warehouse_id', $userWarehouseIds);
            })
            ->get();

        $totalSku = $batches->unique('product_id')->count();
        $totalUnits = $batches->sum(function ($b) {
            return max(0, (int)$b->qty - (int)($b->transaction_items_sum_qty ?? 0) - (int)($b->supplier_return_items_sum_qty ?? 0));
        });
        $lowStockCount = $batches->filter(function ($b) {
            $cur = (int)$b->qty - (int)($b->transaction_items_sum_qty ?? 0) - (int)($b->supplier_return_items_sum_qty ?? 0);
            return $cur > 0 && $cur < 10;
        })->count();
        $nearExpiredCount = $batches->filter(function ($b) {
            return $b->expiry_date && $b->expiry_date->isFuture() && $b->expiry_date->lte(now()->addDays(30));
        })->count();

        return view('admin.manage_master.stock.index', compact('products', 'warehouses', 'totalSku', 'totalUnits', 'lowStockCount', 'nearExpiredCount'))->with('sb', 'Stock');
    }

    public function getall(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $user = auth()->user();
        $userWarehouseIds = $user->warehouses->pluck('id')->toArray();

        $batches = ProductBatch::query()
            ->when($userWarehouseIds, function ($q) use ($userWarehouseIds) {
                $q->whereIn('product_batches.warehouse_id', $userWarehouseIds);
            })
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->join('warehouses', 'product_batches.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('product_variants', 'product_batches.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->select('product_batches.product_id', 'product_batches.product_variant_id', 'product_batches.warehouse_id')
            ->selectRaw('MAX(products.name) as p_name, MAX(merek.name) as m_name, MAX(warehouses.name) as w_name')
            ->selectRaw('MAX(product_nettos.netto_value) as netto_value, MAX(product_nettos.satuan) as satuan')
            ->selectRaw('SUM(product_batches.qty) as initial_qty')
            ->selectRaw('COUNT(product_batches.id) as aggregated_batch_count')
            ->selectRaw('MIN(product_batches.expiry_date) as nearest_expiry')
            ->selectRaw('COALESCE(AVG(product_batches.buy_price), 0) as avg_buy_price')
            ->selectRaw('SUM((SELECT COALESCE(SUM(qty), 0) FROM transaction_items WHERE product_batch_id = product_batches.id)) as sold_qty')
            ->selectRaw('SUM((SELECT COALESCE(SUM(qty), 0) FROM supplier_return_items WHERE product_batch_id = product_batches.id)) as returned_qty')
            ->selectRaw('MAX(products.min_stock_alert) as min_stock_alert')
            ->groupBy(
                'product_batches.product_id',
                'product_batches.product_variant_id',
                'product_batches.warehouse_id'
            )
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where('product_batches.warehouse_id', $warehouseId);
            });

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
            ->addColumn('batch_count', function($row) {
                return $row->aggregated_batch_count;
            })
            ->addColumn('netto', function ($row) {
                return $row->netto_value ? $row->netto_value . $row->satuan : '-';
            })
            ->addColumn('total_current_stock', function ($row) {
                return (int)($row->initial_qty - $row->sold_qty - $row->returned_qty);
            })
            ->addColumn('stock_status', function ($row) {
                $stock = (int)($row->initial_qty - $row->sold_qty - $row->returned_qty);
                if ($stock <= 0) return 'habis';
                $min = max(1, (int)($row->min_stock_alert ?? 10));
                if ($stock < $min) return 'kritis';
                if ($stock < $min * 3) return 'menipis';
                return 'aman';
            })
            ->addColumn('stock_value', function ($row) {
                $stock = (int)($row->initial_qty - $row->sold_qty - $row->returned_qty);
                return $stock * (float)$row->avg_buy_price;
            })
            ->addColumn('expiry_info', function ($row) {
                return $row->nearest_expiry ? $row->nearest_expiry : null;
            })
            ->addColumn('action', function ($row) {
                $url = url('admin/manage-master/stock/detail-audit') . '?product_id=' . $row->product_id . '&variant_id=' . $row->product_variant_id . '&warehouse_id=' . $row->warehouse_id;
                return '
                    <a href="' . $url . '" class="btn btn-detail-icon" title="Lihat Detail">
                        <i class="fas fa-chevron-right"></i>
                    </a>
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
        try {
            file_put_contents(storage_path('logs/debug_delete.txt'), "Trying to delete batch ID: " . $request->id . "\n", FILE_APPEND);
            $batch = ProductBatch::findOrFail($request->id);
            
            if ($batch->transactionItems()->count() > 0) {
                return response()->json(['success' => false, 'message' => 'Batch tidak dapat dihapus karena sudah memiliki riwayat penjualan'], 422);
            }
            if (method_exists($batch, 'supplierReturnItems') && $batch->supplierReturnItems()->count() > 0) {
                return response()->json(['success' => false, 'message' => 'Batch tidak dapat dihapus karena sudah memiliki riwayat return supplier'], 422);
            }
            
            $batch->delete();
            file_put_contents(storage_path('logs/debug_delete.txt'), "Batch ID " . $request->id . " successfully deleted.\n", FILE_APPEND);
            return response()->json(['success' => true, 'message' => 'Batch berhasil dihapus']);
        } catch (\Illuminate\Database\QueryException $e) {
            file_put_contents(storage_path('logs/debug_delete.txt'), "QueryException ID " . $request->id . ": " . $e->getMessage() . "\n", FILE_APPEND);
            return response()->json(['success' => false, 'message' => 'Query Error: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            file_put_contents(storage_path('logs/debug_delete.txt'), "Exception ID " . $request->id . ": " . $e->getMessage() . "\n", FILE_APPEND);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function addNetto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'   => 'required|exists:products,id',
            'variant_id'   => 'required|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Assign variant ke semua batch produk ini di warehouse ini yang belum punya variant
        $updated = ProductBatch::where('product_id', $request->product_id)
            ->where('warehouse_id', $request->warehouse_id)
            ->whereNull('product_variant_id')
            ->update(['product_variant_id' => $request->variant_id]);

        return response()->json(['success' => true, 'message' => "Varian berhasil dihubungkan ke {$updated} batch"]);
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
            'variant_id'     => 'required|exists:product_variants,id',
            'new_variant_id' => 'required|exists:product_variants,id',
            'product_id'     => 'required|exists:products,id',
            'warehouse_id'   => 'required|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Update semua batch produk ini di warehouse ini yang punya variant lama ke variant baru
        $updated = ProductBatch::where('product_id', $request->product_id)
            ->where('warehouse_id', $request->warehouse_id)
            ->where('product_variant_id', $request->variant_id)
            ->update(['product_variant_id' => $request->new_variant_id]);

        return response()->json(['success' => true, 'message' => "Varian berhasil diperbarui pada {$updated} batch"]);
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

        // Safety check
        if (!$productId || !$warehouseId) {
            return response()->json(['success' => false, 'message' => 'ID Produk atau Gudang tidak valid']);
        }

        // 1. Get All Batches for this group
        $batchesQuery = ProductBatch::with(['variant.netto'])
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($variantId && $variantId !== 'null' && $variantId !== '') {
            $batchesQuery->where('product_variant_id', $variantId);
        } else {
            $batchesQuery->whereNull('product_variant_id');
        }

        $batches = $batchesQuery->get()
            ->map(function($batch) {
                $sold = $batch->transactionItems()->sum('qty');
                $returned = $batch->supplierReturnItems()->sum('qty');
                $batch->current_qty = $batch->qty - $sold - $returned;
                return $batch;
            });

        $product = Product::with('merek')->find($productId);
        $warehouse = \App\Models\Warehouse::find($warehouseId);

        if (!$product || !$warehouse) {
            return response()->json(['success' => false, 'message' => 'Produk atau Gudang tidak ditemukan']);
        }
        
        $batchIds = $batches->pluck('id');

        // 2. Incoming History (Supplier + Mutasi Masuk)
        $batchNos = $batches->pluck('batch_no');
        
        $fromSupplier = \App\Models\GoodsReceipt::with(['purchaseOrder', 'supplier'])
            ->whereIn('delivery_note_number', $batchNos)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'Supplier',
                    'ref_no' => $item->sj_number ?? $item->delivery_note_number,
                    'source' => $item->supplier ? $item->supplier->name : '-',
                    'date' => $item->received_date ? $item->received_date->format('Y-m-d') : '-',
                    'print_url' => null
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
                    'date' => $item->stockMovement->received_at ? $item->stockMovement->received_at->format('Y-m-d') : '-',
                    'print_url' => null
                ];
            });

        $incoming = $fromSupplier->concat($fromMovement)->sortByDesc('date')->values();

        // 3. Outgoing History (Transactions + Stock Movements)
        $transactions = \App\Models\TransactionItem::with(['transaction.customer', 'batch'])
            ->whereIn('product_batch_id', $batchIds)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->transaction_id,
                    'type' => 'Penjualan',
                    'ref_no' => $item->transaction->invoice_number ?? $item->transaction->transaction_code ?? '-',
                    'destination' => $item->transaction->customer ? $item->transaction->customer->name : 'Customer Umum',
                    'qty' => $item->qty,
                    'date' => $item->transaction->transaction_date ? $item->transaction->transaction_date->format('Y-m-d') : '-',
                    'batch_no' => $item->batch->batch_no ?? '-',
                    'print_url' => route('admin.sales.invoices.print', $item->transaction_id)
                ];
            });

        $movements = \App\Models\StockMovementItem::with(['stockMovement.toWarehouse', 'batch'])
            ->whereIn('product_batch_id', $batchIds)
            ->whereHas('stockMovement', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->stock_movement_id,
                    'type' => 'Mutasi Stok',
                    'ref_no' => $item->stockMovement->reference_number,
                    'destination' => 'Ke: ' . ($item->stockMovement->toWarehouse->name ?? '-'),
                    'qty' => $item->qty,
                    'date' => $item->stockMovement->created_at ? $item->stockMovement->created_at->format('Y-m-d H:i') : '-',
                    'batch_no' => $item->batch->batch_no ?? '-',
                    'print_url' => null // Add print route if available
                ];
            });

        $returns = \App\Models\SupplierReturnItem::with(['supplierReturn.supplier', 'batch'])
            ->whereIn('product_batch_id', $batchIds)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->supplier_return_id,
                    'type' => 'Return Supplier',
                    'ref_no' => $item->supplierReturn->return_number ?? '-',
                    'destination' => 'Ke: ' . ($item->supplierReturn->supplier->name ?? '-'),
                    'qty' => $item->qty,
                    'date' => $item->supplierReturn->return_date ? $item->supplierReturn->return_date->format('Y-m-d') : '-',
                    'batch_no' => $item->batch->batch_no ?? '-',
                    'print_url' => null
                ];
            });

        // Gabungkan dan urutkan berdasarkan tanggal terbaru
        $outgoing = $transactions->concat($movements)->concat($returns)->sortByDesc('date')->values();

        // Ambil info netto dari variant
        $nettoInfo = null;
        if ($variantId && $variantId !== 'null' && $variantId !== '') {
            $variant = \App\Models\ProductVariant::with('netto')->find($variantId);
            if ($variant && $variant->netto) {
                $nettoInfo = trim($variant->netto->netto_value . ' ' . ($variant->netto->satuan ?? ''));
            }
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id'        => $product->id,
                'name'      => ($product->merek ? $product->merek->name . ' ' : '') . $product->name,
                'warehouse' => $warehouse->name,
                'netto'     => $nettoInfo,
            ],
            'batches' => $batches,
            'incoming' => $incoming,
            'outgoing' => $outgoing
        ]);
    }

    public function detailAuditView(Request $request)
    {
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $warehouseId = $request->warehouse_id;

        return view('admin.manage_master.stock.detail', compact('productId', 'variantId', 'warehouseId'))->with('sb', 'Stock');
    }

    public function expired()
    {
        $user = auth()->user();
        $userWarehouseIds = $user->warehouses->pluck('id')->toArray();

        $batches = ProductBatch::with(['product.merek', 'variant', 'warehouse'])
            ->whereDate('expiry_date', '<=', now())
            ->where('qty', '>', 0)
            ->when($userWarehouseIds, function ($q) use ($userWarehouseIds) {
                $q->whereIn('warehouse_id', $userWarehouseIds);
            })
            ->orderBy('expiry_date')
            ->get();

        $userWarehouses = $user->warehouses;
        $warehouses = $userWarehouses->isNotEmpty()
            ? $userWarehouses
            : \App\Models\Warehouse::orderBy('type')->orderBy('name')->get();

        return view('admin.manage_master.stock.expired', compact('batches', 'warehouses'))->with('sb', 'Stock');
    }
}
