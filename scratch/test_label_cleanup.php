<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\ProductVariant;

try {
    $search = 'Good Mood';
    $query = ProductVariant::with(['netto.product.merek', 'netto.product.batches'])
            ->whereHas('netto.product', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
    
    $results = $query->limit(5)->get();
    echo "Found " . $results->count() . " variants\n";
    foreach($results as $v) {
        $p = $v->netto?->product;
        $merekName   = ($p && $p->merek) ? trim($p->merek->name) : '';
        $productName = trim($p->name ?? '');
        $variantName = trim($v->variant_name ?? '');
        
        $netto = $v->netto;
        $nettoValue = $netto ? trim($netto->netto_value ?? '') : '';
        $satuan = $netto ? trim($netto->satuan ?? '') : '';
        $nettoFull = trim($nettoValue . ' ' . $satuan);

        $parts = [];
        if ($merekName) $parts[] = $merekName;

        if ($variantName && $variantName !== 'Default') {
            if (stripos($variantName, $productName) !== false) {
                $parts[] = $variantName;
            } else {
                $parts[] = $productName;
                $parts[] = $variantName;
            }
        } else {
            $parts[] = $productName;
        }

        $currentText = implode(' ', $parts);
        if ($nettoFull) {
            $cleanCurrent = strtolower(str_replace(' ', '', $currentText));
            $cleanNetto = strtolower(str_replace(' ', '', $nettoFull));
            if (strpos($cleanCurrent, $cleanNetto) === false) {
                $parts[] = $nettoFull;
            }
        }
        $fullName = implode(' ', array_filter($parts));
        $fullName = preg_replace('/\s+/', ' ', $fullName);

        echo " - Original Product: " . $productName . "\n";
        echo " - Original Variant: " . $variantName . "\n";
        echo " - Original Netto: " . $nettoFull . "\n";
        echo " - Formatted: " . $fullName . "\n\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
