<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$product = Product::where('name', 'like', '%Baby Born Series%')->first();
if ($product) {
    $product->is_bundle = 1;
    $product->save();
    echo "Product '{$product->name}' is now a bundle (is_bundle = 1).\n";
} else {
    echo "Product not found.\n";
}
