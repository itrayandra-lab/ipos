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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('product_types', function (Blueprint $table) {
            if (!Schema::hasColumn('product_types', 'sub_category_id')) {
                $table->foreignId('sub_category_id')->nullable()->after('id')->constrained('sub_categories')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            if (Schema::hasColumn('product_types', 'sub_category_id')) {
                $table->dropForeign(['sub_category_id']);
                $table->dropColumn('sub_category_id');
            }
        });
        Schema::dropIfExists('sub_categories');
    }
};
