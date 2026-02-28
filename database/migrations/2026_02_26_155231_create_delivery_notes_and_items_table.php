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
        Schema::create('delivery_notes', function (Blueprint $create) {
            $create->id();
            $create->unsignedBigInteger('user_id')->nullable();
            $create->unsignedBigInteger('customer_id')->nullable();
            $create->string('customer_name')->nullable();
            $create->string('customer_phone')->nullable();
            $create->string('delivery_note_no')->nullable();
            $create->date('transaction_date')->nullable();
            $create->string('delivery_type')->nullable();
            $create->text('notes')->nullable();
            $create->timestamps();
        });

        Schema::create('delivery_note_items', function (Blueprint $create) {
            $create->id();
            $create->unsignedBigInteger('delivery_note_id');
            $create->unsignedBigInteger('product_id')->nullable();
            $create->unsignedBigInteger('product_batch_id')->nullable();
            $create->integer('qty')->default(0);
            $create->timestamps();

            $create->foreign('delivery_note_id')->references('id')->on('delivery_notes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};
