<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_return_id')->constrained('branch_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->unsignedInteger('qty');
            $table->text('reason')->nullable(); // alasan return per item
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_return_items');
    }
};
