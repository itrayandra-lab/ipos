<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransactionTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Transaksi' => new Sheets\TransactionDataSheet(),
            'Referensi Produk' => new Sheets\ProductReferenceSheet(),
        ];
    }
}
