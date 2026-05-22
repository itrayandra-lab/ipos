<?php

namespace App\Exports\Sheets;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;

class TransactionDataSheet implements FromCollection, WithHeadings, WithTitle, WithEvents, ShouldAutoSize
{
    public function collection()
    {
        return new Collection([
            [
                'TR' . date('ym') . '001',
                date('d/m/Y'),
                'John Doe',
                '08123456789',
                'offline',
                'paid',
                'cash',
                'Transaksi contoh',
                '',
                2,
                50000,
                0,
            ],
            [
                '',
                date('d/m/Y'),
                'Jane Doe',
                '08198765432',
                'offline',
                'credit',
                'transfer',
                '',
                '',
                1,
                75000,
                '',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'transaction_code',
            'transaction_date',
            'customer_name',
            'customer_phone',
            'source',
            'payment_status',
            'payment_method',
            'notes',
            'product_name',
            'qty',
            'price',
            'discount',
        ];
    }

    public function title(): string
    {
        return 'Transaksi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = Product::count();
                $lastProductRow = $rowCount + 1;

                // Data validation dropdown for product_name column (I) - max 500 rows
                if ($rowCount > 0) {
                    $validation = $sheet->getDataValidation('I2:I501');
                    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Produk tidak valid');
                    $validation->setError('Pilih produk dari daftar yang tersedia.');
                    $validation->setPromptTitle('Pilih Produk');
                    $validation->setPrompt('Pilih nama produk dari daftar.');
                    $validation->setFormula1('Referensi Produk!$A$2:$A$' . $lastProductRow);
                }

                // Format date column and number columns
                $sheet->getStyle('B2:B501')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $sheet->getStyle('J2:L3')->getNumberFormat()->setFormatCode('#,##0');

                // Freeze header row
                $sheet->freezePane('A2');

                // Style header
                $sheet->getStyle('A1:L1')->getFont()->setBold(true);
                $sheet->getStyle('A1:L1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF4472C4');
                $sheet->getStyle('A1:L1')->getFont()->getColor()->setARGB('FFFFFFFF');
            },
        ];
    }
}
