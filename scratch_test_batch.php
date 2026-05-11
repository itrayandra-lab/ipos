<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pb = \App\Models\ProductBatch::create(['product_id' => 1, 'warehouse_id' => 1, 'batch_no' => 'TEST_BATCH_123', 'qty' => 10, 'buy_price' => 0, 'expiry_date' => '2026-12-31']);
echo "Created ID: " . $pb->id . "\n";
$pb->delete();
echo "Deleted!\n";
