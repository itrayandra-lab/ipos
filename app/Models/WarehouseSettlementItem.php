<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseSettlementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_settlement_id',
        'product_id',
        'product_variant_id',
        'quantity_sold',
        'unit_price',
        'subtotal',
    ];

    public function settlement()
    {
        return $this->belongsTo(WarehouseSettlement::class, 'warehouse_settlement_id');
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
