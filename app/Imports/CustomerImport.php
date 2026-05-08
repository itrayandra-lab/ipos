<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $name = $row['nama_customer'] ?? $row['name'] ?? null;
            $phone = $row['waphone_number'] ?? $row['wa_phone_number'] ?? $row['phone'] ?? null;
            
            // Skip if mandatory fields are missing
            if (!$name || !$phone) {
                continue;
            }

            // Cleaning phone number (removing possible spaces or dashes)
            $phone = str_replace([' ', '-', '(', ')', '+'], '', $phone);

            Customer::updateOrCreate(
                ['phone' => $phone], // Unique key
                [
                    'name'    => $name,
                    'email'   => $row['email'] ?? null,
                    'address' => $row['alamat'] ?? $row['address'] ?? null,
                ]
            );
        }
    }
}
