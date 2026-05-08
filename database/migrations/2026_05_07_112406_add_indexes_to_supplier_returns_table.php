<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supplier_returns', function (Blueprint $table) {
            $table->index('return_date');
            $table->index('status');
        });

        Schema::table('supplier_return_items', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('product_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_returns', function (Blueprint $table) {
            $table->dropIndex(['return_date']);
            $table->dropIndex(['status']);
        });

        Schema::table('supplier_return_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['product_batch_id']);
        });
    }
};
