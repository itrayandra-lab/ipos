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
        'source',
        'customer_name',
        'external_order_id',
        'payment_receipt',
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
     * Generate nomor referensi: BS[warehouseCode]2605001 (per cabang)
     */
    public static function generateReferenceNumber(int $branchWarehouseId, string $warehouseCode): string
    {
        $prefix = 'BS' . $warehouseCode . date('y') . date('m');
        $last   = static::where('branch_warehouse_id', $branchWarehouseId)
                        ->where('reference_number', 'like', $prefix . '%')
                        ->orderByDesc('id')
                        ->first();
        $seq    = $last ? ((int) substr($last->reference_number, -3)) + 1 : 1;
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
