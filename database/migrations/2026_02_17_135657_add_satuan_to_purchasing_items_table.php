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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('satuan')->nullable()->after('description');
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->string('satuan')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};
