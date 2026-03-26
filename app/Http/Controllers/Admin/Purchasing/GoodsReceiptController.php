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
            return '
                    <div class="dropdown d-inline dropleft">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true" data-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="' . route('admin.purchasing.goods_receipts.show', $gr->id) . '" class="dropdown-item">Detail</a></li>
                            <li><a href="#" data-id="' . $gr->id . '" class="dropdown-item btn-delete text-danger">Hapus</a></li>
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
        return view('admin.purchasing.goods_receipts.create', compact('sj_number', 'pos', 'suppliers'))->with('sb', 'GoodsReceipt');
    }

    public function getPoItems(Request $request)
    {
        $po = PurchaseOrder::with('items')->findOrFail($request->po_id);
        return response()->json([
            'supplier_id' => $po->supplier_id,
            'items' => $po->items
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
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

    public function show($id)
    {
        $gr = GoodsReceipt::with(['supplier', 'purchaseOrder', 'receiver', 'items'])->findOrFail($id);
        return view('admin.purchasing.goods_receipts.show', compact('gr'))->with('sb', 'GoodsReceipt');
    }
}
