<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latest = \App\Models\PettyCashTransaction::latest()->first();
if ($latest) {
    echo "ID: " . $latest->id . "\n";
    echo "Amount: " . $latest->amount . "\n";
    echo "Balance After: " . $latest->balance_after . "\n";
} else {
    echo "No records found.\n";
}
