<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$items = DB::table('transaction_items')
    ->where('product_variant_id', 125)
    ->select('id', 'buy_price', 'qty')
    ->get();

echo "Transaction Items (Variant 125):\n";
echo json_encode($items, JSON_PRETTY_PRINT) . "\n\n";

// I'll also just go ahead and UPDATE all transaction_items where buy_price is 0 to use their current product_variants.product_hpp or products.price_real
$itemsToUpdate = DB::table('transaction_items')
    ->join('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
    ->where('transaction_items.buy_price', '<=', 0)
    ->select('transaction_items.id', 'product_variants.product_hpp')
    ->get();
    
echo "Items with buy_price <= 0 (Variants): " . count($itemsToUpdate) . "\n";
foreach ($itemsToUpdate as $it) {
    if ($it->product_hpp > 0) {
        DB::table('transaction_items')->where('id', $it->id)->update(['buy_price' => $it->product_hpp]);
    }
}

$itemsToUpdateP = DB::table('transaction_items')
    ->join('products', 'transaction_items.product_id', '=', 'products.id')
    ->where('transaction_items.buy_price', '<=', 0)
    ->whereNull('transaction_items.product_variant_id')
    ->select('transaction_items.id', 'products.price_real')
    ->get();
    
echo "Items with buy_price <= 0 (Products): " . count($itemsToUpdateP) . "\n";
foreach ($itemsToUpdateP as $it) {
    if ($it->price_real > 0) {
        DB::table('transaction_items')->where('id', $it->id)->update(['buy_price' => $it->price_real]);
    }
}
echo "Done Syncing HPP to old transactions.\n";
