<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pembersihan data: kosongkan stok semua gudang.
 *  - S > 0  -> return supplier (supplier batch -> supplier produk -> PT Lunaray Cahya Abadi),
 *              keterangan "return adjustment", dipecah per supplier + gudang.
 *  - S < 0  -> tambah M batch sebesar |S| (adjustment masuk) sampai S = 0.
 * Default = DRY RUN: seluruh proses dijalankan di dalam transaksi lalu di-rollback,
 * sehingga hasil akhir (S per batch) terbukti sebelum --execute.
 * --execute: arsip (tabel + CSV) lebih dulu, lalu commit bila semua S = 0.
 */
class ZeroOutStock extends Command
{
    protected $signature = 'stock:zero-out
        {--execute : Jalankan sungguhan (tanpa ini hanya simulasi + rollback)}
        {--date= : Tanggal return/adjustment (Y-m-d), default hari ini}
        {--user= : ID user pembuat dokumen return, default super_admin pertama}
        {--label= : Label arsip, default zero-out-YYYYMMDD-HHMMSS}
        {--rename-po : Tambahkan awalan OLD- pada semua nomor PO lama}
        {--so= : Path file Excel hasil SO. Selisih (sistem > SO) dicatat sebagai transaksi sampel}
        {--map=kbp:1,nanjung:4,apotik:5,bintaro:3 : Pemetaan kolom gudang di file SO => warehouse_id}
        {--allow-unmatched : Lanjut walau ada baris SO yang tidak cocok dengan sistem}';

    protected $description = 'Kosongkan stok semua gudang (return supplier + adjustment minus), dengan arsip.';

    public function handle(): int
    {
        $execute = (bool) $this->option('execute');
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $label = $this->option('label') ?: 'zero-out-' . now()->format('Ymd-His');

        $default = Supplier::where('name', 'like', '%Lunaray%')->first();
        if (!$default) {
            $this->error('Supplier default PT Lunaray Cahya Abadi tidak ditemukan.');
            return 1;
        }

        if ($execute && !Schema::hasTable('stock_zero_out_archives')) {
            $this->error('Tabel arsip belum ada. Jalankan: php artisan migrate');
            return 1;
        }

        $batches = ProductBatch::withSisa()->with(['product', 'warehouse'])->get();
        $before = $batches->map(fn($b) => [
            'batch' => $b,
            'm' => (int) $b->qty,
            's' => (int) $b->current_stock,
        ]);

        $plus = $before->filter(fn($r) => $r['s'] > 0);
        $minus = $before->filter(fn($r) => $r['s'] < 0);
        $this->info("Batch aktif: {$before->count()} | stok positif: {$plus->count()} (" . $plus->sum('s') . " pcs) | minus: {$minus->count()} (" . $minus->sum('s') . " pcs)");

        if ($execute) {
            $this->archive($before, $label);
        }

        DB::beginTransaction();
        try {
            $this->applyAdjustments($minus, $label, $execute);

            $samples = [];
            if ($this->option('so')) {
                $samples = $this->planSamples($before);
                if ($samples === null) {
                    DB::rollBack();
                    return 1;
                }
                $this->createSampleTransactions($samples, $before, $date, $label, $execute);
                // kurangi stok yang akan di-return sebesar sampel
                $plus = $plus->map(function ($r) use ($samples) {
                    $r['s'] -= $samples[$r['batch']->id] ?? 0;
                    return $r;
                })->filter(fn($r) => $r['s'] > 0);
            }

            $returns = $this->applyReturns($plus, $default, $date, $label);

            if ($this->option('rename-po')) {
                $this->renamePos($label, $execute);
            }

            Product::bulkSyncStock($before->pluck('batch.product_id')->unique()->all());

            $remaining = ProductBatch::withSisa()->with('warehouse')->get()
                ->filter(fn($b) => (int) $b->current_stock !== 0);

            $this->newLine();
            $this->table(['Supplier', 'Gudang', 'Dokumen', 'Item', 'Qty'], $returns);

            if ($remaining->isNotEmpty()) {
                $this->error("GAGAL VERIFIKASI: {$remaining->count()} batch belum nol setelah proses. Contoh:");
                $this->table(['Batch id', 'No batch', 'Gudang', 'S'],
                    $remaining->take(15)->map(fn($b) => [$b->id, $b->batch_no, $b->warehouse->name ?? '-', $b->current_stock])->all());
                DB::rollBack();
                $this->warn('Di-rollback, tidak ada yang berubah.');
                return 1;
            }

            if ($execute) {
                DB::commit();
                $this->info("SELESAI. Semua stok = 0. Arsip: label '{$label}'.");
            } else {
                DB::rollBack();
                $this->info('SIMULASI OK: semua batch menjadi 0. Di-rollback, belum ada yang berubah. Jalankan dengan --execute untuk eksekusi.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error, di-rollback: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Hitung sampel per batch: per (gudang, produk+batch) bila S > F maka sampel = S - F,
     * dibagi ke baris batch (urut ID). Hanya gudang yang ada di --map dan produk+batch yang
     * ada di file SO. Return null bila perlu dibatalkan.
     */
    private function planSamples($before): ?array
    {
        $map = [];
        foreach (explode(',', (string) $this->option('map')) as $pair) {
            [$k, $v] = array_pad(explode(':', trim($pair)), 2, null);
            if ($k !== null && $v !== null) {
                $map[strtolower(trim($k))] = (int) $v;
            }
        }
        $service = new \App\Services\StockOpnameCheckService();
        $so = $service->soByWarehouse($this->option('so'), $map);
        $this->info('Pemetaan gudang SO: ' . collect($map)->map(fn($v, $k) => "{$k}=>{$v}")->implode(', '));

        $systemKeys = [];
        foreach ($before as $r) {
            $systemKeys[\App\Services\StockOpnameCheckService::batchKey($r['batch'])] = true;
        }
        $unmatched = array_filter($so, fn($row, $k) => !isset($systemKeys[$k]), ARRAY_FILTER_USE_BOTH);
        if ($unmatched) {
            $this->warn(count($unmatched) . ' baris SO belum cocok dengan sistem (perlu dipetakan):');
            $this->table(['Baris', 'Merek', 'Nama di SO', 'Batch'],
                array_map(fn($u) => [$u['line'], $u['brand'], $u['name'], $u['batch']], array_slice(array_values($unmatched), 0, 25)));
            if (!$this->option('allow-unmatched')) {
                $this->error('Dibatalkan. Petakan dulu, atau jalankan dengan --allow-unmatched (sampel produk tsb tidak dihitung).');
                return null;
            }
        }

        $groups = [];
        foreach ($before as $r) {
            if ($r['s'] <= 0) {
                continue;
            }
            $b = $r['batch'];
            $key = \App\Services\StockOpnameCheckService::batchKey($b);
            if (!isset($so[$key]) || !in_array((int) $b->warehouse_id, $map, true)) {
                continue;
            }
            $groups[$b->warehouse_id . '|' . $key][] = $r;
        }

        $samples = [];
        foreach ($groups as $gk => $rows) {
            [$wh, $key] = explode('|', $gk, 2);
            $f = (int) ($so[$key]['wh'][(int) $wh] ?? 0);
            $totalS = array_sum(array_column($rows, 's'));
            $need = max($totalS - $f, 0);
            usort($rows, fn($a, $b) => $a['batch']->id <=> $b['batch']->id);
            foreach ($rows as $r) {
                if ($need <= 0) {
                    break;
                }
                $take = min($need, $r['s']);
                $samples[$r['batch']->id] = $take;
                $need -= $take;
            }
        }

        $this->info('Sampel: ' . count($samples) . ' batch, ' . array_sum($samples) . ' pcs.');

        return $samples;
    }

    /** Satu transaksi sampel per gudang (is_sample, total 0, HPP + relasi PO batch dipertahankan). */
    private function createSampleTransactions(array $samples, $before, Carbon $date, string $label, bool $execute): void
    {
        $byId = $before->keyBy(fn($r) => $r['batch']->id);
        $perWh = [];
        foreach ($samples as $batchId => $qty) {
            $perWh[$byId[$batchId]['batch']->warehouse_id][$batchId] = $qty;
        }

        $rows = [];
        foreach ($perWh as $whId => $items) {
            $wh = DB::table('warehouses')->find($whId);
            $trx = \App\Models\Transaction::create([
                'transaction_code' => \App\Services\TransactionCodeService::generate($date, $wh->code ?? ''),
                'user_id' => $this->userId(),
                'customer_name' => 'Adjustment SO',
                'source' => 'offline-store',
                'notes' => 'Sampel selisih SO - ' . $label,
                'other_fees' => [],
                'is_sample' => true,
                'total_amount' => 0,
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'delivery_type' => 'pickup',
                'delivery_desc' => 'Adjustment SO',
                'midtrans_order_id' => 'ADJ-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'transaction_date' => $date->toDateString(),
                'warehouse_id' => $whId,
            ]);

            foreach ($items as $batchId => $qty) {
                $b = $byId[$batchId]['batch'];
                \App\Models\TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $b->product_id,
                    'product_variant_id' => $b->product_variant_id,
                    'product_batch_id' => $b->id,
                    'purchase_order_item_id' => $b->purchase_order_item_id,
                    'buy_price' => $b->buy_price ?? 0,
                    'qty' => $qty,
                    'price' => 0,
                    'discount' => 0,
                    'subtotal' => 0,
                    'is_sample' => true,
                ]);
                if ($execute) {
                    DB::table('stock_zero_out_archives')->where('run_label', $label)->where('batch_id', $b->id)
                        ->update(['action' => 'sample', 'action_qty' => $qty, 'note' => 'sampel ' . $trx->transaction_code]);
                }
            }
            $rows[] = [$wh->name ?? $whId, $trx->transaction_code, count($items), array_sum($items)];
        }

        if ($rows) {
            $this->table(['Gudang', 'Transaksi sampel', 'Item', 'Qty'], $rows);
        }
    }

    /** S < 0: naikkan M sebesar |S| agar S = 0. */
    private function applyAdjustments($minus, string $label, bool $execute): void
    {
        foreach ($minus as $r) {
            $b = $r['batch'];
            $add = -$r['s'];
            ProductBatch::where('id', $b->id)->update(['qty' => DB::raw('qty + ' . (int) $add)]);
            if ($execute) {
                DB::table('stock_zero_out_archives')->where('run_label', $label)->where('batch_id', $b->id)
                    ->update(['action' => 'adjustment_in', 'action_qty' => $add, 'note' => 'tambah adjustment']);
            }
        }
    }

    /** S > 0: return per supplier + gudang. */
    private function applyReturns($plus, Supplier $default, Carbon $date, string $label): array
    {
        $groups = $plus->groupBy(function ($r) use ($default) {
            $b = $r['batch'];
            $sid = $b->supplier_id ?: ($b->product->supplier_id ?? null) ?: $default->id;
            return $sid . '|' . $b->warehouse_id;
        });

        $suppliers = Supplier::pluck('name', 'id');
        $rows = [];

        foreach ($groups as $key => $items) {
            [$supplierId, $warehouseId] = array_map('intval', explode('|', $key));

            $return = SupplierReturn::create([
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'user_id' => $this->userId(),
                'return_number' => $this->nextReturnNumber($date),
                'return_date' => $date->toDateString(),
                'total_amount' => 0,
                'notes' => 'Return adjustment (pembersihan data stok) - ' . $label,
                'status' => 'completed',
            ]);

            foreach ($items as $r) {
                $b = $r['batch'];
                $price = (float) ($b->buy_price ?? 0);
                SupplierReturnItem::create([
                    'supplier_return_id' => $return->id,
                    'product_id' => $b->product_id,
                    'product_variant_id' => $b->product_variant_id,
                    'product_batch_id' => $b->id,
                    'qty' => $r['s'],
                    'buy_price' => $price,
                    'total' => $price * $r['s'],
                    'reason' => 'return adjustment',
                ]);
                if ($this->option('execute')) {
                    DB::table('stock_zero_out_archives')->where('run_label', $label)->where('batch_id', $b->id)
                        ->update(['action' => 'return', 'action_qty' => $r['s'],
                            'note' => DB::raw("CONCAT(COALESCE(note, ''), IF(note IS NULL, '', ' + '), " . DB::getPdo()->quote('return ' . $return->return_number) . ")")]);
                }
            }

            $rows[] = [$suppliers[$supplierId] ?? $supplierId, $items->first()['batch']->warehouse->name ?? $warehouseId,
                $return->return_number, $items->count(), $items->sum('s')];
        }

        return $rows;
    }

    private function userId(): int
    {
        static $id = null;
        if ($id === null) {
            $id = (int) ($this->option('user')
                ?: DB::table('users')->where('role', 'super_admin')->orderBy('id')->value('id')
                ?: DB::table('users')->orderBy('id')->value('id'));
        }

        return $id;
    }

    private function nextReturnNumber(Carbon $date): string
    {
        $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$date->month - 1];
        $yy = $date->format('y');
        $last = SupplierReturn::whereYear('return_date', $date->year)->whereMonth('return_date', $date->month)
            ->orderByDesc('id')->first();
        $n = 0;
        if ($last && $last->return_number) {
            $parts = explode('/', $last->return_number);
            if (count($parts) === 5) {
                $n = (int) $parts[4];
            }
        }

        return "RT/BL/{$roman}/{$yy}/" . str_pad($n + 1, 4, '0', STR_PAD_LEFT);
    }

    /** Awalan OLD- pada nomor PO; nomor asli disimpan di tabel arsip. */
    private function renamePos(string $label, bool $execute): void
    {
        $pos = DB::table('purchase_orders')->where('po_number', 'not like', 'OLD-%')->get(['id', 'po_number']);
        foreach ($pos as $po) {
            $new = 'OLD-' . $po->po_number;
            if (DB::table('purchase_orders')->where('po_number', $new)->exists()) {
                throw new \Exception("Nomor {$new} sudah dipakai.");
            }
            if ($execute) {
                DB::table('purchase_order_number_archives')->insert([
                    'run_label' => $label, 'purchase_order_id' => $po->id,
                    'old_po_number' => $po->po_number, 'new_po_number' => $new, 'created_at' => now(),
                ]);
            }
            DB::table('purchase_orders')->where('id', $po->id)->update(['po_number' => $new]);
        }
        $this->info('Nomor PO diberi awalan OLD-: ' . $pos->count() . ' PO.');
    }

    /** Snapshot detail ke tabel + CSV sebelum ada perubahan. */
    private function archive($before, string $label): void
    {
        $sources = DB::table('product_batches as b')
            ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'b.purchase_order_item_id')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.id', '=', 'b.goods_receipt_item_id')
            ->leftJoin('stock_movements as sm', 'sm.id', '=', 'b.stock_movement_id')
            ->get(['b.id', 'po.po_number', 'sm.reference_number as movement_ref'])->keyBy('id');

        $dir = storage_path('app/stock-archives');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $fh = fopen("{$dir}/{$label}.csv", 'w');
        $head = ['batch_id', 'merek', 'produk', 'gudang', 'no_batch', 'expiry', 'supplier_id', 'buy_price', 'M', 'T', 'R', 'RR', 'MV', 'S', 'po_number', 'movement_ref'];
        fputcsv($fh, $head);

        foreach ($before->chunk(200) as $chunk) {
            $insert = [];
            foreach ($chunk as $r) {
                $b = $r['batch'];
                $src = $sources[$b->id] ?? null;
                $insert[] = [
                    'run_label' => $label, 'batch_id' => $b->id, 'product_id' => $b->product_id,
                    'product_variant_id' => $b->product_variant_id, 'warehouse_id' => $b->warehouse_id,
                    'supplier_id' => $b->supplier_id, 'batch_no' => $b->batch_no,
                    'expiry_date' => optional($b->expiry_date)->toDateString(), 'buy_price' => $b->buy_price,
                    'qty_m' => (int) $b->qty, 'sales_t' => (int) ($b->transaction_items_sum_qty ?? 0),
                    'return_r' => (int) ($b->supplier_return_items_sum_qty ?? 0),
                    'return_received_rr' => (int) ($b->supplier_return_received_sum_qty ?? 0),
                    'movement_mv' => (int) ($b->stock_movement_items_sum_qty ?? 0), 'stock_s' => $r['s'],
                    'po_number' => $src->po_number ?? null, 'movement_ref' => $src->movement_ref ?? null,
                    'action' => 'none', 'action_qty' => 0, 'note' => null, 'created_at' => now(),
                ];
                fputcsv($fh, [$b->id, $b->product->merek->name ?? '', $b->product->name ?? '', $b->warehouse->name ?? '',
                    $b->batch_no, optional($b->expiry_date)->format('Y-m-d'), $b->supplier_id, $b->buy_price,
                    (int) $b->qty, (int) ($b->transaction_items_sum_qty ?? 0), (int) ($b->supplier_return_items_sum_qty ?? 0),
                    (int) ($b->supplier_return_received_sum_qty ?? 0), (int) ($b->stock_movement_items_sum_qty ?? 0), $r['s'],
                    $src->po_number ?? '', $src->movement_ref ?? '']);
            }
            DB::table('stock_zero_out_archives')->insert($insert);
        }
        fclose($fh);
        $this->info("Arsip tersimpan: tabel stock_zero_out_archives (label {$label}) + {$dir}/{$label}.csv");
    }
}
