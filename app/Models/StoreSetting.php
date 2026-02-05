<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'logo_path',
        'address',
        'phone',
        'whatsapp',
        'email',
        'instagram',
        'facebook',
        'tiktok',
        'shopee_url',
        'tokopedia_url',
        'website',
        'footer_text'
    ];

    /**
     * Get the active store settings (assumes single record)
     */
    public static function getActiveSetting()
    {
        return self::first() ?? self::create([
            'store_name' => 'My Store',
            'address' => 'Store Address',
        ]);
    }
}
