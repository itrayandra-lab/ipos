<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Models\ProductBatch::observe(\App\Observers\ProductBatchObserver::class);
        \App\Models\TransactionItem::observe(\App\Observers\TransactionItemObserver::class);
        \App\Models\StockMovementItem::observe(\App\Observers\StockMovementItemObserver::class);
        \App\Models\StockMovement::observe(\App\Observers\StockMovementObserver::class);
        \App\Models\SupplierReturnItem::observe(\App\Observers\SupplierReturnItemObserver::class);
    }
}
