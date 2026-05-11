<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SettlementExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Nama Produk',
            'HPP Satuan',
            'Total Terjual',
            'Total',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $merekName = trim($row->merek_name ?? '');
        $productName = trim($row->product_name ?? '');
        $variantName = trim($row->variant_name ?? '');

        $originalParts = array_filter([$merekName, $productName, $variantName]);
        $finalParts = [];
        foreach ($originalParts as $p1) {
            $isSubPart = false;
            foreach ($originalParts as $p2) {
                if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                    $isSubPart = true;
                    break;
                }
            }
            if (!$isSubPart) {
                $finalParts[] = $p1;
            }
        }
        $finalName = implode(' ', array_unique($finalParts));

        return [
            $no,
            $finalName,
            $row->buy_price,
            $row->total_qty,
            $row->total_cost,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
