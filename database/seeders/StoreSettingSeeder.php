<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreSetting;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StoreSetting::create([
            'store_name' => 'LUNARAY IPOS',
            'logo_path' => 'assets/img/logo-black.png',
            'address' => 'Parahyangan St No.Kav. 11, Kota Baru, Padalarang, West Bandung Regency, West Java 40553',
            'phone' => '0812-xxxx-xxxx',
            'whatsapp' => '0812-xxxx-xxxx',
            'email' => 'admin@beautylatory.com',
            'footer_text' => 'Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.',
        ]);
    }
}
