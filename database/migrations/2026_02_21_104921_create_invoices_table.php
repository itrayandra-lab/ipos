<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Drop the invoices table — feature moved to transactions table.
     */
    public function up(): void
    {
        Schema::dropIfExists('invoices');
    }

    public function down(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
