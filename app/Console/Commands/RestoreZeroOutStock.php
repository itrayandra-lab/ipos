<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pulihkan hasil stock:zero-out berdasarkan label arsip:
 *  - hapus dokumen return "return adjustment" yang dibuat run tsb,
 *  - kurangi lagi M batch yang tadinya ditambah (adjustment masuk),
 *  - kembalikan nomor PO (hapus awalan OLD-).
 * Default = simulasi (rollback). --execute untuk menjalankan sungguhan.
 * Verifikasi: S tiap batch harus kembali sama dengan S di arsip.
 */
class RestoreZeroOutStock extends Command
{
    protected $signature = 'stock:zero-out-restore {label : Label arsip (lihat tabel stock_zero_out_archives)}
        {--execute : Jalankan sungguhan (tanpa ini hanya simulasi + rollback)}
        {--batches= : Pulihkan HANYA batch ini (id dipisah koma). Return atas batch tsb dibatalkan; PO tidak diubah}';

    protected $description = 'Pulihkan stok & nomor PO dari arsip stock:zero-out.';

    /** Pulihkan sebagian: batalkan return pembersihan hanya untuk batch tertentu. */
    private function restoreBatches(string $label, $rows, bool $execute): int
    {
        $ids = collect(explode(',', (string) $this->option('batches')))->map(fn($x) => (int) trim($x))->filter()->unique()->values();
        $sel = $rows->whereIn('batch_id', $ids->all());
        $missing = $ids->diff($sel->pluck('batch_id'));
        if ($missing->isNotEmpty()) {
            $this->error('Batch tidak ada di arsip ' . $label . ': ' . $missing->implode(', '));
            return 1;
        }
        if ($sel->where('action', 'adjustment_in')->isNotEmpty() || $sel->where('action', 'sample')->isNotEmpty()) {
            $this->error('Pemulihan sebagian hanya untuk batch yang di-return (bukan adjustment/sampel).');
            return 1;
        }

        $returnIds = DB::table('supplier_returns')->where('notes', 'like', '%' . $label . '%')->pluck('id');
        DB::beginTransaction();
        try {
            $q = DB::table('supplier_return_items')->whereIn('supplier_return_id', $returnIds)->whereIn('product_batch_id', $ids->all());
            $n = $q->count();
            $touched = $q->pluck('supplier_return_id')->unique();
            $q->delete();
            $emptied = 0;
            foreach ($touched as $rid) {
                if (!DB::table('supplier_return_items')->where('supplier_return_id', $rid)->exists()) {
                    DB::table('supplier_returns')->where('id', $rid)->delete();
                    $emptied++;
                }
            }
            $this->line("Dihapus: {$n} item return ({$emptied} dokumen return ikut dihapus karena kosong).");

            DB::table('stock_zero_out_archives')->where('run_label', $label)->whereIn('batch_id', $ids->all())
                ->update(['action' => 'none', 'note' => DB::raw("CONCAT(COALESCE(note,''), ' | dipulihkan')")]);

            Product::bulkSyncStock($sel->pluck('product_id')->filter()->unique()->all());
            \App\Models\PurchaseOrderItem::syncReturnedForBatches($ids->all());

            $bad = [];
            $now = ProductBatch::withSisa()->whereIn('id', $ids->all())->get()->keyBy('id');
            foreach ($sel as $r) {
                $b = $now[$r->batch_id] ?? null;
                if ($b && (int) $b->current_stock !== (int) $r->stock_s) {
                    $bad[] = [$r->batch_id, $r->batch_no, $r->stock_s, $b->current_stock];
                }
            }
            $this->table(['Batch id', 'No batch', 'Stok arsip', 'Stok sekarang'],
                $sel->map(fn($r) => [$r->batch_id, $r->batch_no, $r->stock_s, $now[$r->batch_id]->current_stock ?? '?'])->all());
            if ($bad) {
                DB::rollBack();
                $this->error('Stok tidak kembali ke nilai arsip. Di-rollback.');
                return 1;
            }
            if ($execute) {
                DB::commit();
                $this->info('PULIH: ' . $ids->count() . ' batch kembali ke stok arsip.');
            } else {
                DB::rollBack();
                $this->info('SIMULASI OK. Di-rollback; jalankan dengan --execute untuk memulihkan.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error, di-rollback: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    public function handle(): int
    {
        $label = $this->argument('label');
        $execute = (bool) $this->option('execute');

        $rows = DB::table('stock_zero_out_archives')->where('run_label', $label)->get();
        if ($rows->isEmpty()) {
            $this->error("Arsip dengan label '{$label}' tidak ditemukan.");
            $this->line('Label tersedia: ' . DB::table('stock_zero_out_archives')->distinct()->pluck('run_label')->implode(', '));
            return 1;
        }

        if ($this->option('batches')) {
            return $this->restoreBatches($label, $rows, $execute);
        }

        $returnIds = DB::table('supplier_returns')->where('notes', 'like', '%' . $label . '%')->pluck('id');
        $adjust = $rows->where('action', 'adjustment_in');
        $poMap = DB::table('purchase_order_number_archives')->where('run_label', $label)->get();

        $this->info("Arsip '{$label}': {$rows->count()} batch | dokumen return: {$returnIds->count()} | adjustment masuk: {$adjust->count()} | PO di-OLD: {$poMap->count()}");

        DB::beginTransaction();
        try {
            $items = DB::table('supplier_return_items')->whereIn('supplier_return_id', $returnIds)->count();
            DB::table('supplier_return_items')->whereIn('supplier_return_id', $returnIds)->delete();
            DB::table('supplier_returns')->whereIn('id', $returnIds)->delete();
            $this->line("Dihapus: {$returnIds->count()} dokumen return, {$items} item.");

            $trxIds = DB::table('transactions')->where('is_sample', 1)->where('notes', 'Sampel selisih SO - ' . $label)->pluck('id');
            DB::table('transaction_items')->whereIn('transaction_id', $trxIds)->delete();
            DB::table('transactions')->whereIn('id', $trxIds)->delete();
            $this->line("Dihapus: {$trxIds->count()} transaksi sampel.");

            foreach ($adjust as $a) {
                ProductBatch::where('id', $a->batch_id)->update(['qty' => DB::raw('qty - ' . (int) $a->action_qty)]);
            }

            foreach ($poMap as $m) {
                if (DB::table('purchase_orders')->where('po_number', $m->old_po_number)->where('id', '!=', $m->purchase_order_id)->exists()) {
                    throw new \Exception("Nomor {$m->old_po_number} sudah dipakai PO lain; ubah/hapus PO baru itu dulu.");
                }
                DB::table('purchase_orders')->where('id', $m->purchase_order_id)->update(['po_number' => $m->old_po_number]);
            }

            Product::bulkSyncStock($rows->pluck('product_id')->filter()->unique()->all());
            \App\Models\PurchaseOrderItem::syncReturnedForBatches($rows->pluck('batch_id')->all());

            $bad = [];
            $now = ProductBatch::withSisa()->whereIn('id', $rows->pluck('batch_id'))->get()->keyBy('id');
            foreach ($rows as $r) {
                $b = $now[$r->batch_id] ?? null;
                if ($b && (int) $b->current_stock !== (int) $r->stock_s) {
                    $bad[] = [$r->batch_id, $r->batch_no, $r->stock_s, $b->current_stock];
                }
            }

            if ($bad) {
                $this->error(count($bad) . ' batch tidak kembali ke S arsip (contoh):');
                $this->table(['Batch id', 'No batch', 'S arsip', 'S sekarang'], array_slice($bad, 0, 15));
                DB::rollBack();
                $this->warn('Di-rollback, tidak ada yang berubah.');
                return 1;
            }

            if ($execute) {
                DB::commit();
                $this->info('PULIH. Semua batch kembali ke stok arsip, nomor PO dikembalikan.');
            } else {
                DB::rollBack();
                $this->info('SIMULASI OK: semua S kembali sesuai arsip. Di-rollback; jalankan dengan --execute untuk memulihkan.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error, di-rollback: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
