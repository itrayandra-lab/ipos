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
        Schema::create('channel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->enum('margin_type', ['fixed', 'percentage'])->default('percentage');
            $table->bigInteger('margin_value')->default(0);
            $table->enum('fee_type', ['fixed', 'percentage'])->default('percentage');
            $table->bigInteger('fee_value')->default(0);
            $table->bigInteger('fixed_cost')->default(0);
            $table->bigInteger('shipping_subsidy')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_settings');
    }
};
