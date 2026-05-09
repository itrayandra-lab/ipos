<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$c = app()->make(\App\Http\Controllers\Admin\POSController::class);
$req = new \Illuminate\Http\Request();
$req->merge(['warehouse_id' => 1]);
$res = $c->fetchProducts($req);
$content = $res->getContent();
echo "Count: " . count(json_decode($content, true)) . "\n";
