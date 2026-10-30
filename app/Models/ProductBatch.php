<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'supplier_id',
        'batch_no',
        'expiry_date',
        'qty',
        'buy_price',
        'goods_receipt_item_id',
        'purchase_order_item_id',
        'stock_movement_id',
        'stock_movement_item_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    protected $appends = ['current_stock'];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class , 'product_variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class , 'product_batch_id');
    }

    public function supplierReturnItems()
    {
        return $this->hasMany(SupplierReturnItem::class, 'product_batch_id');
    }

    public function incomingMovement()
    {
        return $this->belongsTo(\App\Models\StockMovement::class, 'stock_movement_id');
    }

    public function incomingMovementItem()
    {
        return $this->belongsTo(StockMovementItem::class, 'stock_movement_item_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Kunci identitas 1 baris batch (konvensi GoodsReceipt):
     * produk + varian + gudang + no batch.
     * Dipakai validasi anti input-ganda: ID boleh beda-beda untuk tracking,
     * tapi kombinasi kunci yang sama = baris yang sama (top-up, bukan baris baru).
     */
    public static function findDuplicate($productId, $variantId, $warehouseId, $batchNo, $exceptId = null)
    {
        return static::where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->where('batch_no', $batchNo)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }

    public function stockMovementItems()
    {
        return $this->hasMany(StockMovementItem::class, 'product_batch_id');
    }

    /**
     * Preload jumlah K (transaksi + return + kirim movement transit/completed)
     * agar current_stock tidak N+1 saat dipakai massal.
     */
    public function scopeWithSisa($query)
    {
        return $query->withSum('transactionItems', 'qty')
            ->withSum('supplierReturnItems', 'qty')
            ->withSum(['stockMovementItems as stock_movement_items_sum_qty' => function ($q) {
                $q->whereHas('stockMovement', fn($q2) => $q2->whereIn('status', ['transit', 'completed']));
            }], 'qty')
            ->withSum('returnReceivedItemsAgg as supplier_return_received_sum_qty', 'qty');
    }

    public function supplierDeliveryNoteItems()
    {
        return $this->hasMany(SupplierDeliveryNoteItem::class, 'product_batch_id');
    }

    public function hasStockHistory(): bool
    {
        return $this->stockMovementItems()->exists()
            || $this->supplierDeliveryNoteItems()->exists()
            || $this->transactionItems()->exists()
            || $this->supplierReturnItems()->exists();
    }

    /**
     * Get current stock for this batch (Model Jurnal).
     * M = total terima tercatat (tidak dikurangi aliran keluar rutin).
     * K = transaksi + return supplier + movement keluar tersync.
     * RR = return received (return yang DITERIMA gudang batch ini dari batch lain).
     * S = M - K + RR.
     */
    public function getCurrentStockAttribute()
    {
        $sales = $this->transaction_items_sum_qty ?? $this->transactionItems()->sum('qty');
        $returns = $this->supplier_return_items_sum_qty ?? $this->supplierReturnItems()->sum('qty');
        $moved = $this->stock_movement_items_sum_qty ?? $this->stockMovementItems()
            ->whereHas('stockMovement', fn($q) => $q->whereIn('status', ['transit', 'completed']))->sum('qty');
        $rr = $this->supplier_return_received_sum_qty ?? $this->returnReceivedItems()->sum('qty');

        return (int)($this->qty - ($sales ?? 0) - ($returns ?? 0) - ($moved ?? 0) + ($rr ?? 0));
    }

    /**
     * Baris return yang DITERIMA gudang batch ini dari batch lain
     * (dokumen return gudangnya = gudang batch ini, tapi menunjuk batch lain,
     * varian sama). Info penerima — menambah S. Versi lazy (per instance).
     */
    public function returnReceivedItems()
    {
        return $this->hasMany(SupplierReturnItem::class, 'product_id', 'product_id')
            ->where('supplier_return_items.product_batch_id', '!=', $this->id)
            ->where(function ($q) {
                if (is_null($this->product_variant_id)) {
                    $q->whereNull('supplier_return_items.product_variant_id');
                } else {
                    $q->where('supplier_return_items.product_variant_id', $this->product_variant_id);
                }
            })
            ->whereHas('supplierReturn', fn($q) => $q->where('supplier_returns.warehouse_id', $this->warehouse_id));
    }

    /**
     * Sama seperti returnReceivedItems tapi dengan referensi kolom tabel luar,
     * khusus untuk preload withSum (di withSum, instance model kosong sehingga
     * versi value-based tidak bisa dipakai). JANGAN dipanggil lazy.
     */
    public function returnReceivedItemsAgg()
    {
        return $this->hasMany(SupplierReturnItem::class, 'product_id', 'product_id')
            ->whereColumn('supplier_return_items.product_batch_id', '!=', 'product_batches.id')
            ->whereRaw('supplier_return_items.product_variant_id <=> product_batches.product_variant_id')
            ->whereHas('supplierReturn', fn($q) => $q->whereColumn('supplier_returns.warehouse_id', 'product_batches.warehouse_id'));
    }
}
