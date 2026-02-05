<?php

namespace App\Observers;

use App\Models\ProductBatch;

class ProductBatchObserver
{
    /**
     * Handle the ProductBatch "created" event.
     */
    public function created(ProductBatch $productBatch): void
    {
        $productBatch->product->syncStock();
    }

    public function updated(ProductBatch $productBatch): void
    {
        $productBatch->product->syncStock();
    }

    public function deleted(ProductBatch $productBatch): void
    {
        $productBatch->product->syncStock();
    }

    public function restored(ProductBatch $productBatch): void
    {
        $productBatch->product->syncStock();
    }

    public function forceDeleted(ProductBatch $productBatch): void
    {
        $productBatch->product->syncStock();
    }
}
