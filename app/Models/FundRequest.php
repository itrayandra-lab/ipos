<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'user_id',
        'expense_category_id',
        'title',
        'description',
        'amount',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'status',
        'manager_id',
        'manager_approved_at',
        'manager_notes',
        'finance_id',
        'finance_approved_at',
        'finance_notes',
        'attachment',
        'transfer_proof'
    ];

    protected $casts = [
        'manager_approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function finance()
    {
        return $this->belongsTo(User::class, 'finance_id');
    }

    public static function generateCode()
    {
        $prefix = 'REQ';
        $date = date('Ymd');
        $last = self::where('request_code', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$last) {
            $number = '001';
        } else {
            $lastNumber = substr($last->request_code, -3);
            $number = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . $date . $number;
    }
}
