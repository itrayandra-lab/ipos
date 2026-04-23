<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Warehouse;

$warehouseId = Warehouse::where('type', 'main')->first()?->id ?? 1;
$search = 'baby born';

echo "Searching for '{$search}' in Warehouse ID: {$warehouseId}\n\n";

$bundleQuery = Product::where('is_bundle', true)
    ->where('status', 'Y')
    ->with(['merek', 'photos', 'bundleItems.product.batches', 'variants']);

if ($search) {
    $bundleQuery->where(function($q) use ($search) {
        $q->where('name', 'like', '%' . $search . '%')
          ->orWhereHas('merek', fn($mq) => $mq->where('name', 'like', '%' . $search . '%'));
    });
}

$bundles = $bundleQuery->get();

foreach ($bundles as $bundle) {
    echo "Found Bundle: [{$bundle->id}] {$bundle->name}\n";
    echo "  - Status: {$bundle->status}\n";
    echo "  - Price Product: {$bundle->price}\n";
    echo "  - Price First Variant: " . ($bundle->variants->first()?->price ?? 'None') . "\n";
    
    $minStock = -1;
    echo "  - Components:\n";
    if ($bundle->bundleItems->isEmpty()) {
        echo "    * NO COMPONENTS FOUND!\n";
    }
    foreach ($bundle->bundleItems as $bi) {
        $componentStock = $bi->product->batches->where('warehouse_id', $warehouseId)->sum('qty');
        $possibleBundles = floor($componentStock / ($bi->quantity > 0 ? $bi->quantity : 1));
        echo "    * Component: {$bi->product->name} (Need: {$bi->quantity}, Stock in WH: {$componentStock}, Possible Bundles: {$possibleBundles})\n";
        if ($minStock == -1 || $possibleBundles < $minStock) {
            $minStock = $possibleBundles;
        }
    }
    if ($minStock == -1) $minStock = 0;
    
    $offlinePrice = (int)($bundle->price ?: ($bundle->variants->first() ? $bundle->variants->first()->price : 0));
    
    echo "  - Computed Stock: {$minStock}\n";
    echo "  - Offline Price: {$offlinePrice}\n";
    
    if ($minStock > 0 && $offlinePrice > 0) {
        echo "  - RESULT: SHOULD SHOW IN POS\n";
    } else {
        echo "  - RESULT: HIDDEN FROM POS (Stock/Price missing)\n";
    }
}
