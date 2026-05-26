<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// State BEFORE fix
echo "=== SEBELUM PERBAIKAN ===\n";
$batch = DB::table('product_batches')->where('id', 164)->first();
$product = DB::table('products')->where('id', 130)->first();
echo "Batch 0526 022 qty: {$batch->qty}\n";
echo "Product stock: {$product->stock}\n";

// The batch was created with initial qty=100 (from Goods Receipt)
// A sale of 9 units was made, but stock was NOT decremented (bug)
// Then that sale was deleted, and stock was incremented back by 9 (but it never was decremented!)
// So now batch has 100 + 9 = 109 (wrong), should be 100
// Product stock also has 163 instead of 154 (163 - 9 = 154)

echo "\n=== MELAKUKAN PERBAIKAN ===\n";
DB::beginTransaction();
try {
    // Fix batch 0526 022: 109 → 100
    DB::table('product_batches')->where('id', 164)->update([
        'qty' => 100,
        'updated_at' => now()
    ]);
    echo "Batch 0526 022: qty diperbaiki 109 → 100\n";

    // Fix product stock: 163 → 154 (subtract the phantom 9 units)
    DB::table('products')->where('id', 130)->update([
        'stock' => 154,
        'updated_at' => now()
    ]);
    echo "Product stock: diperbaiki 163 → 154\n";

    DB::commit();
    echo "Berhasil!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "GAGAL: " . $e->getMessage() . "\n";
}

// State AFTER fix
echo "\n=== SETELAH PERBAIKAN ===\n";
$batch = DB::table('product_batches')->where('id', 164)->first();
$product = DB::table('products')->where('id', 130)->first();
echo "Batch 0526 022 qty: {$batch->qty}\n";
echo "Product stock: {$product->stock}\n";

// Verify total batch qty = product stock
echo "\n=== VERIFIKASI: Total Semua Batch ===\n";
$allBatches = DB::table('product_batches')->where('product_id', 130)->get();
$totalBatchQty = 0;
foreach ($allBatches as $b) {
    echo "  Batch {$b->batch_no}: qty={$b->qty}\n";
    $totalBatchQty += $b->qty;
}
echo "Total batch qty: {$totalBatchQty}\n";
echo "Product stock: {$product->stock}\n";
if ($totalBatchQty == $product->stock) {
    echo "✓ Stock konsisten!\n";
} else {
    echo "⚠ Stock tidak konsisten! Selisih: " . ($totalBatchQty - $product->stock) . "\n";
}
