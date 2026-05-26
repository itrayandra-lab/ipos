<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check the GR that created batch 0526 022
// The batch was created on 2026-05-25 15:36:44
echo "=== MENCARI GR yang membuat batch 0526 022 ===\n";

// Find GR items with batch_no = '0526 022'
$grItems = DB::table('goods_receipt_items')
    ->where('batch_no', '0526 022')
    ->get();
echo "GR Items dengan batch_no '0526 022':\n";
echo json_encode($grItems, JSON_PRETTY_PRINT) . "\n";

// Check all recent GR items
echo "\n=== 5 GR items terbaru ===\n";
$recentItems = DB::table('goods_receipt_items')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();
echo json_encode($recentItems, JSON_PRETTY_PRINT) . "\n";

// Check if GR creates the batch or if batch is separate
echo "\n=== Apakah ada product_batch yang berkaitan dengan GR item? ===\n";
// Check if product_batches has a goods_receipt_id column
$columns = DB::select("SHOW COLUMNS FROM product_batches");
echo "Kolom product_batches:\n";
foreach ($columns as $col) {
    echo "  - {$col->Field} ({$col->Type})\n";
}

// Check goods_receipt_items columns
echo "\nKolom goods_receipt_items:\n";
$columns2 = DB::select("SHOW COLUMNS FROM goods_receipt_items");
foreach ($columns2 as $col) {
    echo "  - {$col->Field} ({$col->Type})\n";
}
