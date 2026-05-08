<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TransactionItem;

$items = TransactionItem::where('transaction_id', 121)->get();
foreach ($items as $item) {
    echo "Item ID: " . $item->id . " | Subtotal: " . $item->subtotal . " | Discount: " . $item->discount . " | Price: " . $item->price . " | Qty: " . $item->qty . "\n";
}
