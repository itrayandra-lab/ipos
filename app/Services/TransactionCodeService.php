<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionCodeService
{
    /**
     * Generate a transaction code with format: TRyymmnourut
     * Sequence resets every month (3 digits, zero-padded).
     *
     * @param Carbon|null $date  The date to base the code on (defaults to now)
     * @return string
     */
    public static function generate(Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $yymm = $date->format('ym');
        $prefix = "TR{$yymm}";

        // Thread-safe: lock the rows for this month to prevent duplicate sequence numbers
        $lastRecord = DB::table('transactions')
            ->where('transaction_code', 'like', $prefix . '%')
            ->whereNotNull('transaction_code')
            ->orderBy('transaction_code', 'desc')
            ->lockForUpdate()
            ->first(['transaction_code']);

        if ($lastRecord && $lastRecord->transaction_code) {
            // Format is TRyymmnnn, so the sequence is the last 3 characters
            $lastSeq = (int)substr($lastRecord->transaction_code, -3);
            $nextSeq = $lastSeq + 1;
        }
        else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }
}
