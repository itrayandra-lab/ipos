<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Deduct stock using FEFO (First Expired First Out) logic.
     * It creates TransactionItem records for each batch consumed.
     *
     * @param int $productId
     * @param int $qtyNeeded
     * @param int $transactionId
     * @param int|null $parentItemId Link to bundle product if this is a component
     * @return void
     */
    public function deductStockFEFO($productId, $qtyNeeded, $transactionId, $parentItemId = null)
    {
        // 1. Get available batches for this product that have stock
        // Filter those where current_stock > 0
        // current_stock = qty - sum(transaction_items.qty)
        $batches = ProductBatch::where('product_id', $productId)
            ->get()
            ->filter(function ($batch) {
                return $batch->current_stock > 0;
            })
            ->sortBy('expiry_date'); // FEFO logic

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

    /**
     * Process stock for a sold bundle, exploding it into components and deducting their stock.
     */
    public function explodeBundleComponents($bundleId, $bundleQty, $transactionId, $parentItemId)
    {
        $product = Product::with('bundleItems')->findOrFail($bundleId);
        if (!$product->is_bundle) return;

        foreach ($product->bundleItems as $component) {
            $totalComponentQty = $component->quantity * $bundleQty;
            $this->deductStockFEFO($component->product_id, $totalComponentQty, $transactionId, $parentItemId);
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
                    $this->deductStockFEFO($component->product_id, $totalComponentQty, $transactionId, $mainItem->id);
                }
            }

            return $mainItem;
        });
    }
}
