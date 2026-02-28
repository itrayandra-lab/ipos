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
        Schema::create('transaction_payments', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $blueprint->bigInteger('amount');
            $blueprint->date('payment_date');
            $blueprint->string('payment_method');
            $blueprint->string('payment_receipt')->nullable();
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};
