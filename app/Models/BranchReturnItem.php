<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_return_id',
        'product_id',
        'product_variant_id',
        'product_batch_id',
        'qty',
        'reason',
    ];

    public function return()
    {
        return $this->belongsTo(BranchReturn::class, 'branch_return_id');
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
