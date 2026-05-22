<?php

namespace App\Exports\Sheets;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class ProductReferenceSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return Product::orderBy('name')
            ->get(['name', 'code'])
            ->map(function ($p) {
                return [$p->name, $p->code ?? '-'];
            });
    }

    public function headings(): array
    {
        return ['Nama Produk', 'Kode'];
    }

    public function title(): string
    {
        return 'Referensi Produk';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:B1')->getFont()->setBold(true);
                $sheet->getStyle('A1:B1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFED7D31');
                $sheet->getStyle('A1:B1')->getFont()->getColor()->setARGB('FFFFFFFF');

                // Make header row sticky (won't scroll)
                $sheet->freezePane('A2');
            },
        ];
    }
}
