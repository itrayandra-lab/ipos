<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_returns', function (Blueprint $table) {
            $table->string('warehouse_code', 50)->nullable()->after('branch_warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::table('branch_returns', function (Blueprint $table) {
            $table->dropColumn('warehouse_code');
        });
    }
};
