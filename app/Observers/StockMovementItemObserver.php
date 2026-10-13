<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockMovementItem;

class StockMovementItemObserver
{
    public function created(StockMovementItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }

    public function updated(StockMovementItem $item): void
    {
        Product::bulkSyncStock(array_unique(array_filter([
            $item->product_id, $item->getOriginal('product_id'),
        ])));
    }

    public function deleted(StockMovementItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }

    public function restored(StockMovementItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }

    public function forceDeleted(StockMovementItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }
}
