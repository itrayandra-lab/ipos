<?php

namespace Database\Seeders;

use App\Models\ProductTier;
use Illuminate\Database\Seeder;

class ProductTierSeeder extends Seeder
{
    public function run(): void
    {
        ProductTier::updateOrCreate(['name' => 'Core'], ['multiplier' => 1.5]);
        ProductTier::updateOrCreate(['name' => 'Entry'], ['multiplier' => 2.0]);
        ProductTier::updateOrCreate(['name' => 'Hero'], ['multiplier' => 2.5]);
    }
}
