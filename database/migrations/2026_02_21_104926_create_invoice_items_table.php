<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Drop the invoice_items table — feature moved to transaction_items table.
     */
    public function up(): void
    {
        Schema::dropIfExists('invoice_items');
    }

    public function down(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
