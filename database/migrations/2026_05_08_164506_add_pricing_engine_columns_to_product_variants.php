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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->bigInteger('hpp_rayandra')->default(0)->after('price_real');
            $table->bigInteger('margin_hpp')->default(0)->after('hpp_rayandra');
            $table->bigInteger('ray_store')->default(0)->after('margin_hpp');
            $table->bigInteger('het_online')->default(0)->after('ray_store');
            $table->boolean('is_approved')->default(false)->after('het_online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['hpp_rayandra', 'margin_hpp', 'ray_store', 'het_online', 'is_approved']);
        });
    }
};
