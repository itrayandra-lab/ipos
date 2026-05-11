<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$count = \App\Models\Product::where('is_bundle', 1)->count();
echo "Bundle Count: " . $count . "\n";

$bundle = \App\Models\Product::where('is_bundle', 1)->first();
if ($bundle) {
    echo "First Bundle: " . $bundle->name . " (Price: " . $bundle->price . ")\n";
    $price = $bundle->getSellingPrice();
    echo "Calculated Dynamic Price: " . $price . "\n";
}
