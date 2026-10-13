<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Produk',
            'Netto',
            'Gudang',
            'Jml Batch',
            'Stok Saat Ini',
            'Status',
            'Harga Beli Rata-rata',
            'Nilai Stok',
            'Kadaluarsa Terdekat',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row['product_name'],
            $row['netto'],
            $row['warehouse_name'],
            $row['batch_count'],
            $row['total_current_stock'],
            ucfirst($row['stock_status']),
            $row['avg_buy_price'],
            $row['stock_value'],
            $row['nearest_expiry'] ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
