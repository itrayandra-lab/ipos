<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah kolom product_hpp sebagai pengganti price_real yang lebih eksplisit.
     * 
     * - product_hpp = HPP / Harga Modal Beli (isi dari price_real yang sudah ada)
     * - Kolom price dan price_real TIDAK disentuh, tetap berfungsi seperti biasa.
     * - Kolom price tetap digunakan sebagai harga jual aktif (het_online nantinya).
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->bigInteger('product_hpp')->default(0)->after('price_real')
                  ->comment('HPP / Harga Modal Beli produk');
        });

        // Salin data dari price_real → product_hpp
        DB::statement('UPDATE product_variants SET product_hpp = price_real WHERE price_real > 0');
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('product_hpp');
        });
    }
};

