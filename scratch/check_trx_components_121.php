<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TransactionItem;

$totalSum = TransactionItem::where('transaction_id', 121)->sum('subtotal');
$parentSum = TransactionItem::where('transaction_id', 121)->whereNull('parent_item_id')->sum('subtotal');
$childSum = TransactionItem::where('transaction_id', 121)->whereNotNull('parent_item_id')->sum('subtotal');

echo "Transaction #121 Items Summary:\n";
echo " - Total Sum of Subtotals: " . $totalSum . "\n";
echo " - Parents Sum: " . $parentSum . "\n";
echo " - Children Sum: " . $childSum . "\n";

$children = TransactionItem::where('transaction_id', 121)->whereNotNull('parent_item_id')->get();
foreach ($children as $child) {
    echo "   - Child ID: " . $child->id . " | Product: " . $child->product_id . " | Subtotal: " . $child->subtotal . "\n";
}
