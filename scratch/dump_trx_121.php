<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$transaction = Transaction::find(121);
if ($transaction) {
    echo json_encode($transaction->toArray(), JSON_PRETTY_PRINT) . "\n";
}
