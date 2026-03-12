<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'due_date',
        'transaction_type',
        'down_payment',
        'is_dp',
        'tax_type',
        'tax_amount',
        'discount_type',
        'user_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'source',
        'notes',
        'total_amount',
        'payment_status',
        'payment_method',
        'bank_account_id',
        'delivery_type',
        'delivery_desc',
        'voucher_code',
        'discount',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'payment_receipt',
        'affiliate_id',
        'affiliate_fee_total',
        'affiliate_fee_mode',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
