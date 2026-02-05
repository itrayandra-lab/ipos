<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChannelSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            [
                'slug' => 'offline',
                'name' => 'Offline Store',
                'margin_type' => 'percentage',
                'margin_value' => 20,
                'fee_type' => 'fixed',
                'fee_value' => 0,
                'fixed_cost' => 0,
                'shipping_subsidy' => 0,
            ],
            [
                'slug' => 'shopee',
                'name' => 'Shopee',
                'margin_type' => 'percentage',
                'margin_value' => 20,
                'fee_type' => 'percentage',
                'fee_value' => 10,
                'fixed_cost' => 0,
                'shipping_subsidy' => 0,
            ],
            [
                'slug' => 'tokopedia',
                'name' => 'Tokopedia',
                'margin_type' => 'percentage',
                'margin_value' => 20,
                'fee_type' => 'percentage',
                'fee_value' => 8,
                'fixed_cost' => 0,
                'shipping_subsidy' => 0,
            ],
            [
                'slug' => 'tiktok',
                'name' => 'TikTok Shop',
                'margin_type' => 'percentage',
                'margin_value' => 20,
                'fee_type' => 'percentage',
                'fee_value' => 12,
                'fixed_cost' => 0,
                'shipping_subsidy' => 0,
            ],
        ];

        foreach ($channels as $channel) {
            \App\Models\ChannelSetting::updateOrCreate(['slug' => $channel['slug']], $channel);
        }
    }
}
