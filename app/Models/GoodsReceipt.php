<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'sj_number',
        'purchase_order_id',
        'supplier_id',
        'delivery_note_number',
        'delivery_date',
        'received_date',
        'received_by',
        'notes',
        'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'received_date' => 'date',
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class , 'received_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    // Auto-generate SJ number: SJ/BL/YYYY/MM/XXXX
    public static function generateSJNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "SJ/BL/{$year}/{$month}/";

        $lastSJ = self::where('sj_number', 'like', $prefix . '%')
            ->orderBy('sj_number', 'desc')
            ->first();

        if ($lastSJ) {
            $lastNumber = intval(substr($lastSJ->sj_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }
        else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }
}
