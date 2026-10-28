<?php
require __DIR__ . '/../vendor/autoload.php';
$f = __DIR__ . '/../public/Order.all.20260810_20260909.xlsx';
try {
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($f);
    $ss = $reader->load($f);
    echo 'SHEETS: ' . implode(', ', $ss->getSheetNames()) . PHP_EOL;
    foreach ($ss->getAllSheets() as $s) {
        echo '--- sheet: ' . $s->getTitle() . ' dims=' . $s->getHighestRow() . 'x' . $s->getHighestColumn() . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . PHP_EOL;
}
