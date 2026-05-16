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

    public function recalculatePricing()
    {
        if ($this->product_hpp <= 0) return;

        $pricing = \App\Services\PricingService::calculateRayandraPricing(
            $this->product_hpp,
            $this->product_tier_id,
            $this->ray_store > 0 ? $this->ray_store : null
        );

        $this->hpp_rayandra = $pricing['hpp_rayandra'];
        $this->margin_hpp   = $pricing['margin_hpp'];
        if ($this->ray_store <= 0) {
            $this->ray_store = $pricing['ray_store'];
        }

        $settings   = \App\Models\StoreSetting::getActiveSetting();
        $feeOnline  = ($settings->fee_online_percent ?? 4) / 100;
        $tax        = $this->tax_status ? (($settings->tax_percent ?? 11) / 100) : 0;
        
        if ((1 - $feeOnline) > 0) {
            $rawHet = ($this->ray_store / (1 - $feeOnline)) * (1 + $tax);
            $this->het_online = ceil($rawHet / 1000) * 1000;
        }
        
        $this->save();
    }

    /**
     * Get the active selling price for this variant.
     * Logic: Use het_online if approved, otherwise fallback to legacy price.
     */
    public function getSellingPrice()
    {
        if ($this->is_approved && $this->het_online > 0) {
            return $this->het_online;
        }

        // Fallback to legacy price
        if ($this->price > 0) {
            return $this->price;
        }

        // Last resort: product price
        $product = $this->netto ? $this->netto->product : null;
        if ($product) {
            return $product->price_real > 0 ? $product->price_real : $product->price;
        }

        return 0;
    }

    public function getSellingPriceAttribute()
    {
        return $this->getSellingPrice();
    }
}
