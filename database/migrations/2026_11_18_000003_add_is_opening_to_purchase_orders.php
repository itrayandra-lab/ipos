<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tandai PO "stok awal" hasil import SO. Penjualan sebelum tanggal PO tersebut tidak dialokasikan
 * ke qty PO ini (qty PO ini hanya untuk stok baru hasil SO).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('is_opening')->default(false)->after('status');
        });
        DB::table('purchase_orders')->where('notes', 'like', 'Stok awal hasil SO%')->update(['is_opening' => true]);
    }

    public function down(): void
    {
        Schema::table('purchase_orders', fn(Blueprint $t) => $t->dropColumn('is_opening'));
    }
};
