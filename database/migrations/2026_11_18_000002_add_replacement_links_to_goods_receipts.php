<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penerimaan barang pengganti return supplier:
 *  - goods_receipts.supplier_return_id        : no. return yang ditautkan (header)
 *  - goods_receipt_items.supplier_return_item_id : item return yang diganti
 *  - purchase_order_items.replaced_qty        : qty pengganti yang sudah diterima
 * Qty bayar item PO = quantity - returned_qty + replaced_qty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_return_id')->nullable()->after('purchase_order_id');
        });
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_return_item_id')->nullable()->after('purchase_order_item_id');
        });
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('replaced_qty', 15, 2)->default(0)->after('returned_qty');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', fn(Blueprint $t) => $t->dropColumn('replaced_qty'));
        Schema::table('goods_receipt_items', fn(Blueprint $t) => $t->dropColumn('supplier_return_item_id'));
        Schema::table('goods_receipts', fn(Blueprint $t) => $t->dropColumn('supplier_return_id'));
    }
};
