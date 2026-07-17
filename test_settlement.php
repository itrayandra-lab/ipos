<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Illuminate\Http\Request;

$req = new Request([
    'start_date' => '2026-07-01',
    'end_date' => '2026-07-14'
]);

$controller = app()->make(App\Http\Controllers\Admin\Finance\SettlementController::class);
$ref = new ReflectionMethod($controller, 'getFilteredQuery');
$ref->setAccessible(true);

try {
    $query = $ref->invoke($controller, $req);
    echo 'SQL: ' . $query->toSql() . PHP_EOL;
    echo 'Bindings: ' . json_encode($query->getBindings()) . PHP_EOL;
    $results = $query->get();
    echo 'Count: ' . $results->count() . PHP_EOL;
    if ($results->count() > 0) {
        echo 'First: ' . json_encode($results->first()) . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
