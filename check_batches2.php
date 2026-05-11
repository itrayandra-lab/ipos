<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batches = \App\Models\ProductBatch::where('batch_no', 'like', '%0226 020%')->get();
foreach ($batches as $batch) {
    echo "Batch ID: " . $batch->id . " - No: " . $batch->batch_no . " - Product ID: " . $batch->product_id . "\n";
}
