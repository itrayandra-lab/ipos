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
        'warehouse_id',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'received_date' => 'date',
    ];

    // Relationships
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

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

    // Auto-generate GR number: GR/MMYY/XXX (reset every month)
    public static function generateGRNumber()
    {
        $prefix = 'GR/' . date('my') . '/';

        $lastGR = self::where('sj_number', 'like', $prefix . '%')
            ->orderBy('sj_number', 'desc')
            ->first();

        if ($lastGR) {
            $lastNumber = intval(substr($lastGR->sj_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }
        else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }
}
