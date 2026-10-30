<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('purchase_orders')
                ->nullOnDelete();
        });

        Schema::table('supplier_payment_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->after('supplier_payment_id')
                ->constrained('purchase_order_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payment_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn('purchase_order_item_id');
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
        });
    }
};
