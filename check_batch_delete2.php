<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batch = \App\Models\ProductBatch::where('batch_no', '0226 020')->first();
if (!$batch) {
    echo "Batch not found!\n";
} else {
    echo "Batch ID: " . $batch->id . "\n";
    echo "Transactions: " . $batch->transactionItems()->count() . "\n";
    echo "Supplier Returns: " . (method_exists($batch, 'supplierReturnItems') ? $batch->supplierReturnItems()->count() : 'N/A') . "\n";
    
    // Simulate delete but catch any QueryException specifically to see the exact constraint violation
    try {
        $batch->delete();
        echo "DELETE SUCCESSFUL IN TINKER!\n";
    } catch (\Illuminate\Database\QueryException $e) {
        echo "QUERY EXCEPTION: " . $e->getMessage() . "\n";
    } catch (\Exception $e) {
        echo "DELETE FAILED: " . $e->getMessage() . "\n";
    }
}
