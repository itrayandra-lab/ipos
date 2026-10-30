<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Alokasi penjualan lintas ID batch dalam 1 gudang (Model Jurnal M-K-S).
     * Urutan: FIFO berdasarkan tanggal penerimaan.
     * M = qty batch (tetap), S = current_stock. Tidak mengurangi M.
     *
     * @return array [['batch' => ProductBatch, 'take' => int], ...]
     * @throws \Exception jika total S < qty dibutuhkan
     */
    public function allocateAcrossBatches($productId, $variantId, $warehouseId, $qtyNeeded)
    {
        $batches = ProductBatch::query()
            ->leftJoin('goods_receipt_items as receipt_items', 'receipt_items.id', '=', 'product_batches.goods_receipt_item_id')
            ->leftJoin('goods_receipts', 'goods_receipts.id', '=', 'receipt_items.goods_receipt_id')
            ->where('product_batches.product_id', $productId)
            ->where('product_batches.warehouse_id', $warehouseId)
            ->when($variantId, fn($q) => $q->where('product_batches.product_variant_id', $variantId),
                fn($q) => $q->whereNull('product_batches.product_variant_id'))
            ->select('product_batches.*')
            ->orderByRaw('COALESCE(goods_receipts.received_date, product_batches.created_at) ASC')
            ->orderBy('product_batches.id')
            ->get()
            ->filter(fn($b) => $b->current_stock > 0)
            ->values();

        $totalAvailable = $batches->sum(fn($b) => $b->current_stock);
        if ($totalAvailable < $qtyNeeded) {
            $product = Product::find($productId);
            $name = $product ? $product->name : ('produk #' . $productId);
            throw new \Exception("Stok {$name} di gudang tidak mencukupi (tersisa S: {$totalAvailable}). Butuh: {$qtyNeeded}.");
        }

        $remaining = $qtyNeeded;
        $slices = [];
        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            $take = min($batch->current_stock, $remaining);
            $slices[] = ['batch' => $batch, 'take' => $take];
            $remaining -= $take;
        }

        return $slices;
    }

    /**
     * Deduct stock using FIFO (First In First Out) logic.
     * It creates TransactionItem records for each batch consumed.
     * Model Jurnal: M tidak dikurangi; keluar tercatat via TI (masuk K).
     *
     * @param int $productId
     * @param int $qtyNeeded
     * @param int $transactionId
     * @param int|null $parentItemId Link to bundle product if this is a component
     * @param int|null $warehouseId Batasi ke gudang transaksi (null = semua gudang, legacy)
     * @return void
     */
    public function deductStockFIFO($productId, $qtyNeeded, $transactionId, $parentItemId = null, $warehouseId = null)
    {
        $batches = ProductBatch::query()
            ->leftJoin('goods_receipt_items as receipt_items', 'receipt_items.id', '=', 'product_batches.goods_receipt_item_id')
            ->leftJoin('goods_receipts', 'goods_receipts.id', '=', 'receipt_items.goods_receipt_id')
            ->where('product_batches.product_id', $productId)
            ->when($warehouseId, fn($q) => $q->where('product_batches.warehouse_id', $warehouseId))
            ->select('product_batches.*')
            ->orderByRaw('COALESCE(goods_receipts.received_date, product_batches.created_at) ASC')
            ->orderBy('product_batches.id')
            ->get()
            ->filter(function ($batch) {
                return $batch->current_stock > 0;
            });

        $remaining = $qtyNeeded;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $availableInBatch = $batch->current_stock;
            $take = min($availableInBatch, $remaining);

            // Create TransactionItem for this batch
            TransactionItem::create([
                'transaction_id' => $transactionId,
                'parent_item_id' => $parentItemId,
                'product_id'     => $productId,
                'product_batch_id' => $batch->id,
                'qty'            => $take,
                'buy_price'      => $batch->buy_price ?? 0,
                'price'          => 0, // Components set to 0, bundle main item holds the price
                'discount'       => 0,
                'subtotal'       => 0,
            ]);

            $remaining -= $take;
        }

        // Note: If $remaining > 0 here, it means we sold more than we have in batches.
        // In a strict system, this might throw an exception, but for now, we just complete.
    }

    // Keep the legacy method name for callers outside this service.
    public function deductStockFEFO($productId, $qtyNeeded, $transactionId, $parentItemId = null, $warehouseId = null)
    {
        return $this->deductStockFIFO($productId, $qtyNeeded, $transactionId, $parentItemId, $warehouseId);
    }

    /**
     * Process stock for a sold bundle, exploding it into components and deducting their stock.
     */
    public function explodeBundleComponents($bundleId, $bundleQty, $transactionId, $parentItemId, $warehouseId = null)
    {
        $product = Product::with('bundleItems')->findOrFail($bundleId);
        if (!$product->is_bundle) return;

        foreach ($product->bundleItems as $component) {
            $totalComponentQty = $component->quantity * $bundleQty;
            $this->deductStockFIFO($component->product_id, $totalComponentQty, $transactionId, $parentItemId, $warehouseId);
        }
    }

    /**
     * Process stock for a sold item, exploding bundles if necessary.
     */
    public function processSaleItem($productId, $qty, $transactionId, $price, $subtotal, $batchId = null)
    {
        return DB::transaction(function () use ($productId, $qty, $transactionId, $price, $subtotal, $batchId) {
            $product = Product::findOrFail($productId);

            // 1. Create the main transaction record
            $mainItem = TransactionItem::create([
                'transaction_id' => $transactionId,
                'product_id'     => $productId,
                'product_batch_id' => $batchId, // Could be null for bundle
                'qty'            => $qty,
                'price'          => $price,
                'subtotal'       => $subtotal,
            ]);

            // 2. If it's a bundle, handle components
            if ($product->is_bundle) {
                $components = $product->bundleItems;
                foreach ($components as $component) {
                    $totalComponentQty = $component->quantity * $qty;
                    $this->deductStockFIFO($component->product_id, $totalComponentQty, $transactionId, $mainItem->id);
                }
            }

            return $mainItem;
        });
    }
}
