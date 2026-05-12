<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\ProductVariant;

try {
    $search = '';
    $query = ProductVariant::with(['netto.product.merek', 'netto.product.batches'])
            ->whereHas('netto.product', function($q) use ($search) {
                $q->where('status', 'Y')
                  ->where('is_bundle', 0);
            });
    
    $results = $query->limit(5)->get();
    echo "Found " . $results->count() . " variants\n";
    foreach($results as $v) {
        $p = $v->netto?->product;
        echo " - " . ($p->name ?? 'N/A') . " (" . $v->variant_name . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
