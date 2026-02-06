<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code', 'percent', 'nominal', 'discount_type', 'product_id', 'status', 'start_date', 'end_date', 'usage_limit', 'usage_count'];
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    static function getCode($code) {
        $voucher = static::where('code', $code)->first();
        if($voucher) {
            // Check usage limit if set
            if (!is_null($voucher->usage_limit) && $voucher->usage_count >= $voucher->usage_limit) {
                return '0';
            }
            return $voucher->percent;
        }
        return '0';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
