<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Roman numeral mapping for months
     */
    private static array $romanMonths = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'V',
        6 => 'VI',
        7 => 'VII',
        8 => 'VIII',
        9 => 'IX',
        10 => 'X',
        11 => 'XI',
        12 => 'XII',
    ];

    /**
     * Generate a unique invoice number with format: INV/BL/{roman_month}/{yy}/{sequence}
     * Sequence resets every month (4 digits, zero-padded).
     *
     * @param Carbon|null $date  The date to base the invoice number on (defaults to now)
     * @return string
     */
    public static function generate(Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();

        $roman = self::$romanMonths[$date->month];
        $yy = $date->format('y');
        $prefix = "INV/BL/{$roman}/{$yy}/";

        // Get the last sequence number for this prefix efficiently
        $last = DB::table('transactions')
            ->where('invoice_number', 'like', $prefix . '%')
            ->latest('id')
            ->value('invoice_number');

        if ($last) {
            $parts = explode('/', $last);
            $lastSeq = (int)end($parts);
            $nextSeq = $lastSeq + 1;
        }
        else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
