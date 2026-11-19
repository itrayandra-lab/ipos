<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PoReceiptService
{
    /**
     * Buat goods receipt (confirmed) untuk batch yang sudah terhubung ke item PO tetapi belum punya
     * penerimaan barang, supaya qty diterima tercatat (dipakai alokasi pembayaran supplier).
     * Idempotent: batch yang sudah punya goods_receipt_item_id dilewati.
     *
     * @return int jumlah goods receipt item yang dibuat
     */
    public function createForPo(int $poId, ?int $userId = null, ?string $notes = null): int
    {
        $po = DB::table('purchase_orders')->find($poId);
        if (!$po) {
            return 0;
        }

        $batches = DB::table('product_batches as b')
            ->join('purchase_order_items as i', 'i.id', '=', 'b.purchase_order_item_id')
            ->where('i.purchase_order_id', $poId)
            ->whereNull('b.goods_receipt_item_id')
            ->whereNull('b.deleted_at')
            ->orderBy('b.id')
            ->get(['b.*', 'i.product_name', 'i.satuan', 'i.quantity as qty_ordered']);
        if ($batches->isEmpty()) {
            return 0;
        }

        $date = now()->toDateString();
        $prefix = 'GR/' . now()->format('my') . '/';
        $last = DB::table('goods_receipts')->where('sj_number', 'like', $prefix . '%')->orderByDesc('sj_number')->value('sj_number');
        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        $grId = DB::table('goods_receipts')->insertGetId([
            'warehouse_id' => $po->warehouse_id,
            'sj_number' => $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'purchase_order_id' => $poId,
            'supplier_id' => $po->supplier_id,
            'delivery_note_number' => '-',
            'delivery_date' => $date,
            'received_date' => $date,
            'received_by' => $userId,
            'notes' => $notes,
            'status' => 'confirmed',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $n = 0;
        foreach ($batches as $b) {
            $itemId = DB::table('goods_receipt_items')->insertGetId([
                'goods_receipt_id' => $grId,
                'purchase_order_item_id' => $b->purchase_order_item_id,
                'product_id' => $b->product_id,
                'product_variant_id' => $b->product_variant_id,
                'product_name' => $b->product_name,
                'satuan' => $b->satuan,
                'batch_no' => $b->batch_no,
                'expiry_date' => $b->expiry_date,
                // qty PO hasil import = total stok; per batch tampilkan qty batch agar baris cocok
                'quantity_ordered' => $b->qty,
                'quantity_received' => $b->qty,
                'quantity_difference' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('product_batches')->where('id', $b->id)->update(['goods_receipt_item_id' => $itemId]);
            $n++;
        }

        return $n;
    }
}
