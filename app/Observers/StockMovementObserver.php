<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockMovement;

class StockMovementObserver
{
    /**
     * Perubahan status (pending→transit→completed) mengubah K,
     * tapi tidak menyentuh items — sync eksplisit di sini.
     */
    public function updated(StockMovement $movement): void
    {
        if ($movement->wasChanged('status')) {
            $ids = $movement->items()->pluck('product_id')->unique()->filter()->values()->all();
            Product::bulkSyncStock($ids);
        }
    }

    public function deleted(StockMovement $movement): void
    {
        $ids = $movement->items()->pluck('product_id')->unique()->filter()->values()->all();
        Product::bulkSyncStock($ids);
    }
}
