<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->date('payment_date');
            $table->decimal('total_amount', 15, 2);
            $table->string('payment_proof')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('supplier_payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_payment_id')->constrained('supplier_payments')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null');
            $table->decimal('qty', 15, 2);
            $table->decimal('buy_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('status');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_status');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('paid_qty', 15, 2)->default(0)->after('quantity');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('supplier_payment_id')->nullable()->constrained('supplier_payments')->onDelete('set null')->after('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['supplier_payment_id']);
            $table->dropColumn('supplier_payment_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('paid_qty');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount']);
        });

        Schema::dropIfExists('supplier_payment_items');
        Schema::dropIfExists('supplier_payments');
    }
};
