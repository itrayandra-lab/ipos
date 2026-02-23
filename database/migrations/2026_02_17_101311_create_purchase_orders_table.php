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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // PO/BL/YYYY/MM/XXXX
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(11); // PPN
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'received', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
