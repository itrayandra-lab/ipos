<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TransactionItem;

$items = TransactionItem::where('transaction_id', 121)->get();
foreach ($items as $item) {
    echo "Item ID: " . $item->id . " | Product: " . $item->product_id . " | Qty: " . $item->qty . " | Price: " . $item->price . " | Subtotal: " . $item->subtotal . " | Parent: " . $item->parent_item_id . "\n";
}
