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
        'factors'
    ];

    protected $casts = [
        'factors' => 'array',
    ];

    public function calculatePrice($basePrice)
    {
        $price = $basePrice;
        
        if (!is_array($this->factors)) {
            return $price;
        }

        foreach ($this->factors as $factor) {
            $value = floatval($factor['value']);
            
            switch ($factor['operator']) {
                case 'multiply': 
                    $price *= $value;
                    break;
                case 'percentage': 
                    $price += ($price * ($value / 100));
                    break;
                case 'add': 
                    $price += $value;
                    break;
            }
        }

        return ceil($price); // Round up to nearest integer
    }
}
