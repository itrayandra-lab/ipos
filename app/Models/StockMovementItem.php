<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovementItem extends Model
{
    use HasFactory;

    protected $fillable = ['stock_movement_id', 'product_id', 'product_variant_id', 'product_batch_id', 'qty'];

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }
}
