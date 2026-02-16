<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add category_id to products if it doesn't exist
        if (!Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('merek_id')->constrained('categories')->onDelete('set null');
            });
        }

        // 2. Try to sync existing category data before dropping sub_categories
        try {
            DB::statement("
                UPDATE products p
                JOIN product_types pt ON p.product_type_id = pt.id
                JOIN sub_categories sc ON pt.sub_category_id = sc.id
                SET p.category_id = sc.category_id
            ");
        }
        catch (\Exception $e) {
        // If tables/columns missing, skip sync
        }

        // 3. Remove sub_category_id from product_types
        Schema::table('product_types', function (Blueprint $table) {
            if (Schema::hasColumn('product_types', 'sub_category_id')) {
                $table->dropForeign(['sub_category_id']);
                $table->dropColumn('sub_category_id');
            }
        });

        // 4. Drop sub_categories table
        Schema::dropIfExists('sub_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversed migration would be complex to restore exactly as it was, 
        // but let's at least recreate the column and table.

        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('product_types', function (Blueprint $table) {
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->onDelete('cascade');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
