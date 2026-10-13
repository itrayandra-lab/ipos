<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft delete untuk batch: hapus dari Adjustment = arsip,
     * bisa dikembalikan, tidak menghilangkan riwayat.
     */
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
