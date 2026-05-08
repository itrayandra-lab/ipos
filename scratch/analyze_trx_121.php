<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$transaction = Transaction::find(121);
if ($transaction) {
    echo "Transaction #121 Details:\n";
    echo " - Total Amount: " . $transaction->total_amount . "\n";
    echo " - Discount: " . $transaction->discount . "\n";
    echo " - Tax Amount: " . $transaction->tax_amount . "\n";
    echo " - Tax Type: " . $transaction->tax_type . "\n";
    echo " - Down Payment: " . $transaction->down_payment . "\n";
    echo " - Is DP: " . $transaction->is_dp . "\n";
    echo " - Subtotal (Items Sum): " . $transaction->items->sum('subtotal') . "\n";
}
