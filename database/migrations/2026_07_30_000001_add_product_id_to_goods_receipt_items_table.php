<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('purchase_order_item_id')->constrained('products')->onDelete('set null');
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_variant_id', 'product_id']);
        });
    }
};
