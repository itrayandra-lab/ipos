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
            $table->enum('transaction_type', ['produk', 'kelas'])->default('produk')->after('invoice_number');
            $table->decimal('down_payment', 15, 2)->default(0)->after('total_amount');
            $table->boolean('is_dp')->default(false)->after('down_payment');
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
