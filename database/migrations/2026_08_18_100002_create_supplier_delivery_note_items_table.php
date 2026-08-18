<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_delivery_note_id')->constrained('supplier_delivery_notes')->onDelete('cascade');
            $table->foreignId('product_batch_id')->constrained('product_batches')->onDelete('restrict');
            $table->integer('qty')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_delivery_note_items');
    }
};
