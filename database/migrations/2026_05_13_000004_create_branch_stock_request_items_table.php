<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stock_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_stock_request_id')->constrained('branch_stock_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('qty_requested');
            $table->unsignedInteger('qty_approved')->nullable(); // set oleh pusat saat approve
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stock_request_items');
    }
};
