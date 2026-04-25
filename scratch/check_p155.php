<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$p155 = Product::find(155);
echo "Product 155: " . ($p155 ? $p155->name : 'Not found') . " | Is Bundle: " . ($p155 ? $p155->is_bundle : 'N/A') . "\n";
