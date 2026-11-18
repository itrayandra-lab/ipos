<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Qty return supplier yang terkait item PO. Qty PO (quantity) tetap utuh;
 * qty yang harus dibayar = quantity - returned_qty (dan paid_qty tetap qty yang sudah dibayar).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('returned_qty', 15, 2)->default(0)->after('paid_qty');
        });

        // Backfill dari return yang sudah ada (relasi lewat batch -> item PO langsung / lewat goods receipt)
        DB::statement("
            UPDATE purchase_order_items poi
            SET returned_qty = COALESCE((
                SELECT SUM(sri.qty)
                FROM supplier_return_items sri
                JOIN product_batches pb ON pb.id = sri.product_batch_id
                LEFT JOIN goods_receipt_items gri ON gri.id = pb.goods_receipt_item_id
                WHERE COALESCE(pb.purchase_order_item_id, gri.purchase_order_item_id) = poi.id
            ), 0)
        ");
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('returned_qty');
        });
    }
};
