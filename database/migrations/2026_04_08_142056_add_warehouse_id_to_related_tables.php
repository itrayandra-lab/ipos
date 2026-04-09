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
        Schema::table('product_batches', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('id')->constrained('warehouses')->onDelete('cascade');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('id')->constrained('warehouses')->onDelete('cascade');
        });
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('id')->constrained('warehouses')->onDelete('cascade');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('user_id')->constrained('warehouses')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};
