<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'slug', 'description'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($subCategory) {
            if (empty($subCategory->slug)) {
                $subCategory->slug = \Illuminate\Support\Str::slug($subCategory->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function productTypes()
    {
        return $this->hasMany(ProductType::class);
    }
}
