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
        Schema::table('channel_settings', function (Blueprint $table) {
            $table->dropColumn(['margin_type', 'margin_value', 'fee_type', 'fee_value', 'fixed_cost', 'shipping_subsidy']);
            $table->json('factors')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channel_settings', function (Blueprint $table) {
            $table->dropColumn('factors');
            $table->enum('margin_type', ['fixed', 'percentage'])->default('percentage');
            $table->bigInteger('margin_value')->default(0);
            $table->enum('fee_type', ['fixed', 'percentage'])->default('percentage');
            $table->bigInteger('fee_value')->default(0);
            $table->bigInteger('fixed_cost')->default(0);
            $table->bigInteger('shipping_subsidy')->default(0);
        });
    }
};
