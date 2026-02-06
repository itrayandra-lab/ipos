<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateProductCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'product_id',
        'fee_method',
        'fee_value',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
