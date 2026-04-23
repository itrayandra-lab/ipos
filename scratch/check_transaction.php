<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;

$t = Transaction::latest()->with('items.product')->first();
if ($t) {
    echo "Transaction ID: {$t->id} | Invoice: {$t->invoice_number}\n";
    foreach ($t->items as $item) {
        echo "- [ID: {$item->id}] {$item->product->name} (Parent: " . ($item->parent_item_id ?? 'NULL') . ", Price: {$item->price}, Qty: {$item->qty})\n";
    }
} else {
    echo "No transactions found.\n";
}
