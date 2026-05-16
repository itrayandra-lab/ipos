<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BranchStockRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'branch_warehouse_id',
        'requested_by',
        'approved_by',
        'shipped_by',
        'received_by',
        'status',
        'notes',
        'approval_notes',
        'shipping_notes',
        'receipt_notes',
        'receipt_photo',
        'rejection_reason',
        'approved_at',
        'shipped_at',
        'received_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'shipped_at'  => 'datetime',
        'received_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'branch_warehouse_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(BranchStockRequestItem::class, 'branch_stock_request_id');
    }

    /**
     * Generate nomor referensi: PR/mmyy/001 (reset every month)
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'PR/' . date('my');
        $last   = static::where('reference_number', 'like', $prefix . '/%')
                        ->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))
                        ->orderByDesc('id')
                        ->first();
        $seq    = $last ? ((int) substr($last->reference_number, -3)) + 1 : 1;
        return $prefix . '/' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => '<span class="badge badge-warning">Pending</span>',
            'approved'  => '<span class="badge badge-info">Disetujui</span>',
            'rejected'  => '<span class="badge badge-danger">Ditolak</span>',
            'shipped'   => '<span class="badge badge-primary">Dikirim</span>',
            'received'  => '<span class="badge badge-success">Diterima</span>',
            'cancelled' => '<span class="badge badge-secondary">Dibatalkan</span>',
            default     => '<span class="badge badge-light">' . $this->status . '</span>',
        };
    }
}
