<?php

namespace App\Http\Controllers\Admin\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Batch; // Menggunakan Batch model yang ada untuk stock
use App\Models\ProductBatch; // Jika ada model ini untuk stok batch
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
        return response()->json([
            'supplier_id' => $po->supplier_id,
            'warehouse_id' => $po->warehouse_id,
            'items' => $po->items
        ]);
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
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'received_date' => $request->received_date,
                'received_by' => Auth::id(),
                'notes' => $request->notes,
                'status' => 'confirmed', // Langsung confirmed untuk update stok
            ]);

            foreach ($request->items as $item) {
                $grItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'quantity_ordered' => $item['qty_ordered'] ?? 0,
                    'quantity_received' => $item['qty_received'],
                    'quantity_difference' => $item['qty_received'] - ($item['qty_ordered'] ?? 0),
                    'notes' => $item['item_notes'] ?? null,
                ]);

                // Update Stock logic
                // Cari variant_id berdasarkan item if linked to product
                $poItem = null;
                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = \App\Models\PurchaseOrderItem::find($item['purchase_order_item_id']);
                }

                if ($poItem && $poItem->product_id) {
                    // Cari variant pertama atau bdasarkan description match
                    // Untuk simplifikasi, kita asumsikan variant ID tersimpan di PO Item jika ada link ke product
                    // Namun di PO Item kita hanya simpan product_id. 
                    // Sebaiknya kita cek ProductVariant.

                    $variant = \App\Models\ProductVariant::whereHas('netto', function ($q) use ($poItem) {
                        $q->where('product_id', $poItem->product_id);
                    })->where('variant_name', $poItem->description)->first();

                    if ($variant) {
                        // Create Batch baru (Stok baru masuk)
                        \App\Models\ProductBatch::create([
                            'product_id' => $poItem->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $request->warehouse_id,
                            'batch_no' => $request->delivery_note_number,
                            'qty' => $item['qty_received'],
                            'buy_price' => $poItem->unit_price,
                            'expiry_date' => null, // User bisa input ini nanti
                        ]);
                    }
                }
            }

            // Update PO status if all items received
            if ($request->purchase_order_id) {
                PurchaseOrder::where('id', $request->purchase_order_id)->update(['status' => 'received']);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Penerimaan Barang berhasil dikonfirmasi dan stok telah diperbarui', 'redirect' => route('admin.purchasing.goods_receipts.index')]);

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

            // Reverse old stock (hapus batch lama terkait GR ini)
            foreach ($gr->items as $oldItem) {
                if ($oldItem->purchase_order_item_id) {
                    $poItem = \App\Models\PurchaseOrderItem::find($oldItem->purchase_order_item_id);
                    if ($poItem && $poItem->product_id) {
                        \App\Models\ProductBatch::where('batch_no', $gr->delivery_note_number)
                            ->where('product_id', $poItem->product_id)
                            ->where('warehouse_id', $gr->warehouse_id)
                            ->delete();
                    }
                }
            }

            // Update GR metadata
            $gr->update([
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'delivery_note_number' => $request->delivery_note_number,
                'delivery_date' => $request->delivery_date,
                'received_date' => $request->received_date,
                'notes' => $request->notes,
            ]);

            // Hapus items lama
            $gr->items()->delete();

            // Buat items baru dan update stok
            foreach ($request->items as $item) {
                $grItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'satuan' => $item['satuan'] ?? null,
                    'quantity_ordered' => $item['qty_ordered'] ?? 0,
                    'quantity_received' => $item['qty_received'],
                    'quantity_difference' => $item['qty_received'] - ($item['qty_ordered'] ?? 0),
                    'notes' => $item['item_notes'] ?? null,
                ]);

                // Update Stock logic
                $poItem = null;
                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = \App\Models\PurchaseOrderItem::find($item['purchase_order_item_id']);
                }

                if ($poItem && $poItem->product_id) {
                    $variant = \App\Models\ProductVariant::whereHas('netto', function ($q) use ($poItem) {
                        $q->where('product_id', $poItem->product_id);
                    })->where('variant_name', $poItem->description)->first();

                    if ($variant) {
                        \App\Models\ProductBatch::create([
                            'product_id' => $poItem->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $request->warehouse_id,
                            'batch_no' => $request->delivery_note_number,
                            'qty' => $item['qty_received'],
                            'buy_price' => $poItem->unit_price,
                            'expiry_date' => null,
                        ]);
                    }
                }
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

            // Reverse stock (hapus batch terkait)
            foreach ($gr->items as $item) {
                if ($item->purchase_order_item_id) {
                    $poItem = \App\Models\PurchaseOrderItem::find($item->purchase_order_item_id);
                    if ($poItem && $poItem->product_id) {
                        \App\Models\ProductBatch::where('batch_no', $gr->delivery_note_number)
                            ->where('product_id', $poItem->product_id)
                            ->where('warehouse_id', $gr->warehouse_id)
                            ->delete();
                    }
                }
            }

            // Kembalikan status PO jika ada
            if ($gr->purchase_order_id) {
                \App\Models\PurchaseOrder::where('id', $gr->purchase_order_id)->update(['status' => 'submitted']);
            }

            // Hapus items dan GR
            $gr->items()->delete();
            $gr->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Penerimaan Barang berhasil dihapus dan stok dikembalikan.']);
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
