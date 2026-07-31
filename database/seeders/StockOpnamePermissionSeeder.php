<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class StockOpnamePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Stock Opname (Input)', 'slug' => 'access_stock_opname', 'group' => 'Manajemen Stok'],
            ['name' => 'Stock Opname (Approve)', 'slug' => 'approve_stock_opname', 'group' => 'Manajemen Stok'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
