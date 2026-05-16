<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchStockRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_stock_request_id',
        'product_id',
        'product_variant_id',
        'qty_requested',
        'qty_approved',
        'notes',
    ];

    public function request()
    {
        return $this->belongsTo(BranchStockRequest::class, 'branch_stock_request_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
