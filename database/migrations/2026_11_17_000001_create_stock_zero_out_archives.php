<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_zero_out_archives', function (Blueprint $t) {
            $t->id();
            $t->string('run_label')->index();
            $t->unsignedBigInteger('batch_id')->index();
            $t->unsignedBigInteger('product_id')->nullable();
            $t->unsignedBigInteger('product_variant_id')->nullable();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->unsignedBigInteger('supplier_id')->nullable();
            $t->string('batch_no')->nullable();
            $t->date('expiry_date')->nullable();
            $t->decimal('buy_price', 15, 2)->nullable();
            $t->integer('qty_m')->default(0);
            $t->integer('sales_t')->default(0);
            $t->integer('return_r')->default(0);
            $t->integer('return_received_rr')->default(0);
            $t->integer('movement_mv')->default(0);
            $t->integer('stock_s')->default(0);
            $t->string('po_number')->nullable();
            $t->string('movement_ref')->nullable();
            $t->string('action')->default('none'); // none | return | adjustment_in
            $t->integer('action_qty')->default(0);
            $t->string('note')->nullable();
            $t->timestamp('created_at')->nullable();
        });

        Schema::create('purchase_order_number_archives', function (Blueprint $t) {
            $t->id();
            $t->string('run_label')->index();
            $t->unsignedBigInteger('purchase_order_id')->index();
            $t->string('old_po_number');
            $t->string('new_po_number');
            $t->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_number_archives');
        Schema::dropIfExists('stock_zero_out_archives');
    }
};
