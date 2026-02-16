<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_netto_id', 'variant_name', 'sku_code', 'price', 'price_real', 'stock'];

    public function netto()
    {
        return $this->belongsTo(ProductNetto::class , 'product_netto_id');
    }
}
