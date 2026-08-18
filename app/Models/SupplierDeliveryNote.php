<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierDeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'sj_number',
        'supplier_id',
        'user_id',
        'delivery_note_number',
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierDeliveryNoteItem::class);
    }

    public static function generateSJNumber()
    {
        $prefix = 'SJS/' . date('my') . '/';

        $lastSJ = self::where('sj_number', 'like', $prefix . '%')
            ->orderBy('sj_number', 'desc')
            ->first();

        if ($lastSJ) {
            $lastNumber = intval(substr($lastSJ->sj_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }
}
