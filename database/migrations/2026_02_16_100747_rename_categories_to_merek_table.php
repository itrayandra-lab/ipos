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
        Schema::rename('categories', 'merek');

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('category_id', 'merek_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('merek_id', 'category_id');
        });

        Schema::rename('merek', 'categories');
    }
};
