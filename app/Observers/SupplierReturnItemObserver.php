<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\SupplierReturnItem;

class SupplierReturnItemObserver
{
    public function created(SupplierReturnItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }

    public function updated(SupplierReturnItem $item): void
    {
        Product::bulkSyncStock(array_unique(array_filter([
            $item->product_id, $item->getOriginal('product_id'),
        ])));
    }

    public function deleted(SupplierReturnItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }

    public function restored(SupplierReturnItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }

    public function forceDeleted(SupplierReturnItem $item): void
    {
        Product::bulkSyncStock([$item->product_id]);
    }
}
