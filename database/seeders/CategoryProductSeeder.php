<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merek;
use App\Models\Product;
use Illuminate\Support\Str;

class CategoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'DERMOND' => [
                ['name' => 'DERMOND Reboot Cream'],
                ['name' => 'DERMOND MenGuard Intimate Foam'],
                ['name' => 'DERMOND FreshCore Mist'],
            ],
            'EGGSHELLENT' => [
                ['name' => 'EGGSHELLENT Serum Anti-Aging', 'netto' => '20 ML', 'qty' => 20],
            ],
            'LUECIÉLLEDERM' => [
                ['name' => 'Radiance A Pelle Gold Serum'],
                ['name' => 'Luminare A Pelle Body Lotion'],
                ['name' => 'Luce A Pelle Licorice Serum'],
                ['name' => 'Iluminate A Pelle Body Lotion'],
                ['name' => 'Ammor A Pelle Body Wash'],
            ],
            'BABYLATORY' => [
                ['name' => 'Hydra-Moist Baby Cream', 'price' => 42570, 'qty' => 20],
                ['name' => 'Top to Toe Baby Smile Wash', 'price' => 41140, 'qty' => 20],
                ['name' => 'First Touch Gentle Baby Cream', 'price' => 41140, 'qty' => 20],
            ],
            'ADHWA' => [
                ['name' => 'Safa Serenity Sunscreen Protector', 'price' => 42570, 'qty' => 15],
                ['name' => 'Safa Serenity Body Lotion', 'price' => 0, 'qty' => 15],
                ['name' => 'Safa Serenity Radiance Face Mist', 'price' => 0, 'qty' => 15],
                ['name' => 'Safa Serenity Nourishing Moist Balm', 'price' => 0, 'qty' => 15],
                ['name' => 'Serenity Lip Balm', 'price' => 0, 'qty' => 15],
                ['name' => 'Serenity Facial Cleanser', 'price' => 0, 'qty' => 15],
                ['name' => 'Safa Serenity Massage Lotion', 'price' => 0, 'qty' => 15],
            ],
            'MOMMYLATORY' => [
                ['name' => 'Cica Peptide Intensive Stretch Mark Cream', 'price' => 42570, 'qty' => 20],
                ['name' => 'SilkSkin Renewal Cream', 'netto' => '10 GR', 'price' => 41140, 'qty' => 20],
                ['name' => 'Daily Intimate Wash Triple Protection', 'price' => 41140, 'qty' => 20],
            ],
            'DERMALINK' => [
                ['name' => 'HyaloPan Moistboost Serum', 'netto' => '20 ml', 'price' => 42570, 'qty' => 20],
                ['name' => 'NiaProCica Bright Serum', 'netto' => '20 ml', 'price' => 41140, 'qty' => 20],
                ['name' => 'Acne Treatment Night Cream', 'netto' => '10 gr', 'price' => 36621, 'qty' => 20],
                ['name' => 'Radiance Facial Wash', 'netto' => '100 ml', 'price' => 38883, 'qty' => 20],
                ['name' => 'Radiance Essence Toner', 'netto' => '100 ml', 'price' => 41500, 'qty' => 20],
                ['name' => 'Acne Facial Wash', 'netto' => '100 ml', 'price' => 38701, 'qty' => 20],
                ['name' => 'Acne Essence Toner', 'netto' => '100 ml', 'price' => 43613, 'qty' => 20],
                ['name' => 'Radiance Milky Cleansing', 'netto' => '100 ml', 'price' => 44422, 'qty' => 20],
                ['name' => 'Radiance Shield Broad Spectrum Protector Sunscreen', 'netto' => '10 gr', 'price' => 33740, 'qty' => 20],
                ['name' => 'Radiance Night Repair Cream', 'netto' => '10 gr', 'price' => 44562, 'qty' => 20],
                ['name' => 'Acne Defense Broad Spectrum Protector Sunscreen', 'netto' => '10 gr', 'price' => 39235, 'qty' => 20],
                ['name' => 'CeraFix Moisturizer', 'netto' => '15 gr', 'price' => 0, 'qty' => 20],
                ['name' => 'Acne Milky Cleansing', 'netto' => '100 ml', 'price' => 49541, 'qty' => 20],
            ],
            'BEAUTYLATORY' => [
                ['name' => 'UpG Moisturizer', 'netto' => '30 GR'],
            ],
            'SAM SUN AND MOON' => [
                ['name' => 'Sun Protection'],
                ['name' => 'Prebio Hydra Cream'],
                ['name' => 'Laveu Gentle Shower Gel'],
            ],
            'MOMALAYA' => [
                ['name' => 'Mangosteen Serum'],
                ['name' => 'Mangosteen Eye Patch with Collagen'],
                ['name' => 'Soy Isoflavon Anti Aging Serum'],
            ],
            'CREYA' => [
                ['name' => 'Sacha Inchi Miracle Oil Frangipani Bliss', 'netto' => '20 ml'],
                ['name' => 'Sacha Inchi Miracle Oil'],
                ['name' => 'Deep Hydration Face Serum'],
                ['name' => 'Miracle Oil Sacha Inci'],
            ],
            'COSMETORY' => [
                ['name' => 'Sunshield MoistureBoost'],
                ['name' => 'Ocean Waves Glaze Brightening Body Lotion'],
                ['name' => 'UV Defense System Cream'],
                ['name' => 'Suede Lotus Glaze Body Mist'],
                ['name' => 'Gentle Daily Face Toner'],
                ['name' => 'Ocean Waves Body Mist'],
                ['name' => 'Polypeptide Liposome Serum for Anti-Aging'],
                ['name' => 'Suede Lotus Glaze Brightening Body Lotion'],
                ['name' => 'Youth Firming Dew Body Serum'],
            ],
        ];

        foreach ($data as $merek => $products) {
            $mrk = Merek::updateOrCreate(
            ['slug' => Str::slug($merek)],
            [
                'name' => $merek,
                'description' => $merek . ' products'
            ]
            );

            foreach ($products as $p) {
                Product::updateOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'merek_id' => $mrk->id,
                    'name' => $p['name'],
                    'price' => $p['price'] ?? 0,
                    'stock' => $p['qty'] ?? 0,
                ]
                );
            }
        }
    }
}
