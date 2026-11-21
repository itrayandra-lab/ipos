<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'payment_method', 'amount', 'description',
        'reference_id', 'balance_after', 'user_id',
        'expense_category_id', 'transaction_date', 'receipt_photo'
    ];

    /**
     * Hitung ulang balance_after seluruh baris, berurutan menurut tanggal transaksi lalu id.
     * Hanya kas masuk dan kas keluar bermetode cash yang mengubah saldo.
     * Harus dipanggil di dalam DB::transaction (mengunci baris yang dibaca).
     *
     * @throws \RuntimeException jika ada saldo negatif dan $allowNegative = false
     */
    public static function rebuildBalances(bool $allowNegative = false): float
    {
        $rows = static::query()
            ->orderByRaw('COALESCE(transaction_date, DATE(created_at)) asc')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $running = 0.0;
        foreach ($rows as $row) {
            if ($row->type === 'in') {
                $running += (float) $row->amount;
            } elseif ($row->payment_method === 'cash') {
                $running -= (float) $row->amount;
            }

            if (!$allowNegative && $running < -0.001) {
                $date = $row->transaction_date
                    ? \Carbon\Carbon::parse($row->transaction_date)->format('d-m-Y')
                    : $row->created_at->format('d-m-Y');
                throw new \RuntimeException(
                    'Saldo kas kecil tidak mencukupi: saldo menjadi minus Rp '
                    . number_format(abs($running), 0, ',', '.')
                    . ' pada transaksi tanggal ' . $date . ' (' . $row->description . ').'
                );
            }

            if (abs((float) $row->balance_after - $running) > 0.001) {
                static::whereKey($row->id)->update(['balance_after' => $running]);
            }
        }

        return $running;
    }

    /** Saldo terkini = balance_after baris terakhir menurut urutan tanggal lalu id. */
    public static function currentBalance(): float
    {
        $last = static::query()
            ->orderByRaw('COALESCE(transaction_date, DATE(created_at)) desc')
            ->orderByDesc('id')
            ->first();

        return $last ? (float) $last->balance_after : 0.0;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\ExpenseCategory::class, 'expense_category_id');
    }
}
