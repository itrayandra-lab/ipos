<?php

namespace App\Observers;

use App\Models\TransactionItem;

class TransactionItemObserver
{
    /**
     * Handle the TransactionItem "created" event.
     */
    public function created(TransactionItem $transactionItem): void
    {
        $transactionItem->product->syncStock();
    }

    public function updated(TransactionItem $transactionItem): void
    {
        $transactionItem->product->syncStock();
    }

    public function deleted(TransactionItem $transactionItem): void
    {
        $transactionItem->product->syncStock();
    }

    public function restored(TransactionItem $transactionItem): void
    {
        $transactionItem->product->syncStock();
    }

    public function forceDeleted(TransactionItem $transactionItem): void
    {
        $transactionItem->product->syncStock();
    }
}
