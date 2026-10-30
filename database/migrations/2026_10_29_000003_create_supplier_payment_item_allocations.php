<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payment_item_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_payment_item_id')
                ->constrained('supplier_payment_items', 'id', 'spa_payment_item_fk')
                ->cascadeOnDelete();
            $table->foreignId('transaction_item_id')
                ->constrained('transaction_items', 'id', 'spa_transaction_item_fk')
                ->cascadeOnDelete();
            $table->decimal('quantity', 15, 2);
            $table->timestamps();

            $table->unique(['supplier_payment_item_id', 'transaction_item_id'], 'payment_item_transaction_unique');
            $table->index('transaction_item_id', 'payment_allocations_transaction_item_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_item_allocations');
    }
};
