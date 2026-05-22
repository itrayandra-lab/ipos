<?php

namespace App\Exports\Sheets;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class CustomerReferenceSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return Customer::orderBy('name')
            ->get(['name', 'phone'])
            ->map(function ($c) {
                return [$c->name, $c->phone ?? '-'];
            });
    }

    public function headings(): array
    {
        return ['Nama Pelanggan', 'No. WA/Telepon'];
    }

    public function title(): string
    {
        return 'Referensi Pelanggan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:B1')->getFont()->setBold(true);
                $sheet->getStyle('A1:B1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF70AD47');
                $sheet->getStyle('A1:B1')->getFont()->getColor()->setARGB('FFFFFFFF');

                // Make header row sticky (won't scroll)
                $sheet->freezePane('A2');
            },
        ];
    }
}
