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
        // 1. Add is_bundle to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_bundle')) {
                $table->boolean('is_bundle')->default(false)->after('status');
            }
        });

        // 2. Create bundle_items table
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
        });

        // 3. Add parent_item_id to transaction_items table for tracking bundle components
        Schema::table('transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_items', 'parent_item_id')) {
                $table->unsignedBigInteger('parent_item_id')->nullable()->after('transaction_id');
                $table->foreign('parent_item_id')->references('id')->on('transaction_items')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['parent_item_id']);
            $table->dropColumn('parent_item_id');
        });

        Schema::dropIfExists('bundle_items');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_bundle');
        });
    }
};
