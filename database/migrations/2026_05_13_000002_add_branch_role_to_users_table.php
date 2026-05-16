<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'store_manager', 'finance', 'admin', 'sales', 'branch'])
                  ->default('sales')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'store_manager', 'finance', 'admin', 'sales'])
                  ->default('sales')
                  ->change();
        });
    }
};
