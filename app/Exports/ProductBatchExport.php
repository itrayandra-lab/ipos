<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductBatchExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Produk',
            'Netto Varian',
            'No Batch',
            'Qty',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $product = trim(($row->merek_name ?? '') . ' ' . ($row->product_name ?? ''));
        $netto = trim(($row->netto_value ?? '') . ' ' . ($row->satuan ?? ''));

        return [
            $no,
            $row->transaction_date,
            $product,
            $netto ?: '-',
            $row->batch_no ?? '-',
            $row->qty,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
