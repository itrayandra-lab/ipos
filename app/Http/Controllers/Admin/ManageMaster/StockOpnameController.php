<?php

namespace App\Http\Controllers\Admin\ManageMaster;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\SupplierReturnItem;
use App\Models\TransactionItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockOpnameController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (in_array($request->route()->getActionMethod(), ['approve', 'reject'])) {
                if (!auth()->user()->hasPermission('approve_stock_opname')) {
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk approve opname'], 403);
                    }
                    return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk approve opname');
                }
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = auth()->user();
        $userWarehouseIds = $user->warehouses->pluck('id')->toArray();

        $opnames = StockOpname::with(['warehouse', 'creator', 'approver', 'items'])
            ->when($userWarehouseIds, function ($q) use ($userWarehouseIds) {
                $q->whereIn('warehouse_id', $userWarehouseIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.manage_master.stock_opname.index', compact('opnames'))->with('sb', 'StockOpname');
    }

    public function create()
    {
        $user = auth()->user();
        $warehouses = $user->warehouses->isNotEmpty()
            ? $user->warehouses
            : Warehouse::orderBy('type')->orderBy('name')->get();

        return view('admin.manage_master.stock_opname.create', compact('warehouses'))->with('sb', 'StockOpname');
    }

    public function getProducts(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        if (!$warehouseId) {
            return response()->json(['success' => false, 'message' => 'Pilih gudang terlebih dahulu']);
        }

        $products = ProductBatch::selectRaw(
            'product_batches.product_id,
             product_batches.product_variant_id,
             MAX(products.name) as product_name,
             MAX(merek.name) as merek_name,
             MAX(product_variants.variant_name) as variant_name,
             MAX(product_variants.product_hpp) as product_hpp,
             MAX(product_nettos.netto_value) as netto_value,
             MAX(product_nettos.satuan) as netto_satuan,
             SUM(product_batches.qty) as total_qty'
        )
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->leftJoin('merek', 'products.merek_id', '=', 'merek.id')
            ->leftJoin('product_variants', 'product_batches.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('product_nettos', 'product_variants.product_netto_id', '=', 'product_nettos.id')
            ->where('product_batches.warehouse_id', $warehouseId)
            ->where('product_batches.qty', '>', 0)
            ->groupBy('product_batches.product_id', 'product_batches.product_variant_id')
            ->orderBy('product_name')
            ->get();

        $soldQty = TransactionItem::selectRaw('product_id, product_variant_id, SUM(qty) as total_sold')
            ->whereHas('batch', fn($q) => $q->where('warehouse_id', $warehouseId))
            ->groupBy('product_id', 'product_variant_id')
            ->get()
            ->keyBy(fn($item) => $item->product_id . '-' . ($item->product_variant_id ?? '0'));

        $returnedQty = SupplierReturnItem::selectRaw('product_id, product_variant_id, SUM(qty) as total_returned')
            ->whereHas('batch', fn($q) => $q->where('warehouse_id', $warehouseId))
            ->groupBy('product_id', 'product_variant_id')
            ->get()
            ->keyBy(fn($item) => $item->product_id . '-' . ($item->product_variant_id ?? '0'));

        $products = $products->map(function ($row) use ($soldQty, $returnedQty) {
            $key = $row->product_id . '-' . ($row->product_variant_id ?? '0');
            $sold = (int)($soldQty[$key]->total_sold ?? 0);
            $returned = (int)($returnedQty[$key]->total_returned ?? 0);
            $row->system_qty = max(0, (int)($row->total_qty - $sold - $returned));
            $row->display_name = ($row->merek_name ? $row->merek_name . ' ' : '') . $row->product_name;
            $row->netto_label = $row->netto_value ? $row->netto_value . ($row->netto_satuan ?? '') : '-';
            return $row;
        });

        return response()->json(['success' => true, 'data' => $products->values()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.system_qty' => 'required|integer|min:0',
            'items.*.physical_qty' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'reference_number' => StockOpname::generateReferenceNumber(),
                'warehouse_id' => $request->warehouse_id,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => $user->id,
            ]);

            foreach ($request->items as $item) {
                $diff = (int)$item['physical_qty'] - (int)$item['system_qty'];

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?: null,
                    'system_qty' => (int)$item['system_qty'],
                    'physical_qty' => (int)$item['physical_qty'],
                    'difference' => $diff,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Opname stok berhasil disimpan sebagai draft, menunggu persetujuan.',
                'data' => ['reference_number' => $opname->reference_number, 'id' => $opname->id]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $opname = StockOpname::with(['warehouse', 'creator', 'approver', 'items.product', 'items.variant'])->findOrFail($id);
        return view('admin.manage_master.stock_opname.show', compact('opname'))->with('sb', 'StockOpname');
    }

    public function approve($id)
    {
        $opname = StockOpname::with('items')->findOrFail($id);

        if ($opname->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Opname sudah ' . $opname->status]);
        }

        $user = auth()->user();

        DB::beginTransaction();
        try {
            foreach ($opname->items as $item) {
                if ($item->difference == 0) continue;

                if ($item->difference > 0) {
                    $hpp = 0;
                    if ($item->product_variant_id) {
                        $variant = ProductVariant::find($item->product_variant_id);
                        $hpp = $variant && $variant->product_hpp ? (int)$variant->product_hpp : 0;
                    } else {
                        $lastBatch = ProductBatch::where('product_id', $item->product_id)
                            ->whereNull('product_variant_id')
                            ->where('warehouse_id', $opname->warehouse_id)
                            ->where('buy_price', '>', 0)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        $hpp = $lastBatch ? (int)$lastBatch->buy_price : 0;
                    }

                    ProductBatch::create([
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'warehouse_id' => $opname->warehouse_id,
                        'batch_no' => 'OPNAME-' . $opname->id . '-' . $item->id,
                        'qty' => $item->difference,
                        'buy_price' => $hpp,
                    ]);
                } elseif ($item->difference < 0) {
                    $remaining = abs($item->difference);
                    $batches = ProductBatch::where('product_id', $item->product_id)
                        ->where('product_variant_id', $item->product_variant_id)
                        ->where('warehouse_id', $opname->warehouse_id)
                        ->where('qty', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($batches as $batch) {
                        if ($remaining <= 0) break;
                        $reduce = min($batch->qty, $remaining);
                        $batch->decrement('qty', $reduce);
                        $remaining -= $reduce;
                    }
                }
            }

            $opname->update([
                'status' => 'completed',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'completed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Opname ' . $opname->reference_number . ' telah disetujui dan stok disesuaikan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $opname = StockOpname::findOrFail($id);

        if ($opname->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Opname sudah ' . $opname->status]);
        }

        $opname->update([
            'status' => 'cancelled',
            'notes' => $opname->notes . ($request->alasan ? "\nAlasan ditolak: " . $request->alasan : ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Opname ' . $opname->reference_number . ' ditolak.',
        ]);
    }
}
