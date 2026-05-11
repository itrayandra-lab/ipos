<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    /**
     * Get the bundle product that owns this item.
     */
    public function bundle()
    {
        return $this->belongsTo(Product::class, 'bundle_id');
    }

    /**
     * Get the product component.
     */
    public function componentProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Alias for componentProduct for backward compatibility.
     */
    public function product()
    {
        return $this->componentProduct();
    }

    /**
     * Get the product variant.
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
