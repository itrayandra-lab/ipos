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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('tax_type', ['none', 'ppn'])->default('none')->after('total_amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_type');
            $table->enum('discount_type', ['fixed', 'percent'])->default('fixed')->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
        //
        });
    }
};
