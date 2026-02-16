<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateProductsToVariantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = \Illuminate\Support\Facades\DB::table('products')->get();
        foreach ($products as $product) {
            // 1. Create Netto
            $nettoId = \Illuminate\Support\Facades\DB::table('product_nettos')->insertGetId([
                'product_id' => $product->id,
                'netto_value' => $product->neto ?? '-',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create Variant
            $variantId = \Illuminate\Support\Facades\DB::table('product_variants')->insertGetId([
                'product_netto_id' => $nettoId,
                'variant_name' => 'Default',
                'sku_code' => 'SKU-' . str_pad($product->id, 6, '0', STR_PAD_LEFT),
                'price' => $product->price ?? 0,
                'price_real' => $product->price_real ?? 0,
                'stock' => $product->stock ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Update Batches
            \Illuminate\Support\Facades\DB::table('product_batches')
                ->where('product_id', $product->id)
                ->update(['product_variant_id' => $variantId]);

            // 4. Update Transaction Items
            \Illuminate\Support\Facades\DB::table('transaction_items')
                ->where('product_id', $product->id)
                ->update(['product_variant_id' => $variantId]);
        }
    }
}
