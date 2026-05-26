<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ProductBatch;
use App\Models\Product;

$product = Product::where('name', 'like', '%intimate foam%')->first();
echo "Before sync: " . $product->stock . "\n";
echo "Batches total qty: " . ProductBatch::where('product_id', $product->id)->sum('qty') . "\n";
echo "Tx items total: " . DB::table('transaction_items')->where('product_id', $product->id)->sum('qty') . "\n";

$product->syncStock();
$product->refresh();

echo "After sync: " . $product->stock . "\n";
