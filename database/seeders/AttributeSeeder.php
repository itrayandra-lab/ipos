<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $group = \App\Models\AttributeGroup::firstOrCreate(
            ['code' => 'AFFILIATE_TYPE'],
            ['name' => 'Tipe Affiliate']
        );

        $attributes = ['Dokter', 'Reseller', 'Influencer', 'Sales'];

        foreach ($attributes as $name) {
            \App\Models\Attribute::firstOrCreate(
                ['attribute_group_id' => $group->id, 'name' => $name]
            );
        }
    }
}
