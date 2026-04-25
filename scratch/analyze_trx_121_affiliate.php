<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$transaction = Transaction::find(121);
if ($transaction) {
    echo "Transaction #121 Affiliate Info:\n";
    echo " - Affiliate ID: " . $transaction->affiliate_id . "\n";
    echo " - Affiliate Fee Total: " . $transaction->affiliate_fee_total . "\n";
    echo " - Affiliate Fee Mode: " . $transaction->affiliate_fee_mode . "\n";
}
