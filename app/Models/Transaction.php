<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'source',
        'notes',
        'total_amount',
        'payment_status',
        'payment_method',
        'delivery_type',
        'delivery_desc',
        'voucher_code',
        'discount',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'affiliate_id',
        'affiliate_fee_total',
        'affiliate_fee_mode',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
