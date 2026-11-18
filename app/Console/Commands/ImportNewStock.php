<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

/**
 * Import stok baru dari Excel (sheet "List SKU" + "Raw Produk").
 *
 * Tahap 1 (master): sheet "List SKU" dicocokkan ke produk lama lewat Merek Lama + Nama Produk Lama +
 *                   Kode Produk Lama + Netto, lalu merek (nama/kode), produk (nama/kode), netto, SKU diperbarui.
 * Tahap 2 (stok):   tiap baris "Raw Produk" menjadi batch di gudang tujuan (default Beautylatory = 1)
 *                   sebesar TOTAL STOK, terhubung ke PO (dibuat baru bila belum ada) dan supplier.
 *
 * Default = simulasi (semua dijalankan di transaksi lalu di-rollback). --execute untuk sungguhan.
 * Bila ada ERROR, --execute ditolak kecuali pakai --skip-invalid (baris bermasalah dilewati).
 */
class ImportNewStock extends Command
{
    protected $signature = 'stock:import-new {file : Path file Excel}
        {--execute : Jalankan sungguhan (tanpa ini hanya simulasi + rollback)}
        {--warehouse=1 : ID gudang tujuan stok}
        {--date= : Tanggal PO (Y-m-d, default hari ini = tanggal import)}
        {--user= : ID user pembuat PO (default super_admin pertama)}
        {--label= : Label proses (untuk catatan PO)}
        {--skip-invalid : Lewati baris yang error, proses sisanya}';

    protected $description = 'Perbarui master produk/SKU dari sheet List SKU, lalu import stok baru + PO dari sheet Raw Produk.';

    private array $errors = [];
    private array $warnings = [];

    private const ROMAN = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12];

    private static function norm($v): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $v));
    }

    private function err(string $where, string $msg): void
    {
        $this->errors[] = [$where, $msg];
    }

    private function warn2(string $where, string $msg): void
    {
        $this->warnings[] = [$where, $msg];
    }

    public function handle(): int
    {
        $file = $this->argument('file');
        if (!is_file($file)) {
            $this->error("File tidak ditemukan: {$file}");
            return 1;
        }
        $execute = (bool) $this->option('execute');
        $whId = (int) $this->option('warehouse');
        $label = $this->option('label') ?: 'import-stok-' . now()->format('Ymd-His');
        $defaultDate = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        if (!DB::table('warehouses')->where('id', $whId)->exists()) {
            $this->error("Gudang {$whId} tidak ada.");
            return 1;
        }

        $book = IOFactory::load($file);
        $skuSheet = $book->getSheetByName('List SKU');
        $rawSheet = $book->getSheetByName('Raw Produk');
        if (!$skuSheet || !$rawSheet) {
            $this->error('Sheet "List SKU" dan "Raw Produk" harus ada.');
            return 1;
        }
        $skuRows = $skuSheet->toArray(null, true, false, false);
        $rawRows = $rawSheet->toArray(null, true, false, false);
        array_shift($skuRows);
        array_shift($rawRows);

        DB::beginTransaction();
        try {
            $this->info("== TAHAP 1: update master (merek, produk, netto, SKU) ==");
            $this->updateMaster($skuRows);

            $this->info("\n== TAHAP 2: import stok ke gudang #{$whId} + PO ==");
            $stats = $this->importStock($rawRows, $whId, $defaultDate, $label);

            $this->report($stats);

            if ($this->errors && $execute && !$this->option('skip-invalid')) {
                DB::rollBack();
                $this->error('Ada ERROR. Dibatalkan (rollback). Perbaiki Excel, atau jalankan dengan --skip-invalid untuk melewati baris bermasalah.');
                return 1;
            }

            if ($execute) {
                DB::commit();
                $this->info('SELESAI: master & stok baru tersimpan.');
            } else {
                DB::rollBack();
                $this->info('SIMULASI selesai. Semua di-rollback, tidak ada data yang berubah. Tambahkan --execute untuk menjalankan sungguhan.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error, di-rollback: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')');
            return 1;
        }

        return 0;
    }

    // ------------------------------------------------------------------ TAHAP 1

    private function updateMaster(array $skuRows): void
    {
        $skuRows = array_values(array_filter($skuRows, fn($r) => trim((string) ($r[0] ?? '')) !== ''));

        $db = DB::table('product_variants as v')
            ->join('product_nettos as n', 'n.id', '=', 'v.product_netto_id')
            ->join('products as p', 'p.id', '=', 'n.product_id')
            ->join('merek as m', 'm.id', '=', 'p.merek_id')
            ->get(['v.id as vid', 'v.sku_code', 'n.id as nid', 'n.netto_value', 'n.satuan', 'p.id as pid', 'p.name as pname', 'p.code as pcode', 'm.id as mid', 'm.name as mname', 'm.code as mcode']);

        $idx = [];
        foreach ($db as $r) {
            $idx[self::norm($r->mname) . '|' . self::norm($r->pname) . '|' . self::norm($r->pcode)][] = $r;
        }

        $matched = [];   // list row index => db row
        $usedVid = [];
        foreach ($skuRows as $i => $r) {
            $line = $i + 2;
            $sku = trim((string) $r[0]);
            $cands = $idx[self::norm($r[8]) . '|' . self::norm($r[7]) . '|' . self::norm($r[9])] ?? [];
            $cands = array_values(array_filter($cands, fn($x) => self::norm($x->netto_value) === self::norm($r[5])));
            if (count($cands) !== 1) {
                $this->err("List SKU baris {$line} ({$sku})", count($cands) === 0
                    ? "produk lama tidak ditemukan di database ({$r[8]} | {$r[7]} | {$r[9]} | netto {$r[5]})"
                    : 'produk lama cocok ke lebih dari satu varian');
                continue;
            }
            if (isset($usedVid[$cands[0]->vid])) {
                $this->err("List SKU baris {$line} ({$sku})", 'varian lama yang sama sudah dipakai SKU lain: ' . $usedVid[$cands[0]->vid]);
                continue;
            }
            $usedVid[$cands[0]->vid] = $sku;
            $matched[$i] = $cands[0];
        }

        // --- merek: nama & kode baru (nilai terbanyak per merek lama)
        $byMerek = [];
        foreach ($matched as $i => $m) {
            $byMerek[$m->mid]['name'][trim((string) $skuRows[$i][2])] = ($byMerek[$m->mid]['name'][trim((string) $skuRows[$i][2])] ?? 0) + 1;
            $byMerek[$m->mid]['code'][trim((string) $skuRows[$i][1])] = ($byMerek[$m->mid]['code'][trim((string) $skuRows[$i][1])] ?? 0) + 1;
        }
        $merekChanged = 0;
        foreach ($byMerek as $mid => $d) {
            arsort($d['name']);
            arsort($d['code']);
            $newName = array_key_first($d['name']);
            $newCode = array_key_first($d['code']);
            if (count($d['code']) > 1) {
                $this->warn2("Merek #{$mid}", 'kode merek di Excel tidak seragam (' . implode(' / ', array_keys($d['code'])) . "); dipakai: {$newCode}");
            }
            if (count($d['name']) > 1) {
                $this->warn2("Merek #{$mid}", 'nama merek di Excel tidak seragam (' . implode(' / ', array_keys($d['name'])) . "); dipakai: {$newName}");
            }
            $cur = DB::table('merek')->find($mid);
            if ($cur->name === $newName && $cur->code === $newCode) {
                continue;
            }
            if (DB::table('merek')->where('code', $newCode)->where('id', '!=', $mid)->exists()) {
                $this->err("Merek {$cur->name}", "kode baru '{$newCode}' sudah dipakai merek lain");
                continue;
            }
            $slug = Str::slug($newName);
            if (DB::table('merek')->where('slug', $slug)->where('id', '!=', $mid)->exists()) {
                $slug .= '-' . $mid;
            }
            DB::table('merek')->where('id', $mid)->update(['name' => $newName, 'code' => $newCode, 'slug' => $slug, 'updated_at' => now()]);
            $this->line("  Merek: {$cur->name} ({$cur->code}) => {$newName} ({$newCode})");
            $merekChanged++;
        }

        // --- produk: nama & kode (konsisten antar varian)
        $byProduct = [];
        foreach ($matched as $i => $m) {
            $byProduct[$m->pid]['name'][trim((string) $skuRows[$i][4])] = ($byProduct[$m->pid]['name'][trim((string) $skuRows[$i][4])] ?? 0) + 1;
            $byProduct[$m->pid]['code'][trim((string) $skuRows[$i][3])] = ($byProduct[$m->pid]['code'][trim((string) $skuRows[$i][3])] ?? 0) + 1;
        }
        $productChanged = 0;
        foreach ($byProduct as $pid => $d) {
            arsort($d['name']);
            arsort($d['code']);
            $newName = array_key_first($d['name']);
            $newCode = array_key_first($d['code']);
            $distinctNames = collect(array_keys($d['name']))->map(fn($x) => self::norm($x))->unique();
            if ($distinctNames->count() > 1) {
                $this->err("Produk #{$pid}", 'nama baru berbeda antar varian: ' . implode(' / ', array_keys($d['name'])));
                continue;
            }
            if (count($d['code']) > 1) {
                $this->err("Produk #{$pid}", 'kode baru berbeda antar varian: ' . implode(' / ', array_keys($d['code'])));
                continue;
            }
            $cur = DB::table('products')->find($pid);
            if ($cur->name === $newName && (string) $cur->code === (string) $newCode) {
                continue;
            }
            $upd = ['name' => $newName, 'code' => $newCode, 'updated_at' => now()];
            if ($cur->name !== $newName) {
                $slug = Str::slug($newName);
                $n = DB::table('products')->where('slug', $slug)->where('id', '!=', $pid)->count();
                $upd['slug'] = $n ? $slug . '-' . ($n + 1) : $slug;
            }
            DB::table('products')->where('id', $pid)->update($upd);
            $productChanged++;
            if ($cur->name !== $newName) {
                $this->line("  Produk: {$cur->name} => {$newName}");
            }
        }

        // --- netto
        $nettoChanged = 0;
        foreach ($matched as $i => $m) {
            $nv = trim((string) $skuRows[$i][5]);
            $sat = trim((string) $skuRows[$i][6]);
            if ((string) $m->netto_value !== $nv || (string) $m->satuan !== $sat) {
                DB::table('product_nettos')->where('id', $m->nid)->update(['netto_value' => $nv, 'satuan' => $sat, 'updated_at' => now()]);
                $nettoChanged++;
            }
        }

        // --- SKU (dua tahap supaya tidak bentrok dengan unique index)
        $newSku = [];
        foreach ($matched as $i => $m) {
            $newSku[$m->vid] = trim((string) $skuRows[$i][0]);
        }
        $dups = array_filter(array_count_values($newSku), fn($c) => $c > 1);
        foreach (array_keys($dups) as $d) {
            $this->err("SKU {$d}", 'SKU baru dipakai lebih dari satu varian di Excel');
        }
        $skuChanged = 0;
        $toChange = [];
        foreach ($matched as $i => $m) {
            if ($m->sku_code !== $newSku[$m->vid] && !isset($dups[$newSku[$m->vid]])) {
                $toChange[$m->vid] = $newSku[$m->vid];
            }
        }
        foreach ($toChange as $vid => $sku) {
            DB::table('product_variants')->where('id', $vid)->update(['sku_code' => '__TMP__' . $vid]);
        }
        foreach ($toChange as $vid => $sku) {
            if (DB::table('product_variants')->where('sku_code', $sku)->exists()) {
                $this->err("SKU {$sku}", 'sudah dipakai varian lain yang tidak ada di Excel');
                DB::table('product_variants')->where('id', $vid)->update(['sku_code' => $matched ? ($db->firstWhere('vid', $vid)->sku_code ?? '__TMP__' . $vid) : '__TMP__' . $vid]);
                continue;
            }
            DB::table('product_variants')->where('id', $vid)->update(['sku_code' => $sku, 'updated_at' => now()]);
            $skuChanged++;
        }

        $this->table(['Perubahan master', 'Jumlah'], [
            ['Baris List SKU terbaca', count($skuRows)],
            ['Cocok ke produk lama (1:1)', count($matched)],
            ['Merek diubah', $merekChanged],
            ['Produk diubah (nama/kode)', $productChanged],
            ['Netto diubah', $nettoChanged],
            ['SKU diubah', $skuChanged],
        ]);
    }

    // ------------------------------------------------------------------ TAHAP 2

    private function importStock(array $rawRows, int $whId, Carbon $defaultDate, string $label): array
    {
        $rows = [];
        foreach ($rawRows as $i => $r) {
            if (trim((string) ($r[0] ?? '')) === '' && trim((string) ($r[2] ?? '')) === '') {
                continue;
            }
            $rows[] = ['line' => $i + 2, 'r' => $r];
        }

        $userId = $this->option('user') ?: DB::table('users')->where('role', 'super_admin')->value('id');

        // supplier
        $suppliers = DB::table('suppliers')->get(['id', 'name']);
        $supByNorm = [];
        foreach ($suppliers as $s) {
            $supByNorm[self::norm($s->name)] = $s->id;
        }
        $defaultSupplier = $supByNorm[self::norm('PT Lunaray Cahya Abadi')] ?? null;

        // nomor PO dinormalisasi; supplier per PO (terbanyak)
        $poSuppliers = [];
        foreach ($rows as $x) {
            $po = $this->normPo($x['r'][12] ?? '');
            $sn = self::norm($x['r'][14] ?? '');
            if ($po !== '' && $sn !== '') {
                $poSuppliers[$po][$sn] = ($poSuppliers[$po][$sn] ?? 0) + 1;
            }
        }

        $valid = [];
        $seen = [];
        foreach ($rows as $x) {
            $r = $x['r'];
            $line = $x['line'];
            $sku = trim((string) ($r[0] ?? ''));
            $name = trim((string) ($r[2] ?? ''));
            $where = "Raw baris {$line} ({$sku} {$name})";
            $bad = false;

            if ($sku === '') {
                $this->err($where, 'SKU kosong');
                continue;
            }
            $variant = DB::table('product_variants as v')
                ->join('product_nettos as n', 'n.id', '=', 'v.product_netto_id')
                ->join('products as p', 'p.id', '=', 'n.product_id')
                ->leftJoin('merek as m', 'm.id', '=', 'p.merek_id')
                ->where('v.sku_code', $sku)
                ->first(['v.id as vid', 'v.product_hpp', 'p.id as pid', 'p.name as pname', 'p.supplier_id as psup', 'm.name as mname', 'n.netto_value', 'n.satuan']);
            if (!$variant) {
                $this->err($where, 'SKU tidak ditemukan di master (setelah update tahap 1)');
                continue;
            }

            $batchNo = trim((string) ($r[4] ?? ''));
            if ($batchNo === '') {
                $this->err($where, 'no batch kosong');
                $bad = true;
            }

            $exp = null;
            $e = $r[5] ?? null;
            if ($e === null || $e === '') {
                $this->warn2($where, 'tanggal kadaluarsa kosong');
            } elseif (is_numeric($e)) {
                $exp = Carbon::instance(XlsDate::excelToDateTimeObject($e));
            } else {
                try {
                    $exp = Carbon::parse($e);
                } catch (\Throwable $t) {
                    $this->err($where, "tanggal kadaluarsa tidak terbaca: {$e}");
                    $bad = true;
                }
            }
            if ($exp && $exp->lt(now())) {
                $this->warn2($where, 'batch sudah kadaluarsa (' . $exp->format('Y-m-d') . ')');
            }

            $qty = (int) ($r[11] ?? 0);
            $sumWh = (int) ($r[7] ?? 0) + (int) ($r[8] ?? 0) + (int) ($r[9] ?? 0) + (int) ($r[10] ?? 0);
            if ($qty <= 0) {
                $this->warn2($where, "TOTAL STOK {$qty}, baris dilewati");
                continue;
            }
            if ($sumWh !== $qty) {
                $this->warn2($where, "TOTAL STOK {$qty} tidak sama dengan jumlah per gudang {$sumWh}; dipakai TOTAL STOK");
            }

            $hpp = (float) ($r[6] ?? 0);
            $hppFromFile = $hpp > 0;
            if ($hpp <= 0) {
                if ((float) $variant->product_hpp > 0) {
                    $hpp = (float) $variant->product_hpp;
                    $this->warn2($where, "HPP kosong, dipakai HPP master Rp " . number_format($hpp, 0, ',', '.'));
                } else {
                    $this->warn2($where, 'HPP kosong dan HPP master 0, batch bernilai 0');
                    $hpp = 0;
                }
            }

            $po = $this->normPo($r[12] ?? '');
            if ($po === '') {
                $this->err($where, 'nomor PO kosong');
                $bad = true;
            }
            if (trim((string) ($r[12] ?? '')) !== $po && $po !== '') {
                $this->warn2($where, "nomor PO dirapikan: '" . trim((string) $r[12]) . "' => '{$po}'");
            }

            // supplier: kolom -> terbanyak di PO -> supplier produk -> default
            $supplierId = null;
            $sn = self::norm($r[14] ?? '');
            if ($sn !== '') {
                $supplierId = $supByNorm[$sn] ?? null;
                if (!$supplierId) {
                    $this->err($where, "supplier '" . trim((string) $r[14]) . "' tidak ada di database");
                    $bad = true;
                }
            } else {
                if (isset($poSuppliers[$po])) {
                    arsort($poSuppliers[$po]);
                    $supplierId = $supByNorm[array_key_first($poSuppliers[$po])] ?? null;
                    $this->warn2($where, 'supplier kosong, diisi dari baris lain di PO yang sama');
                }
                $supplierId ??= $variant->psup ?: $defaultSupplier;
            }

            $key = $sku . '|' . self::norm($batchNo) . '|' . $po;
            if (isset($seen[$key])) {
                $this->err($where, 'duplikat SKU + no batch + PO dengan baris ' . $seen[$key]);
                $bad = true;
            }
            $seen[$key] = $line;

            if ($bad) {
                continue;
            }
            $valid[] = compact('line', 'variant', 'batchNo', 'exp', 'qty', 'hpp', 'hppFromFile', 'po', 'supplierId');
        }

        // PO beda supplier di Excel
        foreach ($poSuppliers as $po => $s) {
            if (count($s) > 1) {
                $this->warn2("PO {$po}", 'dipakai lebih dari satu supplier di Excel: ' . implode(', ', array_keys($s)));
            }
        }

        // --- buat PO, item, batch
        $poIds = [];
        $itemIds = [];
        $batchIds = [];
        $newPo = 0;
        $totalQty = 0;
        $poGroups = collect($valid)->groupBy('po');
        foreach ($poGroups as $po => $items) {
            if (DB::table('purchase_orders')->where('po_number', $po)->exists()) {
                $this->err("PO {$po}", 'nomor PO sudah ada di database (bukan OLD-). Ubah nomor atau pakai PO tsb manual.');
                continue;
            }
            $supplierId = $items->groupBy('supplierId')->sortByDesc(fn($g) => $g->count())->keys()->first();
            $poDate = $defaultDate->copy(); // tanggal import (hari ini), supaya PO hasil import mudah dikenali

            $poId = DB::table('purchase_orders')->insertGetId([
                'warehouse_id' => $whId,
                'po_number' => $po,
                'supplier_id' => $supplierId,
                'po_date' => $poDate->toDateString(),
                'expected_delivery_date' => $poDate->toDateString(),
                'subtotal' => 0, 'discount_type' => 'percentage', 'discount_value' => 0, 'discount_amount' => 0,
                'tax_percentage' => 0, 'tax_amount' => 0, 'total' => 0,
                'notes' => "Stok awal hasil SO ({$label})",
                'status' => 'received', 'is_opening' => true, 'payment_status' => 'unpaid', 'paid_amount' => 0,
                'created_by' => $userId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $poIds[] = $poId;
            $newPo++;

            $subtotal = 0;
            foreach ($items->groupBy(fn($x) => $x['variant']->vid) as $vid => $g) {
                $v = $g->first()['variant'];
                $qty = $g->sum('qty');
                $value = $g->sum(fn($x) => $x['qty'] * $x['hpp']);
                $unit = $qty ? round($value / $qty, 2) : 0;
                $itemId = DB::table('purchase_order_items')->insertGetId([
                    'purchase_order_id' => $poId,
                    'product_id' => $v->pid,
                    'product_name' => trim(($v->mname ? $v->mname . ' ' : '') . $v->pname . ' ' . $v->netto_value . strtoupper((string) $v->satuan)),
                    'satuan' => $v->satuan,
                    'quantity' => $qty, 'paid_qty' => 0,
                    'unit_price' => $unit, 'total' => $value,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $itemIds[] = $itemId;
                $subtotal += $value;

                foreach ($g as $x) {
                    $b = ProductBatch::create([
                        'warehouse_id' => $whId,
                        'product_id' => $v->pid,
                        'product_variant_id' => $v->vid,
                        'batch_no' => $x['batchNo'],
                        'expiry_date' => $x['exp'] ? $x['exp']->toDateString() : null,
                        'qty' => $x['qty'],
                        'buy_price' => $x['hpp'],
                        'purchase_order_item_id' => $itemId,
                        'supplier_id' => $x['supplierId'],
                    ]);
                    $batchIds[$b->id] = $x['qty'];
                    $totalQty += $x['qty'];
                }
            }
            DB::table('purchase_orders')->where('id', $poId)->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            // penerimaan barang otomatis supaya qty diterima tercatat (dasar alokasi pembayaran supplier)
            (new \App\Services\PoReceiptService())->createForPo($poId, $userId ? (int) $userId : null, "Stok awal hasil SO ({$label})");
        }

        // HPP master varian mengikuti HPP baru dari file (batch terbaru = kadaluarsa terjauh)
        $hppUpdated = 0;
        foreach (collect($valid)->where('hppFromFile', true)->groupBy(fn($x) => $x['variant']->vid) as $vid => $g) {
            $latest = $g->sortByDesc(fn($x) => $x['exp'] ? $x['exp']->timestamp : 0)->first();
            if ((float) $latest['variant']->product_hpp !== (float) $latest['hpp']) {
                DB::table('product_variants')->where('id', $vid)->update(['product_hpp' => (int) round($latest['hpp']), 'updated_at' => now()]);
                $hppUpdated++;
            }
        }

        // sinkron stok produk + verifikasi S == qty
        $pids = collect($valid)->pluck('variant.pid')->unique()->all();
        Product::bulkSyncStock($pids);
        $bad = 0;
        if ($batchIds) {
            $now = ProductBatch::withSisa()->whereIn('id', array_keys($batchIds))->get();
            foreach ($now as $b) {
                if ((int) $b->current_stock !== (int) $batchIds[$b->id]) {
                    $bad++;
                    $this->err("Batch #{$b->id} {$b->batch_no}", "stok terhitung {$b->current_stock} != {$batchIds[$b->id]}");
                }
            }
        }

        return [
            'rows' => count($rows), 'valid' => count($valid), 'po' => $newPo, 'items' => count($itemIds),
            'batches' => count($batchIds), 'hpp_updated' => $hppUpdated, 'qty' => $totalQty, 'verify_bad' => $bad,
            'expected_qty' => collect($valid)->sum('qty'),
        ];
    }

    private function normPo($po): string
    {
        $po = trim(preg_replace('/\s+/', ' ', (string) $po));

        return preg_replace('/\s*-\s*/', '-', $po);
    }

    /** @return array{0: Carbon, 1: bool} tanggal dari nomor PO (bulan/tahun), tanggal 1. */
    private function poDate(string $po, Carbon $fallback): array
    {
        if (preg_match('#BL/(\d{4})/(\d{2})/#', $po, $m)) {
            return [Carbon::create((int) $m[1], (int) $m[2], 1), true];
        }
        if (preg_match('#/([IVX]{1,4})/(\d{4})$#', $po, $m) && isset(self::ROMAN[$m[1]])) {
            return [Carbon::create((int) $m[2], self::ROMAN[$m[1]], 1), true];
        }
        if (preg_match('#/(\d{2})/(\d{4})$#', $po, $m) && (int) $m[1] >= 1 && (int) $m[1] <= 12) {
            return [Carbon::create((int) $m[2], (int) $m[1], 1), true];
        }

        // format hari/bulan/tahun-2-digit, mis. PO/05/08/25/Frec/SPM = 5 Agustus 2025
        if (preg_match('#/(\d{2})/(\d{2})/(\d{2})/#', $po, $m) && (int) $m[2] >= 1 && (int) $m[2] <= 12 && (int) $m[1] >= 1 && (int) $m[1] <= 31) {
            return [Carbon::create(2000 + (int) $m[3], (int) $m[2], (int) $m[1]), true];
        }

        return [$fallback->copy(), false];
    }

    private function report(array $s): void
    {
        $this->newLine();
        $this->table(['Hasil import stok', 'Jumlah'], [
            ['Baris Raw Produk', $s['rows']],
            ['Baris valid', $s['valid']],
            ['PO baru dibuat', $s['po']],
            ['Item PO', $s['items']],
            ['Batch dibuat', $s['batches']],
            ['HPP master varian diperbarui', $s['hpp_updated']],
            ['Total qty masuk', $s['qty'] . ' (harapan ' . $s['expected_qty'] . ')'],
            ['Batch gagal verifikasi stok', $s['verify_bad']],
        ]);

        $this->newLine();
        if ($this->warnings) {
            $this->warn(count($this->warnings) . ' PERINGATAN (tidak menghalangi):');
            $this->table(['Lokasi', 'Pesan'], $this->warnings);
        }
        if ($this->errors) {
            $this->error(count($this->errors) . ' ERROR (baris ini tidak bisa diimpor):');
            $this->table(['Lokasi', 'Pesan'], $this->errors);
        } else {
            $this->info('Tidak ada ERROR.');
        }
    }
}
