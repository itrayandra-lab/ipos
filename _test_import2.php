<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = \App\Models\Product::first();
echo "Product: {$product->name} (ID: {$product->id})\n";

$user = \App\Models\User::find(7);
echo "User: {$user->name} (ID: {$user->id})\n";

$rows = new \Illuminate\Support\Collection([
    new \Illuminate\Support\Collection([
        'transaction_code' => 'TEST003',
        'transaction_date' => '2026-05-21',
        'customer_name' => 'Test Customer',
        'customer_phone' => '08123456789',
        'source' => 'offline',
        'payment_status' => 'paid',
        'payment_method' => 'cash',
        'notes' => 'test import',
        'product_name' => $product->name,
        'qty' => '2',
        'price' => '50000',
        'discount' => '0',
    ]),
]);

echo "Starting import...\n";

try {
    $import = new \App\Imports\TransactionImport($user->id);
    $import->collection($rows);
    echo "Imported: {$import->getImportedCount()}, Skipped: {$import->getSkippedCount()}\n";

    $tx = \App\Models\Transaction::where('transaction_code', 'TEST003')->first();
    if ($tx) {
        echo "Transaction ID: {$tx->id}, Code: {$tx->transaction_code}\n";
        echo "Items count: " . $tx->items->count() . "\n";
        foreach ($tx->items as $i) {
            echo "  Item: product_id={$i->product_id}, qty={$i->qty}, price={$i->price}\n";
        }
        $tx->items()->delete();
        $tx->delete();
        echo "Cleaned up.\n";
    } else {
        echo "Transaction NOT found!\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "In: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
