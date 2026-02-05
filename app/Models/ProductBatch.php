<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_no',
        'expiry_date',
        'qty',
        'buy_price',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'product_batch_id');
    }

    /**
     * Get current stock for this batch.
     * Formula: qty (initial) - SUM(transaction_items.qty)
     */
    public function getCurrentStockAttribute()
    {
        return $this->qty - $this->transactionItems()->sum('qty');
    }
}
