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
                $table->unsignedBigInteger('category_id')->nullable()->after('merek_id');
            });
            
            // Add foreign key separately to avoid duplicate key error
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key already exists, skip
            }
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
        if (Schema::hasColumn('product_types', 'sub_category_id')) {
            Schema::table('product_types', function (Blueprint $table) {
                try {
                    $table->dropForeign(['sub_category_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, skip
                }
                $table->dropColumn('sub_category_id');
            });
        }

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

        if (!Schema::hasColumn('product_types', 'sub_category_id')) {
            Schema::table('product_types', function (Blueprint $table) {
                $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, skip
                }
                $table->dropColumn('category_id');
            });
        }
    }
};
