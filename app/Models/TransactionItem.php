<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_id', 'product_id', 'product_variant_id', 'product_batch_id', 'buy_price', 'qty', 'price', 'subtotal'];

    public $timestamps = false;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class , 'product_variant_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class , 'product_batch_id');
    }
}
