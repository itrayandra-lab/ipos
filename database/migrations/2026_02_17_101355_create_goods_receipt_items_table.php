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
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->onDelete('set null');
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('quantity_ordered', 15, 2)->default(0);
            $table->decimal('quantity_received', 15, 2);
            $table->decimal('quantity_difference', 15, 2)->default(0); // received - ordered
            $table->text('notes')->nullable(); // catatan jika ada perbedaan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
