<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Product;

try {
    $search = '';
    $products = Product::with(['merek', 'variants.netto'])
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })
            ->limit(5)
            ->get();
    
    echo "Found " . $products->count() . " products\n";
    foreach($products as $p) {
        echo " - " . $p->name . " (Variants: " . $p->variants->count() . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
