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
    
    // Test if we can find any other relations?
    // GoodReceiptItem ?
    if (class_exists('App\Models\GoodsReceiptItem')) {
        $griCount = \App\Models\GoodsReceiptItem::where('product_batch_id', $batch->id)->count();
        echo "GoodsReceiptItems: " . $griCount . "\n";
    }

    if (class_exists('App\Models\StockMovementItem')) {
        $smiCount = \App\Models\StockMovementItem::where('product_batch_id', $batch->id)->count();
        echo "StockMovementItems: " . $smiCount . "\n";
    }

    if (class_exists('App\Models\PurchaseOrderItem')) {
        $poiCount = \App\Models\PurchaseOrderItem::where('product_batch_id', $batch->id)->count();
        echo "PurchaseOrderItems: " . $poiCount . "\n";
    }
}
