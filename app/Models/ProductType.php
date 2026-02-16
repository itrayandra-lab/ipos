<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($productType) {
            if (empty($productType->slug)) {
                $productType->slug = \Illuminate\Support\Str::slug($productType->name);
            }
        });
    }
}
