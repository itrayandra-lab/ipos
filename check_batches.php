<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batches = \App\Models\ProductBatch::where('product_id', 100)->get();
foreach ($batches as $batch) {
    echo "Batch ID: " . $batch->id . " - No: " . $batch->batch_no . "\n";
}
