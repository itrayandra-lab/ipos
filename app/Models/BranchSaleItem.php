<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_sale_id',
        'product_id',
        'product_variant_id',
        'product_batch_id',
        'qty_sold',
        'sell_price',
        'subtotal',
    ];

    public function sale()
    {
        return $this->belongsTo(BranchSale::class, 'branch_sale_id');
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
