<?php

namespace App\Services;

use App\Models\ProductBatch;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Pembanding hasil SO (file Excel) dengan stok sistem. READ-ONLY.
 * Kunci pencocokan: merek + nama produk + no batch (dinormalisasi).
 * Stok sistem = jumlah current_stock semua gudang untuk kunci yang sama.
 */
class StockOpnameCheckService
{
    public static function norm(?string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower((string) $s));
    }

    public static function normBatch(?string $s): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $s));
    }

    public function check(string $path): array
    {
        $so = $this->readSo($path);
        $system = $this->systemStock();

        $matched = [];
        $soUnmatched = [];
        $usedKeys = [];

        foreach ($so as $row) {
            $key = $row['key'];
            if ($row['batch_norm'] !== '' && isset($system[$key])) {
                $sys = $system[$key];
                $usedKeys[$key] = true;
                $matched[] = $this->makeRow($sys, $row['qty'], $row);
                continue;
            }

            // SO tanpa batch: cocokkan per produk (jumlah semua batch sistem)
            if ($row['batch_norm'] === '') {
                $group = array_filter($system, fn($s) => $s['prod_key'] === $row['prod_key']);
                if ($group) {
                    $first = reset($group);
                    foreach (array_keys($group) as $k) {
                        $usedKeys[$k] = true;
                    }
                    $sys = $first;
                    $sys['batch_no'] = '(semua batch)';
                    $sys['qty'] = array_sum(array_column($group, 'qty'));
                    $sys['warehouses'] = [];
                    $matched[] = $this->makeRow($sys, $row['qty'], $row, 'SO tanpa batch');
                    continue;
                }
            }

            $soUnmatched[] = $row + ['candidates' => $this->candidates($row, $system)];
        }

        $notInSo = [];
        foreach ($system as $key => $sys) {
            if (!isset($usedKeys[$key]) && $sys['qty'] != 0) {
                $sys['hpp_value'] = $sys['qty'] * ($sys['buy_price'] ?? 0);
                $notInSo[] = $sys;
            }
        }

        usort($matched, fn($a, $b) => strcmp($a['name'] . $a['batch_no'], $b['name'] . $b['batch_no']));
        usort($notInSo, fn($a, $b) => strcmp($a['name'] . $a['batch_no'], $b['name'] . $b['batch_no']));

        $summary = [
            'so_rows' => count($so),
            'matched' => count($matched),
            'with_diff' => count(array_filter($matched, fn($r) => $r['diff'] != 0)),
            'total_system_matched' => array_sum(array_column($matched, 'system_qty')),
            'total_so_matched' => array_sum(array_column($matched, 'so_qty')),
            'not_in_so' => count($notInSo),
            'not_in_so_qty' => array_sum(array_column($notInSo, 'qty')),
            'so_unmatched' => count($soUnmatched),
            'so_unmatched_qty' => array_sum(array_column($soUnmatched, 'qty')),
        ];

        return compact('matched', 'notInSo', 'soUnmatched', 'summary');
    }

    /**
     * Baca SO per gudang. $map = [nama kolom (huruf kecil) => warehouse_id], mis. ['kbp' => 1].
     * Return: [key (merek|produk|batch) => ['name','brand','batch','line','wh' => [warehouse_id => qty]]]
     */
    public function soByWarehouse(string $path, array $map): array
    {
        $sheet = $this->readSheet($path);
        if (!$sheet) {
            return [];
        }
        $header = array_map(fn($h) => strtolower(trim((string) $h)), $sheet[0]);
        $col = fn($n) => array_search($n, $header, true);
        $cBrand = $col('brand');
        $cName = $col('nama barang');
        $cBatch = $col('no batch');

        $out = [];
        foreach (array_slice($sheet, 1) as $i => $r) {
            $name = trim((string) ($r[$cName] ?? ''));
            if ($name === '') {
                continue;
            }
            $brand = trim((string) ($r[$cBrand] ?? ''));
            $batch = trim((string) ($r[$cBatch] ?? ''));
            $key = self::norm($brand) . '|' . self::norm($name) . '|' . self::normBatch($batch);
            $out[$key] ??= ['name' => $name, 'brand' => $brand, 'batch' => $batch, 'line' => $i + 2, 'wh' => []];
            foreach ($map as $colName => $whId) {
                $c = $col($colName);
                if ($c === false) {
                    continue;
                }
                $out[$key]['wh'][$whId] = ($out[$key]['wh'][$whId] ?? 0) + (int) ($r[$c] ?? 0);
            }
        }

        return $out;
    }

    /** Kunci pencocokan batch sistem (sama dengan kunci SO). */
    public static function batchKey($batch): string
    {
        return self::norm($batch->product->merek->name ?? '') . '|' . self::norm($batch->product->name ?? '') . '|' . self::normBatch($batch->batch_no);
    }

    private function makeRow(array $sys, int $soQty, array $row, ?string $note = null): array
    {
        return [
            'name' => $sys['name'],
            'batch_no' => $sys['batch_no'],
            'expiry' => $sys['expiry'],
            'system_qty' => $sys['qty'],
            'so_qty' => $soQty,
            'diff' => $soQty - $sys['qty'],
            'note' => $note,
            'so_name' => $row['name'],
        ];
    }

    /**
     * Baca sheet pertama sebagai array. Sel formula (mis. hasil export Google Sheets
     * IFERROR(__xludf...)) memakai nilai hasil hitung yang tersimpan di file.
     */
    private function readSheet(string $path): array
    {
        $ws = IOFactory::load($path)->getSheet(0);
        $rows = [];
        foreach ($ws->getRowIterator() as $row) {
            $cells = [];
            $it = $row->getCellIterator();
            $it->setIterateOnlyExistingCells(false);
            foreach ($it as $cell) {
                $v = $cell->getValue();
                if (is_string($v) && str_starts_with($v, '=')) {
                    $v = $cell->getOldCalculatedValue();
                }
                $cells[] = $v;
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    private function readSo(string $path): array
    {
        $sheet = $this->readSheet($path);
        if (!$sheet) {
            return [];
        }

        $header = array_map(fn($h) => strtolower(trim((string) $h)), $sheet[0]);
        $col = fn($name) => array_search($name, $header, true);

        $cBrand = $col('brand');
        $cName = $col('nama barang');
        $cNetto = $col('netto');
        $cBatch = $col('no batch');
        $cExp = $col('exp');
        $cTotal = $col('total stok');
        $whCols = array_filter([$col('kbp'), $col('nanjung'), $col('apotik'), $col('bintaro')], fn($v) => $v !== false);

        $out = [];
        foreach (array_slice($sheet, 1) as $i => $r) {
            $name = trim((string) ($r[$cName] ?? ''));
            if ($name === '') {
                continue; // baris judul brand / kosong
            }
            $qty = 0;
            foreach ($whCols as $c) {
                $qty += (int) ($r[$c] ?? 0);
            }
            if ($cTotal !== false && is_numeric($r[$cTotal] ?? null)) {
                $qty = (int) $r[$cTotal];
            }

            $brand = trim((string) ($r[$cBrand] ?? ''));
            $batch = trim((string) ($r[$cBatch] ?? ''));
            $prodKey = self::norm($brand) . '|' . self::norm($name);
            $batchNorm = self::normBatch($batch);

            $row = [
                'line' => $i + 2,
                'brand' => $brand,
                'name' => $name,
                'netto' => trim((string) ($r[$cNetto] ?? '')),
                'batch' => $batch,
                'batch_norm' => $batchNorm,
                'exp' => trim((string) ($r[$cExp] ?? '')),
                'qty' => $qty,
                'prod_key' => $prodKey,
                'key' => $prodKey . '|' . $batchNorm,
            ];

            // gabungkan jika kunci kembar di file SO
            if (isset($out[$row['key']])) {
                $out[$row['key']]['qty'] += $qty;
            } else {
                $out[$row['key']] = $row;
            }
        }

        return array_values($out);
    }

    private function systemStock(): array
    {
        $batches = ProductBatch::withSisa()
            ->with(['product.merek', 'variant.netto', 'warehouse'])
            ->get();

        $system = [];
        foreach ($batches as $b) {
            if (!$b->product) {
                continue;
            }
            $merek = $b->product->merek->name ?? '';
            $prodKey = self::norm($merek) . '|' . self::norm($b->product->name);
            $key = $prodKey . '|' . self::normBatch($b->batch_no);
            $stock = $b->current_stock;

            if (!isset($system[$key])) {
                $netto = $b->variant?->netto;
                $system[$key] = [
                    'name' => trim($merek . ' ' . $b->product->name . ' ' . ($netto ? $netto->netto_value . ' ' . $netto->satuan : '')),
                    'product_name' => $b->product->name,
                    'merek' => $merek,
                    'prod_key' => $prodKey,
                    'batch_no' => $b->batch_no,
                    'expiry' => optional($b->expiry_date)->format('m-y'),
                    'buy_price' => $b->buy_price,
                    'qty' => 0,
                    'warehouses' => [],
                ];
            } elseif ($b->buy_price && !$system[$key]['buy_price']) {
                $system[$key]['buy_price'] = $b->buy_price;
            }

            $system[$key]['qty'] += $stock;
            if ($stock != 0) {
                $wh = $b->warehouse->name ?? '-';
                $system[$key]['warehouses'][$wh] = ($system[$key]['warehouses'][$wh] ?? 0) + $stock;
            }
        }

        return $system;
    }

    /** Kandidat produk sistem yang namanya mirip (merek sama dulu). */
    private function candidates(array $row, array $system): array
    {
        $needle = self::norm($row['name']);
        $brand = self::norm($row['brand']);
        $seen = [];
        $scored = [];

        foreach ($system as $sys) {
            if (isset($seen[$sys['prod_key']])) {
                continue;
            }
            $sysName = self::norm($sys['product_name']);
            $score = 0;
            if ($needle !== '' && (str_contains($sysName, $needle) || str_contains($needle, $sysName))) {
                $score = 100;
            } else {
                similar_text($needle, $sysName, $pct);
                $score = $pct;
            }
            if (self::norm($sys['merek']) === $brand) {
                $score += 10;
            }
            if ($score >= 60) {
                $seen[$sys['prod_key']] = true;
                $scored[] = [$score, $sys['merek'] . ' ' . $sys['product_name']];
            }
        }

        usort($scored, fn($a, $b) => $b[0] <=> $a[0]);

        return array_column(array_slice($scored, 0, 3), 1);
    }
}
