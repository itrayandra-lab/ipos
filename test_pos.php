<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$p = \App\Models\Product::where('name', 'like', '%Facial Wash Brightening%')->first();
if (!$p) {
    echo "Product not found.\n";
} else {
    $variants = \App\Models\ProductVariant::whereIn('product_netto_id', function($q) use ($p) {
        $q->select('id')->from('product_nettos')->where('product_id', $p->id);
    })->get(['id', 'price'])->toArray();

    echo json_encode([
        'id' => $p->id, 
        'name' => $p->name, 
        'status' => $p->status, 
        'price' => $p->price, 
        'price_real' => $p->price_real, 
        'qty' => \App\Models\ProductBatch::where('product_id', $p->id)->sum('qty'), 
        'variants' => $variants
    ]);
}
