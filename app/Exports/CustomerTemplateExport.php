<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class CustomerTemplateExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Return empty collection for template
        return new Collection([]);
    }

    public function headings(): array
    {
        return [
            'Nama Customer',
            'WA/Phone Number',
            'Email',
            'Alamat',
        ];
    }
}
