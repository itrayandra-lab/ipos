<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_netto_id', 'variant_name', 'sku_code', 'price', 'price_real', 'price_tier', 'stock', 'product_hpp', 'product_tier_id', 'hpp_rayandra', 'margin_hpp', 'ray_store', 'tax_status', 'het_online', 'is_approved'];

    public function netto()
    {
        return $this->belongsTo(ProductNetto::class , 'product_netto_id');
    }

    public function productTier()
    {
        return $this->belongsTo(ProductTier::class, 'product_tier_id');
    }
}
