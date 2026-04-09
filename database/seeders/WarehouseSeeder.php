<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::create([
            'name' => 'Gudang Utama (Beautylatory)',
            'address' => 'Bandung',
            'type' => 'main'
        ]);
        Warehouse::create([
            'name' => 'Store Novus',
            'address' => 'Bandung',
            'type' => 'branch'
        ]);
        Warehouse::create([
            'name' => 'Store Apotek',
            'address' => 'Bandung',
            'type' => 'branch'
        ]);
    }
}
