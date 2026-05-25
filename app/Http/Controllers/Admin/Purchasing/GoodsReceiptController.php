<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class GoodsReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $restrictedMethods = ['create', 'store', 'edit', 'update', 'destroy'];

            if ($user && in_array($request->route()->getActionMethod(), $restrictedMethods)) {
                if (!$user->canEdit('access_goods_receipts')) {
                    if ($request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk tindakan ini.'], 403);
                    }
                    return redirect()->route('admin.purchasing.goods_receipts.index')->with('error', 'Anda tidak memiliki akses untuk tindakan ini.');
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.purchasing.goods_receipts.index')->with('sb', 'GoodsReceipt');
    }

    public function getall()
    {
        $grs = GoodsReceipt::with(['supplier', 'purchaseOrder', 'receiver'])->orderBy('received_date', 'desc');

        return DataTables::of($grs)
            ->addIndexColumn()
            ->addColumn('supplier_name', function ($gr) {
            return $gr->supplier ? $gr->supplier->name : '-';
        })
            ->addColumn('po_number', function ($gr) {
            return $gr->purchaseOrder ? $gr->purchaseOrder->po_number : '-';
        })
            ->addColumn('received_by_name', function ($gr) {
            return $gr->receiver ? $gr->receiver->name : '-';
        })
            ->editColumn('received_date', function ($gr) {
            return $gr->received_date->format('d/m/Y');
        })
            ->addColumn('action', function ($gr) {
            $isFinance = auth()->user()->isFinance();
            $editBtn = !$isFinance ? '<a class="dropdown-item has-icon" href="' . route('admin.purchasing.goods_receipts.edit', $gr->id) . '"><i class="fas fa-edit text-warning"></i> Edit</a>' : '';
            $deleteBtn = !$isFinance ? '
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item has-icon text-danger btn-delete" href="javascript:void(0)" data-id="' . $gr->id . '"><i class="fas fa-trash"></i> Hapus</a>' : '';
            return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item has-icon" href="' . route('admin.purchasing.goods_receipts.show', $gr->id) . '"><i class="fas fa-eye text-info"></i> Detail</a></li>
                            ' . $editBtn . '
                            ' . $deleteBtn . '
                        </ul>
                    </div>';
        })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $sj_number = GoodsReceipt::generateSJNumber();
        $pos = PurchaseOrder::whereIn('status', ['submitted', 'approved'])->get();
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        return view('admin.purchasing.goods_receipts.create', compact('sj_number', 'pos', 'suppliers', 'warehouses'))->with('sb', 'GoodsReceipt');
    }

    public function getPoItems(Request $request)
    {
        $po = PurchaseOrder::with('items')->findOrFail($request->po_id);
        $items = $po->items->map(function ($item) {
            $data = $item->toArray();
            $data['display_name'] = $item->product_name;

            if ($item->product) {
                $parts = [];
                if ($item->product->merek) {
                    $parts[] = $item->product->merek->name;
                }
                $parts[] = $item->product->name;
                if ($item->description) {
                    $parts[] = $item->description;
                }
                $data['display_name'] = implode(' ', $parts);
            }

            return $data;
        });

        return response()->json([
            'supplier_id' => $po->supplier_id,
            'warehouse_id' => $po->warehouse_id,
            'items' => $items
        ]);
    }

    public function getProducts(Request $request)
    {
        $search = $request->search;
        $products = Product::with(['merek', 'variants.netto'])
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('merek', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('variants', function ($vq) use ($search) {
                      $vq->where('sku_code', 'like', "%{$search}%");
                  });
            })
            ->limit(20)
            ->get();

        $results = [];
        foreach ($products as $product) {
            $brand = ($product->merek && $product->merek->name) ? $product->merek->name : '';

            if ($product->variants->count() > 0) {
                foreach ($product->variants as $variant) {
                    $nettoText = '';
                    if ($variant->netto) {
                        $nettoText = trim($variant->netto->netto_value . ' ' . $variant->netto->satuan);
                    }

                    $pName = $product->name;
                    $vName = ($variant->variant_name && $variant->variant_name !== 'Default') ? $variant->variant_name : '';

                    $parts = [];
                    if ($brand) $parts[] = $brand;

                    if ($vName) {
                        if (stripos($vName, $pName) !== false) {
                            $parts[] = $vName;
                        } else {
                            $parts[] = $pName;
                            $parts[] = $vName;
                        }
                    } else {
                        $parts[] = $pName;
                    }

                    $currentText = implode(' - ', $parts);

                    if ($nettoText) {
                        $cleanCurrent = strtolower(str_replace(' ', '', $currentText));
                        $cleanNetto = strtolower(str_replace(' ', '', $nettoText));
                        if (strpos($cleanCurrent, $cleanNetto) === false) {
                            $parts[] = $nettoText;
                        }
                    }

                    $name = implode(' ', array_filter($parts));
                    $name = preg_replace('/\s+/', ' ', $name);

                    $results[] = [
                        'id' => $variant->id,
                        'text' => $name,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'product_name' => $product->name,
                        'variant_name' => $variant->variant_name ?? '',
                        'description' => $nettoText,
                        'satuan' => $variant->netto->satuan ?? '',
                    ];
                }
            } else {
                $name = trim($brand . ' ' . $product->name);
                $results[] = [
                    'id' => 'p_' . $product->id,
                    'text' => $name,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'product_name' => $product->name,
                    'variant_name' => '',
                    'description' => '',
                    'satuan' => '',
                ];
            }
        }

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_note_number' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'received_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty_received' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::create([
                'sj_number' => GoodsReceipt::generateSJNumber(),
                'purchase_order_id' => $request->purchase_order_id ?: null,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'received_date' => $request->received_date,
                'received_by' => Auth::id(),
                'notes' => $request->notes,
                'status' => 'confirmed',
            ]);

            foreach ($request->items as $item) {
                $batchNo = $item['batch_no'] ?? null;
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'batch_no' => $batchNo,
                    'quantity_ordered' => $item['qty_ordered'] ?? 0,
                    'quantity_received' => $item['qty_received'],
                    'quantity_difference' => $item['qty_received'] - ($item['qty_ordered'] ?? 0),
                    'notes' => $item['item_notes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Penerimaan Barang berhasil dikonfirmasi', 'redirect' => route('admin.purchasing.goods_receipts.index')]);

        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $gr = GoodsReceipt::with(['supplier', 'purchaseOrder', 'receiver', 'items'])->findOrFail($id);
        $pos = PurchaseOrder::whereIn('status', ['submitted', 'approved', 'received'])->get();
        $suppliers = Supplier::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        return view('admin.purchasing.goods_receipts.edit', compact('gr', 'pos', 'suppliers', 'warehouses'))->with('sb', 'GoodsReceipt');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_note_number' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'received_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.qty_received' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::with('items')->findOrFail($id);

            $gr->update([
                'purchase_order_id' => $request->purchase_order_id ?: null,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'received_date' => $request->received_date,
                'notes' => $request->notes,
            ]);

            $gr->items()->delete();

            foreach ($request->items as $item) {
                $batchNo = $item['batch_no'] ?? null;
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'batch_no' => $batchNo,
                    'quantity_ordered' => $item['qty_ordered'] ?? 0,
                    'quantity_received' => $item['qty_received'],
                    'quantity_difference' => $item['qty_received'] - ($item['qty_ordered'] ?? 0),
                    'notes' => $item['item_notes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Penerimaan Barang berhasil diperbarui', 'redirect' => route('admin.purchasing.goods_receipts.index')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::with('items')->findOrFail($id);

            $gr->items()->delete();
            $gr->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Penerimaan Barang berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $gr = GoodsReceipt::with(['supplier', 'purchaseOrder', 'receiver', 'items'])->findOrFail($id);
        return view('admin.purchasing.goods_receipts.show', compact('gr'))->with('sb', 'GoodsReceipt');
    }
}
