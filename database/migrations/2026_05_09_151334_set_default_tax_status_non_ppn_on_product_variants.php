<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set default value of tax_status to 0 (Non PPN)
        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('tax_status')->default(0)->change();
        });

        // Also update any existing variants that have NULL tax_status to 0
        DB::table('product_variants')->whereNull('tax_status')->update(['tax_status' => 0]);
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('tax_status')->default(1)->change();
        });
    }
};
