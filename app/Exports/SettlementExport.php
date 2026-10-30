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
    protected $rowNumber = 0;

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
            'Kode Transaksi',
            'Invoice',
            'Tanggal',
            'Nama Produk',
            'Supplier',
            'Jumlah Transaksi',
            'Total Nilai Transaksi',
            'Status Pembayaran',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $no = $this->rowNumber;

        $merekName = trim($row->merek_name ?? '');
        $productName = trim($row->product_name ?? '');

        $originalParts = array_filter([$merekName, $productName]);
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

        $nettoValue = trim((string) ($row->netto_value ?? ''));
        $satuan = trim((string) ($row->satuan ?? ''));
        $size = trim(implode(' ', array_filter([$nettoValue, $satuan])));
        $finalName = trim($finalName . ' ' . $size);

        $status = match ($row->payment_status ?? 'belum') {
            'lunas' => 'Lunas',
            'sebagian' => 'Sebagian',
            default => 'Belum Bayar',
        };

        return [
            $no,
            $row->transaction_code ?? '-',
            $row->invoice_number ?? '-',
            $row->transaction_date ?? '-',
            $finalName,
            $row->supplier_name ?? '-',
            $row->total_qty,
            $row->total_cost,
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
