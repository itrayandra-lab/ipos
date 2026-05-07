<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'batch_no',
        'expiry_date',
        'qty',
        'buy_price',
    ];

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

    /**
     * Get current stock for this batch.
     * Formula: qty (initial) - SUM(transaction_items.qty)
     */
    public function getCurrentStockAttribute()
    {
        $sales = $this->transaction_items_sum_qty ?? $this->transactionItems()->sum('qty');
        $returns = $this->supplier_return_items_sum_qty ?? $this->supplierReturnItems()->sum('qty');
        
        return (int)($this->qty - ($sales ?? 0) - ($returns ?? 0));
    }
}
