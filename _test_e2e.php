<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Generate the template
$path = storage_path('app/test_import.xlsx');
\Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\TransactionTemplateExport(), 'test_import.xlsx');
echo "Template saved to: $path\n";

// Read it back
$rows = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\TransactionImport(7), $path);
echo "Rows read: " . $rows->count() . "\n";

// Check the first sheet
$firstSheet = $rows->first();
if ($firstSheet) {
    echo "First sheet has " . $firstSheet->count() . " rows\n";
    if ($firstSheet->count() > 0) {
        $firstRow = $firstSheet->first();
        echo "First row columns:\n";
        foreach ($firstRow as $key => $val) {
            echo "  '$key' => '$val'\n";
        }
    }
} else {
    echo "No sheets found!\n";
}

@unlink($path);
