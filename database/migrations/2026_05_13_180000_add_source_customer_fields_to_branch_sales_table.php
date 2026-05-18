<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_sales', function (Blueprint $table) {
            $table->string('source', 50)->nullable()->after('total_amount');
            $table->string('customer_name', 200)->nullable()->after('source');
            $table->string('external_order_id', 100)->nullable()->after('customer_name');
            $table->string('payment_receipt', 255)->nullable()->after('external_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('branch_sales', function (Blueprint $table) {
            $table->dropColumn(['source', 'customer_name', 'external_order_id', 'payment_receipt']);
        });
    }
};
