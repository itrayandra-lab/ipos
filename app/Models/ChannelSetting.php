<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'margin_type',
        'margin_value',
        'fee_type',
        'fee_value',
        'fixed_cost',
        'shipping_subsidy'
    ];
}
