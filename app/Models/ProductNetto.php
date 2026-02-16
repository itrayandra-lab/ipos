<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductNetto extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'netto_value'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class , 'product_netto_id');
    }
}
