<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'branch_warehouse_id',
        'user_id',
        'sale_date',
        'notes',
        'total_amount',
    ];

    protected $casts = [
        'sale_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'branch_warehouse_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(BranchSaleItem::class, 'branch_sale_id');
    }

    /**
     * Generate nomor referensi: BS/MM/YY/0001
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'BS/' . date('m') . '/' . date('y');
        $last   = static::where('reference_number', 'like', $prefix . '/%')
                        ->orderByDesc('id')
                        ->first();
        $seq    = $last ? ((int) substr($last->reference_number, -4)) + 1 : 1;
        return $prefix . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
