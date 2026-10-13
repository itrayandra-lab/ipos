<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Taut permanen batch tujuan transfer ke dokumen movement-nya.
     * receive() mengisi otomatis; baris lama di-backfill bila cocok ketat.
     */
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->foreignId('stock_movement_id')->nullable()->after('goods_receipt_item_id')
                ->constrained('stock_movements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_movement_id');
        });
    }
};
