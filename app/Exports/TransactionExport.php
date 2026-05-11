<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $transactions;
    protected $totalSum;
    private $rowNumber = 0;

    public function __construct($transactions, $totalSum = 0)
    {
        $this->transactions = $transactions;
        $this->totalSum = $totalSum;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            ['Total Bayar Keseluruhan', 'Rp ' . number_format($this->totalSum, 0, ',', '.')],
            [], // Spacer
            [
                'No',
                'ID Transaksi',
                'Customer',
                'Produk (Merek + Produk)',
                'Subtotal',
                'Diskon',
                'Total Bayar',
                'Status',
                'Tanggal',
            ]
        ];
    }

    public function map($transaction): array
    {
        $this->rowNumber++;

        $products = [];
        $mainItems = $transaction->items->whereNull('parent_item_id');
        foreach ($mainItems as $item) {
            $merek = $item->product->merek->name ?? '';
            $productName = $item->product->name;
            $qty = $item->qty;
            $products[] = "{$merek} {$productName} ({$qty})";
        }

        return [
            $this->rowNumber,
            $transaction->transaction_code ?: '#'.$transaction->id,
            $transaction->customer->name ?? ($transaction->customer_name ?? '-'),
            implode(", ", $products),
            $transaction->items->sum('subtotal') + $transaction->items->sum('discount'),
            $transaction->discount + $transaction->items->sum('discount'),
            $transaction->total_amount,
            ucfirst($transaction->payment_status),
            $transaction->created_at->format('d-m-Y'),
        ];
    }
}
