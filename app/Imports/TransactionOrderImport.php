<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TransactionOrderImport implements ToCollection, WithHeadingRow
{
    protected Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function rows(): Collection
    {
        return $this->rows ?? collect();
    }
}
