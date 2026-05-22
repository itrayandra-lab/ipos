<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Generate the template
$path = storage_path('app/test_import.xlsx');
\Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\TransactionTemplateExport(), 'test_import.xlsx');
echo "Template created.\n";

// Read with import
$import = new \App\Imports\TransactionImport(7);
$result = \Maatwebsite\Excel\Facades\Excel::import($import, $path);
echo "Import completed.\n";
echo "Imported: {$import->getImportedCount()}, Skipped: {$import->getSkippedCount()}\n";

// Now test with real product name
$product = \App\Models\Product::inRandomOrder()->first();
echo "\nTesting with product: {$product->name}\n";

// Create a new Excel with product name filled
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('I2', $product->name);
$sheet->setCellValue('J2', 5);
$sheet->setCellValue('K2', 25000);

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
$filledPath = storage_path('app/test_filled.xlsx');
$writer->save($filledPath);

$import2 = new \App\Imports\TransactionImport(7);
\Maatwebsite\Excel\Facades\Excel::import($import2, $filledPath);
echo "Second import: {$import2->getImportedCount()} imported, {$import2->getSkippedCount()} skipped\n";

$lastTx = \App\Models\Transaction::latest()->first();
if ($lastTx) {
    echo "Last transaction: {$lastTx->transaction_code}\n";
    echo "Items: " . $lastTx->items->count() . "\n";
    foreach ($lastTx->items as $i) {
        echo "  - product_id={$i->product_id}, qty={$i->qty}, price={$i->price}\n";
    }
    // Clean up
    $lastTx->items()->delete();
    $lastTx->delete();
    echo "Cleaned up.\n";
}

@unlink($path);
@unlink($filledPath);
echo "\nDone.\n";
