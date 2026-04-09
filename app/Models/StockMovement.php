<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'user_id',
        'status',
        'shipped_at',
        'received_at',
        'received_by',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
