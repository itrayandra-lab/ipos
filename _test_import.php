<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create a test import with known product
$product = \App\Models\Product::first();
if (!$product) {
    echo "No products found in DB!\n";
    exit;
}

echo "Using product: {$product->name} (ID: {$product->id})\n";

// Create test rows mimicking Excel import
$rows = new \Illuminate\Support\Collection([
    new \Illuminate\Support\Collection([
        'transaction_code' => 'TEST001',
        'transaction_date' => date('Y-m-d'),
        'customer_name' => 'Test Customer',
        'customer_phone' => '08123456789',
        'source' => 'offline',
        'payment_status' => 'paid',
        'payment_method' => 'cash',
        'notes' => 'Test import',
        'product_name' => $product->name,
        'qty' => '2',
        'price' => '50000',
        'discount' => '0',
    ]),
]);

echo "Test data prepared.\n";

try {
    $import = new \App\Imports\TransactionImport(1); // user_id=1 (super_admin)
    $import->collection($rows);
    echo "Import completed.\n";
    echo "Imported: {$import->getImportedCount()}, Skipped: {$import->getSkippedCount()}\n";
    
    // Check the last transaction
    $last = \App\Models\Transaction::where('transaction_code', 'TEST001')->first();
    if ($last) {
        echo "Transaction found: {$last->transaction_code}\n";
        echo "Items count: " . $last->items->count() . "\n";
        foreach ($last->items as $item) {
            echo "  - Item ID: {$item->id}, Product ID: {$item->product_id}, Qty: {$item->qty}, Price: {$item->price}\n";
        }
        // Clean up
        $last->items()->delete();
        $last->delete();
        echo "Test data cleaned up.\n";
    } else {
        echo "Transaction NOT found!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
