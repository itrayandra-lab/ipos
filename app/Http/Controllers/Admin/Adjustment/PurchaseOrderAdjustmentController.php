<?php

namespace App\Http\Controllers\Admin\Adjustment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderAdjustmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $canAdjust = $user && ($user->canEdit('access_adjustment_products') || $user->canEdit('access_products'));

        return view('admin.adjustment.purchase_orders.index')->with([
            'sb' => 'AdjustmentPurchaseOrder',
            'canAdjust' => $canAdjust,
        ]);
    }

    public function data()
    {
        $canAdjust = auth()->user()
            && (auth()->user()->canEdit('access_adjustment_products') || auth()->user()->canEdit('access_products'));
        $unmatchedBatches = DB::table('product_batches as batches')
            ->join('products', 'products.id', '=', 'batches.product_id')
            ->leftJoin('merek', 'merek.id', '=', 'products.merek_id')
            ->leftJoin('suppliers as batch_suppliers', 'batch_suppliers.id', '=', 'batches.supplier_id')
            ->leftJoin('suppliers as product_suppliers', 'product_suppliers.id', '=', 'products.supplier_id')
            ->join('transaction_items', 'transaction_items.product_batch_id', '=', 'batches.id')
            ->leftJoin('goods_receipt_items', 'goods_receipt_items.id', '=', 'batches.goods_receipt_item_id')
            ->whereNull('batches.deleted_at')
            ->whereNull('batches.purchase_order_item_id')
            ->whereNull('transaction_items.purchase_order_item_id')
            ->whereNull('goods_receipt_items.purchase_order_item_id')
            ->whereNull('transaction_items.supplier_payment_id')
            ->selectRaw("
                'batch' as source_type,
                batches.id as source_id,
                batches.id as batch_id,
                batches.batch_no,
                products.id as product_id,
                products.name as product_name,
                merek.name as brand_name,
                COALESCE(batch_suppliers.name, product_suppliers.name) as supplier_name,
                SUM(transaction_items.qty) as sold_qty,
                MAX(batches.buy_price) as buy_price,
                MIN(transaction_items.id) as transaction_item_id
            ")
            ->groupBy('batches.id', 'batches.batch_no', 'products.id', 'products.name', 'merek.name', 'batch_suppliers.name', 'product_suppliers.name');

        $unmatchedItems = DB::table('transaction_items')
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('merek', 'merek.id', '=', 'products.merek_id')
            ->leftJoin('suppliers as product_suppliers', 'product_suppliers.id', '=', 'products.supplier_id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereNull('transaction_items.product_batch_id')
            ->whereNull('transaction_items.purchase_order_item_id')
            ->whereNull('transaction_items.supplier_payment_id')
            ->selectRaw("
                'transaction_item' as source_type,
                transaction_items.id as source_id,
                NULL as batch_id,
                CONCAT('Transaksi ', COALESCE(transactions.transaction_code, transactions.id)) as batch_no,
                products.id as product_id,
                products.name as product_name,
                merek.name as brand_name,
                product_suppliers.name as supplier_name,
                transaction_items.qty as sold_qty,
                transaction_items.buy_price as buy_price,
                transaction_items.id as transaction_item_id
            ");

        $unmatched = DB::query()->fromSub($unmatchedBatches->unionAll($unmatchedItems), 'unmatched');

        return DataTables::of($unmatched)
            ->addIndexColumn()
            ->addColumn('product_label', function ($row) {
                return trim(implode(' ', array_filter([$row->brand_name, $row->product_name])));
            })
            ->addColumn('action', function ($row) use ($canAdjust) {
                if (!$canAdjust) {
                    return '<span class="text-muted">Tidak ada akses</span>';
                }
                return '<button type="button" class="btn btn-sm btn-primary btn-link-po" data-type="' . e($row->source_type) . '" data-id="' . (int) $row->source_id . '" data-product="' . (int) $row->product_id . '"><i class="fas fa-link mr-1"></i> Hubungkan PO</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function candidates(Request $request)
    {
        $this->authorizeAdjustment();
        $data = $request->validate([
            'source_type' => 'required|in:batch,transaction_item',
            'source_id' => 'required|integer|min:1',
        ]);

        $productId = $data['source_type'] === 'batch'
            ? DB::table('product_batches')->where('id', $data['source_id'])->whereNull('purchase_order_item_id')->value('product_id')
            : DB::table('transaction_items')->where('id', $data['source_id'])->whereNull('purchase_order_item_id')->whereNull('product_batch_id')->value('product_id');

        if (!$productId) {
            return response()->json(['message' => 'Data sudah terhubung atau tidak ditemukan.'], 404);
        }

        $query = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->where('purchase_order_items.product_id', $productId)
            ->whereNotIn('purchase_orders.status', ['draft', 'submitted', 'cancelled'])
            ->select(
                'purchase_order_items.id as purchase_order_item_id',
                'purchase_order_items.quantity',
                'purchase_order_items.paid_qty',
                'purchase_order_items.product_name',
                'purchase_orders.id as purchase_order_id',
                'purchase_orders.po_number',
                'purchase_orders.po_date',
                'suppliers.name as supplier_name'
            )
            ->orderByDesc('purchase_orders.po_date');

        $supplierId = $data['source_type'] === 'batch'
            ? DB::table('product_batches')->where('id', $data['source_id'])->value('supplier_id')
            : DB::table('products')->where('id', $productId)->value('supplier_id');
        if ($supplierId) {
            $query->where('purchase_orders.supplier_id', $supplierId);
        }

        return response()->json(['items' => $query->get()]);
    }

    public function update(Request $request)
    {
        $this->authorizeAdjustment();
        $data = $request->validate([
            'source_type' => 'required|in:batch,transaction_item',
            'source_id' => 'required|integer|min:1',
            'purchase_order_item_id' => 'required|exists:purchase_order_items,id',
        ]);

        DB::transaction(function () use ($data) {
            $item = DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->where('purchase_order_items.id', $data['purchase_order_item_id'])
                ->whereNotIn('purchase_orders.status', ['draft', 'submitted', 'cancelled'])
                ->select('purchase_order_items.*', 'purchase_orders.supplier_id', 'purchase_orders.id as po_id')
                ->lockForUpdate()
                ->first();

            if (!$item) {
                abort(422, 'Item PO tidak tersedia untuk penautan.');
            }

            if ($data['source_type'] === 'batch') {
                $batch = DB::table('product_batches')
                    ->where('id', $data['source_id'])
                    ->where('product_id', $item->product_id)
                    ->whereNull('purchase_order_item_id')
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();
                if (!$batch || ($batch->supplier_id && (int) $batch->supplier_id !== (int) $item->supplier_id)) {
                    abort(422, 'Batch tidak cocok dengan produk atau supplier PO.');
                }
                $productSupplierId = DB::table('products')->where('id', $batch->product_id)->value('supplier_id');
                if (!$batch->supplier_id && $productSupplierId && (int) $productSupplierId !== (int) $item->supplier_id) {
                    abort(422, 'Supplier produk tidak cocok dengan supplier PO.');
                }

                $receiptPOItemId = DB::table('goods_receipt_items')
                    ->where('id', $batch->goods_receipt_item_id)
                    ->value('purchase_order_item_id');
                if ($receiptPOItemId) {
                    abort(422, 'Batch sudah memiliki referensi PO dari penerimaan barang.');
                }

                DB::table('product_batches')->where('id', $batch->id)->update([
                    'purchase_order_item_id' => $item->id,
                    'updated_at' => now(),
                ]);
            } else {
                $saleItem = DB::table('transaction_items')
                    ->where('id', $data['source_id'])
                    ->where('product_id', $item->product_id)
                    ->whereNull('product_batch_id')
                    ->whereNull('purchase_order_item_id')
                    ->whereNull('supplier_payment_id')
                    ->lockForUpdate()
                    ->first();
                if (!$saleItem) {
                    abort(422, 'Baris transaksi sudah terhubung atau tidak ditemukan.');
                }

                $supplierId = DB::table('products')->where('id', $saleItem->product_id)->value('supplier_id');
                if ($supplierId && (int) $supplierId !== (int) $item->supplier_id) {
                    abort(422, 'Supplier produk tidak cocok dengan supplier PO.');
                }

                DB::table('transaction_items')->where('id', $saleItem->id)->update([
                    'purchase_order_item_id' => $item->id,
                ]);
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Produk berhasil dihubungkan ke PO.']);
    }

    private function authorizeAdjustment(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->canEdit('access_adjustment_products') && !$user->canEdit('access_products'))) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan adjustment PO.');
        }
    }
}
