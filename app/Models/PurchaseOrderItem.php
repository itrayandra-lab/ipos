<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_name',
        'description',
        'satuan',
        'quantity',
        'paid_qty',
        'returned_qty',
        'replaced_qty',
        'unit_price',
        'total',
    ];

    /** Qty yang harus dibayar = qty PO - qty return + qty pengganti yang sudah diterima dari supplier. */
    public function getPayableQtyAttribute(): float
    {
        return max(0, (float) $this->quantity - (float) $this->returned_qty + (float) $this->replaced_qty);
    }

    /**
     * Hitung ulang returned_qty dari supplier_return_items (relasi batch -> item PO, langsung atau lewat goods receipt).
     * $poItemIds = null untuk semua item PO.
     */
    public static function syncReturned(?array $poItemIds = null): void
    {
        $where = '';
        $bindings = [];
        if ($poItemIds !== null) {
            $poItemIds = array_values(array_unique(array_filter($poItemIds)));
            if (!$poItemIds) {
                return;
            }
            $where = 'WHERE poi.id IN (' . implode(',', array_fill(0, count($poItemIds), '?')) . ')';
            $bindings = $poItemIds;
        }

        \Illuminate\Support\Facades\DB::update("
            UPDATE purchase_order_items poi
            SET replaced_qty = COALESCE((
                SELECT SUM(g.quantity_received) FROM goods_receipt_items g
                WHERE g.purchase_order_item_id = poi.id AND g.supplier_return_item_id IS NOT NULL
            ), 0),
            returned_qty = COALESCE((
                SELECT SUM(sri.qty)
                FROM supplier_return_items sri
                JOIN product_batches pb ON pb.id = sri.product_batch_id
                LEFT JOIN goods_receipt_items gri ON gri.id = pb.goods_receipt_item_id
                WHERE COALESCE(pb.purchase_order_item_id, gri.purchase_order_item_id) = poi.id
            ), 0)
            {$where}
        ", $bindings);
    }

    /** Sinkronkan returned_qty item PO yang terkait batch-batch tertentu. */
    public static function syncReturnedForBatches(array $batchIds): void
    {
        $batchIds = array_values(array_unique(array_filter($batchIds)));
        if (!$batchIds) {
            return;
        }
        $ids = \Illuminate\Support\Facades\DB::table('product_batches as pb')
            ->leftJoin('goods_receipt_items as gri', 'gri.id', '=', 'pb.goods_receipt_item_id')
            ->whereIn('pb.id', $batchIds)
            ->selectRaw('COALESCE(pb.purchase_order_item_id, gri.purchase_order_item_id) as poi_id')
            ->pluck('poi_id')
            ->filter()
            ->all();

        self::syncReturned($ids);
    }

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}
