<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Drop invoices and invoice_items tables — feature has been moved to transactions/transaction_items.
     */
    public function up(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }

    public function down(): void
    {
        // Recreate stubs if needed for rollback
        Schema::create('invoices', function ($table) {
            $table->id();
            $table->timestamps();
        });
        Schema::create('invoice_items', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }
};
