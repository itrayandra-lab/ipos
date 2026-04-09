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
        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')->constrained('stock_movements')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('product_batch_id')->constrained('product_batches');
            $table->integer('qty');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
    }
};
