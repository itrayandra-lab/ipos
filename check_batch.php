<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find batch with batch_no = '0526 022'
$batch = DB::table('product_batches')->where('batch_no', '0526 022')->first();
echo "=== BATCH 0526 022 ===\n";
echo json_encode($batch, JSON_PRETTY_PRINT) . "\n";

if ($batch) {
    // Get the variant info
    $variant = DB::table('product_variants')->find($batch->product_variant_id);
    echo "\n=== PRODUCT VARIANT ===\n";
    echo json_encode($variant, JSON_PRETTY_PRINT) . "\n";

    // Get the product info
    if ($variant) {
        $product = DB::table('products')->find($variant->product_id ?? null);
        // product_variants -> product_nettos -> products
        $netto = DB::table('product_nettos')->find($variant->product_netto_id);
        if ($netto) {
            $product = DB::table('products')->find($netto->product_id);
        }
        echo "\n=== PRODUCT ===\n";
        echo json_encode($product, JSON_PRETTY_PRINT) . "\n";
    }

    // Get all transaction_items linked to this batch
    echo "\n=== TRANSACTION ITEMS using batch_id={$batch->id} ===\n";
    $txItems = DB::table('transaction_items')
        ->where('product_batch_id', $batch->id)
        ->get();
    echo json_encode($txItems, JSON_PRETTY_PRINT) . "\n";
    echo "Total sales qty from this batch: " . $txItems->sum('qty') . "\n";

    // Check deleted_at on transactions linked
    echo "\n=== TRANSACTIONS linked to these items ===\n";
    $txIds = $txItems->pluck('transaction_id')->unique()->toArray();
    if ($txIds) {
        $txs = DB::table('transactions')->whereIn('id', $txIds)->get();
        echo json_encode($txs, JSON_PRETTY_PRINT) . "\n";
    }
}

// Also search by variant name for DERMOND Intimen
echo "\n=== ALL BATCHES for DERMOND Intimen ===\n";
$batches = DB::table('product_batches as pb')
    ->join('product_variants as pv', 'pb.product_variant_id', '=', 'pv.id')
    ->join('product_nettos as pn', 'pv.product_netto_id', '=', 'pn.id')
    ->join('products as p', 'pn.product_id', '=', 'p.id')
    ->where('p.name', 'like', '%Intimen%')
    ->select('pb.*', 'p.name as product_name', 'pv.variant_name', 'p.stock as product_stock')
    ->get();
echo json_encode($batches, JSON_PRETTY_PRINT) . "\n";
