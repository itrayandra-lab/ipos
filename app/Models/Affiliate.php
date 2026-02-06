<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_id',
        'fee_method',
        'fee_value',
        'is_active',
    ];

    public function type()
    {
        return $this->belongsTo(Attribute::class, 'type_id');
    }

    public function productCommissions()
    {
        return $this->hasMany(AffiliateProductCommission::class);
    }
}
