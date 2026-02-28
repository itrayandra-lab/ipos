<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\TransactionPayment;

$id = 73;
$transaction = Transaction::find($id);

if (!$transaction) {
    echo "Transaction $id not found\n";
    exit;
}

echo "Transaction ID: $id\n";
echo "Payment Method: '" . ($transaction->payment_method ?? 'NULL') . "'\n";
echo "Payment Status: " . $transaction->payment_status . "\n";
echo "Total Amount: " . $transaction->total_amount . "\n";
echo "Down Payment: " . $transaction->down_payment . "\n";

// Check if transaction_payments table exists and its structure
try {
    $columns = DB::getSchemaBuilder()->getColumnListing('transaction_payments');
    echo "Columns in transaction_payments: " . implode(', ', $columns) . "\n";
}
catch (\Exception $e) {
    echo "Error checking table: " . $e->getMessage() . "\n";
}
