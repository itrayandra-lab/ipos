<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
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
        'other_fees',
        'is_sample',
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
        'transaction_date',
        'warehouse_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'due_date' => 'datetime',
        'is_sample' => 'boolean',
        'other_fees' => 'array',
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

    /**
     * Catat DP lama (kolom down_payment) sebagai baris pembayaran bila belum pernah dicatat.
     * Harus dipanggil di dalam DB::transaction pada instance yang sudah dikunci.
     */
    public function ensureDownPaymentRecorded(): void
    {
        if ((float) $this->down_payment > 0 && !$this->payments()->exists()) {
            TransactionPayment::create([
                'transaction_id' => $this->id,
                'amount'         => $this->down_payment,
                'payment_date'   => $this->created_at ?? now(),
                'payment_method' => $this->payment_method ?: 'manual',
                'notes'          => 'Down Payment',
            ]);
        }
    }

    /**
     * Catat satu pembayaran dengan lock dan validasi: nominal > 0 dan tidak melebihi sisa tagihan.
     * Status otomatis menjadi 'paid' bila sudah lunas.
     *
     * @throws \RuntimeException
     */
    public function recordPayment(float $amount, $paymentDate, ?string $method, ?string $receiptPath, ?string $notes): TransactionPayment
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($amount, $paymentDate, $method, $receiptPath, $notes) {
            $locked = static::lockForUpdate()->findOrFail($this->id);
            $locked->ensureDownPaymentRecorded();

            if ($amount <= 0) {
                throw new \RuntimeException('Nominal pembayaran harus lebih dari 0.');
            }

            $remaining = (float) $locked->total_amount - (float) $locked->payments()->sum('amount');
            if ($amount > $remaining + 0.01) {
                throw new \RuntimeException('Nominal melebihi sisa tagihan (sisa Rp ' . number_format(max($remaining, 0), 0, ',', '.') . ').');
            }

            $payment = TransactionPayment::create([
                'transaction_id'  => $locked->id,
                'amount'          => $amount,
                'payment_date'    => $paymentDate,
                'payment_method'  => $method ?: ($locked->payment_method ?: 'manual'),
                'payment_receipt' => $receiptPath,
                'notes'           => $notes,
            ]);

            if ((float) $locked->payments()->sum('amount') >= (float) $locked->total_amount - 0.01) {
                $locked->update(['payment_status' => 'paid', 'is_dp' => false]);
            }

            return $payment;
        });
    }

    /**
     * Alasan transaksi tidak boleh dihapus, atau null bila boleh.
     * - Sudah dipakai pelunasan supplier: menghapus merusak data hutang (alokasi ikut cascade).
     * - Sudah ada pembayaran: bukti uang masuk akan hilang (hanya super_admin boleh).
     */
    public function deletionBlocker(?User $by = null): ?string
    {
        $allocated = \Illuminate\Support\Facades\DB::table('supplier_payment_item_allocations')
            ->whereIn('transaction_item_id', $this->items()->select('id'))
            ->exists();
        if ($allocated) {
            return 'Transaksi ini sudah dipakai dalam Pelunasan Supplier sehingga tidak dapat dihapus.';
        }

        if ($this->payments()->exists() && ($by === null || $by->role !== 'super_admin')) {
            return 'Transaksi ini sudah memiliki catatan pembayaran sehingga tidak dapat dihapus. Hubungi Super Admin bila memang harus dihapus.';
        }

        return null;
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
