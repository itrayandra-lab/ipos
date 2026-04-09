<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_no',
        'warehouse_id',
        'period_start',
        'period_end',
        'total_amount',
        'status',
        'notes',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'verified_at'  => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(WarehouseSettlementItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
