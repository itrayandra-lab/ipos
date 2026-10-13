<?php

namespace App\Http\Controllers\Admin\Adjustment;

use App\Http\Controllers\Controller;
use App\Models\Merek;
use App\Models\Product;
use App\Models\ProductNetto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class ProductAdjustmentController extends Controller
{
    /**
     * Halaman List Produk untuk Adjustment Data.
     * Kolom sesuai DB: merek.name (tabel merek),
     * products.name (tabel products),
     * jumlah variasi = COUNT product_variants via product_nettos.
     */
    public function index()
    {
        $merek = Merek::orderBy('name', 'ASC')->get(['id', 'name', 'code']);
        // Produk yang belum punya netto/varian sama sekali
        $noNetto = Product::doesntHave('nettos')->with('merek:id,name')->orderBy('id')->get(['id', 'merek_id', 'name', 'code']);

        return view('admin.adjustment.products.index')->with([
            'sb' => 'AdjustmentProduct',
            'merek' => $merek,
            'noNetto' => $noNetto,
        ]);
    }

    /**
     * Normalisasi nama untuk deteksi duplikat:
     * - huruf kecil, buang semua spasi & tanda baca (calmup == calm up == calm-up)
     * - menangani kasus "tanpa netto" vs "ada netto" yang namanya ditulis beda-beda
     */
    private function normalizeName($name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $name));
    }

    /**
     * DataTables source: 1 baris = 1 netto.
     * Kolom: Merek | Nama Produk | Netto | Harga Produk (dari varian, sama
     * seperti halaman Pricing: product_hpp, hpp_rayandra+margin, ray_store,
     * het_online+status). ID produk tetap sama untuk semua netto-nya.
     */
    public function getall(Request $request)
    {
        $base = DB::table('product_nettos as pn')
            ->join('products as p', 'p.id', '=', 'pn.product_id')
            ->leftJoin('merek as m', 'm.id', '=', 'p.merek_id')
            ->leftJoin('product_variants as v', 'v.product_netto_id', '=', 'pn.id')
            ->select([
                'pn.id as netto_id',
                'pn.netto_value',
                'pn.satuan',
                'p.id as product_id',
                'p.merek_id',
                'p.name as product_name',
                'p.code as product_code',
                'm.name as merek_name',
                'v.id as variant_id',
                'v.sku_code',
                'v.product_hpp',
                'v.hpp_rayandra',
                'v.margin_hpp',
                'v.ray_store',
                'v.het_online',
                'v.is_approved',
            ]);

        // Filter opsional per merek
        if ($request->filled('merek_id')) {
            $base->where('p.merek_id', $request->merek_id);
        }

        // Peta grup duplikat dengan nama ternormalisasi (tahan beda spasi/tanda baca/huruf besar).
        $allNames = Product::select('id', 'name')->get();
        $groups = [];
        foreach ($allNames as $row) {
            $groups[$this->normalizeName($row->name)][] = $row->id;
        }
        $dupGroupSize = []; // product_id => ukuran grup
        foreach ($groups as $ids) {
            if (count($ids) > 1) {
                foreach ($ids as $pid) {
                    $dupGroupSize[$pid] = count($ids);
                }
            }
        }

        // Filter hanya grup nama duplikat (versi normalisasi: calmup == calm up)
        if ($request->boolean('only_duplicates')) {
            $dupIds = array_keys($dupGroupSize);
            $base->whereIn('p.id', $dupIds ?: [0]);
        }

        return DataTables::of($base)
            ->addIndexColumn()
            ->filterColumn('merek_name', function ($q, $kw) {
                $q->where('m.name', 'like', "%{$kw}%");
            })
            ->filterColumn('nama_produk', function ($q, $kw) {
                $q->where(function ($w) use ($kw) {
                    $w->where('p.name', 'like', "%{$kw}%")
                        ->orWhere('p.code', 'like', "%{$kw}%");
                });
            })
            ->filterColumn('netto', function ($q, $kw) {
                $q->where(function ($w) use ($kw) {
                    $w->where('pn.netto_value', 'like', "%{$kw}%")
                        ->orWhere('pn.satuan', 'like', "%{$kw}%");
                });
            })
            ->addColumn('nama_produk', function ($row) use ($dupGroupSize) {
                $html = e($row->product_name);
                $html .= '<br><small class="text-muted">Kode: ' . e($row->product_code ?? '-') . ' | ID Produk: ' . $row->product_id . '</small>';
                if (isset($dupGroupSize[$row->product_id])) {
                    $html .= ' <span class="badge badge-warning">' . $dupGroupSize[$row->product_id] . 'x mirip</span>';
                }

                return $html;
            })
            ->addColumn('netto', function ($row) {
                $val = trim(($row->netto_value ?? '') . ' ' . ($row->satuan ?? ''));
                $html = '<strong>' . e($val !== '' ? $val : '(tanpa netto)') . '</strong>';
                if ($row->sku_code) {
                    $html .= '<br><small class="text-muted">SKU: ' . e($row->sku_code) . '</small>';
                }

                return $html;
            })
            ->addColumn('harga', function ($row) {
                // Sama seperti halaman Pricing; '-' jika netto belum punya varian
                if (!$row->variant_id) {
                    return '<span class="text-muted small">-</span>';
                }
                $rp = fn($v) => 'Rp ' . number_format((float) ($v ?? 0), 0, ',', '.');
                $pct = $row->product_hpp > 0
                    ? round(((float) ($row->margin_hpp ?? 0) / (float) $row->product_hpp) * 100, 1)
                    : 0;
                $status = $row->is_approved
                    ? "<span class='badge badge-success px-2 py-1 mt-1'>Approved</span>"
                    : "<span class='badge badge-warning px-2 py-1 mt-1'>Pending</span>";

                return "<div class='small'>"
                    . "<div class='d-flex justify-content-between mb-1'><span class='text-muted'>HPP:</span><span class='font-weight-bold'>{$rp($row->product_hpp)}</span></div>"
                    . "<div class='d-flex justify-content-between mb-1'><span class='text-muted'>HPP Ray:</span><span class='font-weight-bold text-info'>{$rp($row->hpp_rayandra)}</span></div>"
                    . "<div class='text-right mb-1'><small class='text-muted'>Margin {$pct}%</small></div>"
                    . "<div class='d-flex justify-content-between mb-1'><span class='text-muted'>Ray Store:</span><span class='font-weight-bold'>{$rp($row->ray_store)}</span></div>"
                    . "<div class='d-flex justify-content-between mb-1'><span class='text-muted'>HET:</span><span class='font-weight-bold text-primary'>{$rp($row->het_online)}</span></div>"
                    . "<div class='text-right'>{$status}</div>"
                    . '</div>';
            })
            ->addColumn('action', function ($row) {
                return '<div class="btn-group" role="group">'
                    . '<a href="' . route('admin.adjustment.products.show', $row->netto_id) . '" class="btn btn-sm btn-info">Detail</a>'
                    . '<button type="button" class="btn btn-sm btn-warning btn-merge" data-id="' . $row->product_id . '" data-name="' . e($row->product_name) . '">Merge</button>'
                    . '</div>';
            })
            ->addColumn('stok', function ($row) {
                // Model Jurnal: M = total terima tercatat, K = transaksi + return + movement tersync, S = M - K.
                $q = \App\Models\ProductBatch::query()
                    ->withSum('transactionItems as k_transaksi', 'qty')
                    ->withSum('supplierReturnItems as k_return', 'qty')
                    ->orderBy('id');
                if ($row->variant_id) {
                    $q->where('product_variant_id', $row->variant_id);
                } else {
                    $q->where('product_id', $row->product_id)->whereNull('product_variant_id');
                }
                $batches = $q->get();
                if ($batches->isEmpty()) {
                    return '<span class="text-muted small">-</span>';
                }
                $f = fn($v) => number_format((int) ($v ?? 0), 0, ',', '.');
                $mvMap = DB::table('stock_movement_items as i')
                    ->join('stock_movements as m', 'm.id', '=', 'i.stock_movement_id')
                    ->whereIn('i.product_batch_id', $batches->pluck('id'))
                    ->whereIn('m.status', ['transit', 'completed'])
                    ->groupBy('i.product_batch_id')
                    ->select('i.product_batch_id as bid', DB::raw('SUM(i.qty) as tq'), DB::raw('COUNT(*) as tc'))
                    ->get()->keyBy('bid');
                $lines = $batches->map(function ($b) use ($f, $mvMap) {
                    $m = (int) $b->qty;
                    $kt = (int) ($b->k_transaksi ?? 0);
                    $kr = (int) ($b->k_return ?? 0);
                    $km = (int) ($mvMap->get($b->id)->tq ?? 0);
                    $k = $kt + $kr + $km;
                    $s = $m - $k;
                    $split = ($km > 0 || $kr > 0)
                        ? '<br><span class="text-muted" style="font-size:11px;">keluar &rarr; T:' . $f($kt) . ' R:' . $f($kr) . ' MV:' . $f($km) . '</span>' : '';

                    return '<div class="mb-1"><strong>' . e($b->batch_no ?? ('#' . $b->id)) . '</strong> : '
                        . '<span class="text-muted">M:</span> ' . $f($m) . ', '
                        . '<span class="text-muted">K:</span> ' . $f($k) . ', '
                        . '<span class="text-muted">S:</span> <strong>' . $f($s) . '</strong>' . $split . '</div>';
                });

                // Total stok (penjumlahan semua batch di cell ini)
                $totM = (int) $batches->sum('qty');
                $totKt = (int) $batches->sum('k_transaksi');
                $totKr = (int) $batches->sum('k_return');
                $totKm = (int) $mvMap->sum('tq');
                $totK = $totKt + $totKr + $totKm;
                $totS = $totM - $totK;
                $lines[] = '<div class="mt-1 pt-1 border-top"><strong>Total</strong> : '
                    . '<span class="text-muted">M:</span> ' . $f($totM) . ', '
                    . '<span class="text-muted">K:</span> ' . $f($totK) . ', '
                    . '<span class="text-muted">S:</span> <strong>' . $f($totS) . '</strong>'
                    . '<br><span class="text-muted" style="font-size:11px;">keluar &rarr; T:' . $f($totKt) . ' R:' . $f($totKr) . ' MV:' . $f($totKm) . '</span></div>';

                return '<div class="small">' . $lines->implode('') . '</div>';
            })
            ->rawColumns(['nama_produk', 'netto', 'harga', 'stok', 'action'])
            ->make(true);
    }

    /**
     * Search produk untuk dropdown "produk tujuan" di modal merge.
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        $exceptId = $request->get('except_id');

        $query = Product::with(['merek:id,name', 'nettos:id,product_id,netto_value,satuan'])
            ->select('id', 'merek_id', 'name', 'code')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', '%' . $q . '%')
                        ->orWhere('code', 'like', '%' . $q . '%')
                        ->orWhere('id', $q)
                        ->orWhereHas('merek', fn($m) => $m->where('name', 'like', '%' . $q . '%'));
                });
            })
            ->when($exceptId, fn($query) => $query->where('id', '!=', $exceptId))
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(function ($p) {
                $netto = $p->nettos->map(fn($n) => trim(($n->netto_value ?? '') . ' ' . ($n->satuan ?? '')))
                    ->filter()->unique()->values()->implode(' • ');

                $text = '[ID: ' . $p->id . '] ' . ($p->merek->name ?? '-')
                    . ' — ' . $p->name
                    . ($p->code ? ' (' . $p->code . ')' : '')
                    . ($netto !== '' ? ' — Netto: ' . $netto : '');

                return ['id' => $p->id, 'text' => $text];
            });

        return response()->json($query);
    }

    /**
     * Preview jumlah relasi yang akan dipindah saat merge.
     */
    public function preview($id)
    {
        $product = Product::with(['nettos.variants'])->findOrFail($id);
        $counts = $this->relationCounts($product->id);
        $nettos = $product->nettos->map(fn($n) => [
            'netto' => trim(($n->netto_value ?? '') . ' ' . ($n->satuan ?? '')) !== '' ? trim(($n->netto_value ?? '') . ' ' . ($n->satuan ?? '')) : '(tanpa netto)',
            'skus' => $n->variants->pluck('sku_code')->values(),
        ]);

        return response()->json([
            'status' => 'success',
            'product' => ['id' => $product->id, 'name' => $product->name, 'code' => $product->code],
            'nettos' => $nettos,
            'counts' => $counts,
            'total' => array_sum($counts),
        ]);
    }

    /**
     * Merge: pindahkan SEMUA relasi source -> target, lalu hapus source.
     * Netto yang nilainya sama digabung ke varian target,
     * netto yang beda dipindah utuh (varian + SKU ikut pindah).
     */
    public function merge(Request $request)
    {
        $user = auth()->user();
        if (!$user->canEdit('access_adjustment_products') && !$user->canEdit('access_products')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses merge produk.'], 403);
        }

        $data = $request->validate([
            'source_id' => 'required|integer|exists:products,id',
            'target_id' => 'required|integer|exists:products,id|different:source_id',
        ]);

        $source = Product::findOrFail($data['source_id']);
        $target = Product::findOrFail($data['target_id']);

        DB::beginTransaction();
        try {
            // 1. Petakan netto source -> netto target berdasarkan nilai (case-insensitive)
            $sourceNettos = ProductNetto::where('product_id', $source->id)->with('variants')->get();
            $targetNettos = ProductNetto::where('product_id', $target->id)->with('variants')->get();
            $targetByKey = [];
            foreach ($targetNettos as $t) {
                $targetByKey[$this->nettoKey($t->netto_value, $t->satuan)] = $t;
            }

            $variantMap = []; // old_variant_id => new_variant_id
            $nettosToDelete = [];
            foreach ($sourceNettos as $sNetto) {
                $key = $this->nettoKey($sNetto->netto_value, $sNetto->satuan);
                if (isset($targetByKey[$key])) {
                    $tVariant = $targetByKey[$key]->variants->first();
                    foreach ($sNetto->variants as $sVar) {
                        if ($tVariant) {
                            $variantMap[$sVar->id] = $tVariant->id;
                        }
                    }
                    $nettosToDelete[] = $sNetto->id;
                } else {
                    // Netto unik: pindah utuh ke target (varian + SKU ikut)
                    $sNetto->update(['product_id' => $target->id]);
                }
            }

            // 2. Repoint FK level varian (hanya yang netto-nya gabung)
            if (!empty($variantMap)) {
                $oldIds = array_keys($variantMap);
                $variantTables = [
                    'transaction_items' => 'product_variant_id',
                    'product_batches' => 'product_variant_id',
                    'goods_receipt_items' => 'product_variant_id',
                    'supplier_return_items' => 'product_variant_id',
                    'stock_movement_items' => 'product_variant_id',
                    'stock_opname_items' => 'product_variant_id',
                    'bundle_items' => 'variant_id',
                    'branch_sale_items' => 'product_variant_id',
                    'branch_stock_request_items' => 'product_variant_id',
                    'branch_return_items' => 'product_variant_id',
                    'warehouse_settlement_items' => 'product_variant_id',
                ];
                foreach ($variantMap as $oldVid => $newVid) {
                    foreach ($variantTables as $table => $col) {
                        if (Schema::hasTable($table) && Schema::hasColumn($table, $col)) {
                            DB::table($table)->where($col, $oldVid)->update([$col => $newVid]);
                        }
                    }
                }
                DB::table('product_variants')->whereIn('id', $oldIds)->delete();
                ProductNetto::whereIn('id', $nettosToDelete)->delete();
            }

            // 3. Repoint FK level produk (sisa netto yang pindah utuh ikut aman)
            $productTables = [
                'transaction_items' => 'product_id',
                'product_batches' => 'product_id',
                'purchase_order_items' => 'product_id',
                'goods_receipt_items' => 'product_id',
                'supplier_return_items' => 'product_id',
                'stock_movement_items' => 'product_id',
                'stock_opname_items' => 'product_id',
                'bundle_items' => 'product_id',
                'delivery_note_items' => 'product_id',
                'vouchers' => 'product_id',
                'affiliate_product_commissions' => 'product_id',
                'branch_sale_items' => 'product_id',
                'branch_stock_request_items' => 'product_id',
                'branch_return_items' => 'product_id',
                'warehouse_settlement_items' => 'product_id',
            ];
            $moved = [];
            foreach ($productTables as $table => $col) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $col)) {
                    $moved[$table] = DB::table($table)->where($col, $source->id)->update([$col => $target->id]);
                }
            }

            // bundle_items.bundle_id: definisi bundel milik source pindah ke target
            if (Schema::hasTable('bundle_items') && Schema::hasColumn('bundle_items', 'bundle_id')) {
                $moved['bundle_items.bundle_id'] = DB::table('bundle_items')->where('bundle_id', $source->id)->update(['bundle_id' => $target->id]);
            }

            // voucher_product pivot: hindari duplikat voucher yang sama
            if (Schema::hasTable('voucher_product')) {
                $sourceVoucherIds = DB::table('voucher_product')->where('product_id', $source->id)->pluck('voucher_id');
                $targetVoucherIds = DB::table('voucher_product')->where('product_id', $target->id)->pluck('voucher_id')->toArray();
                foreach ($sourceVoucherIds as $vid) {
                    if (in_array($vid, $targetVoucherIds)) {
                        DB::table('voucher_product')->where('product_id', $source->id)->where('voucher_id', $vid)->delete();
                    } else {
                        DB::table('voucher_product')->where('product_id', $source->id)->where('voucher_id', $vid)->update(['product_id' => $target->id]);
                    }
                }
                $moved['voucher_product'] = count($sourceVoucherIds);
            }

            // affiliate commissions: jika target sudah punya komisi affiliate yang sama, hapus source
            if (Schema::hasTable('affiliate_product_commissions')) {
                $dups = DB::table('affiliate_product_commissions as s')
                    ->join('affiliate_product_commissions as t', 's.affiliate_id', '=', 't.affiliate_id')
                    ->where('s.product_id', $source->id)
                    ->where('t.product_id', $target->id)
                    ->pluck('s.id');
                if ($dups->count()) {
                    DB::table('affiliate_product_commissions')->whereIn('id', $dups)->delete();
                }
            }

            // Foto pindah ke target (tidak hilang)
            if (Schema::hasTable('photo_product')) {
                $moved['photo_product'] = DB::table('photo_product')->where('id_product', $source->id)->update(['id_product' => $target->id]);
            }

            // 4. Hapus produk source + sinkron stok target
            $source->delete();
            Product::bulkSyncStock([$target->id]);
            if ($target->is_bundle) {
                $target->syncBundlePrice();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Produk [{$source->name}] berhasil digabung ke [{$target->name}]. Relasi tidak hilang.",
                'moved' => $moved,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Gagal merge: ' . $e->getMessage()], 500);
        }
    }

    private function nettoKey($value, $satuan): string
    {
        return strtolower(trim((string) $value)) . '|' . strtolower(trim((string) ($satuan ?? '')));
    }

    /**
     * Hitung relasi per tabel untuk preview (aman jika tabel tidak ada).
     */
    private function relationCounts(int $productId): array
    {
        $tables = [
            'transaction_items' => 'product_id',
            'product_batches' => 'product_id',
            'purchase_order_items' => 'product_id',
            'goods_receipt_items' => 'product_id',
            'supplier_return_items' => 'product_id',
            'stock_movement_items' => 'product_id',
            'stock_opname_items' => 'product_id',
            'bundle_items' => 'product_id',
            'delivery_note_items' => 'product_id',
            'vouchers' => 'product_id',
            'affiliate_product_commissions' => 'product_id',
            'branch_sale_items' => 'product_id',
            'branch_stock_request_items' => 'product_id',
            'branch_return_items' => 'product_id',
            'warehouse_settlement_items' => 'product_id',
            'photo_product' => 'id_product',
            'product_nettos' => 'product_id',
        ];
        $counts = [];
        foreach ($tables as $table => $col) {
            try {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $col)) {
                    $counts[$table] = DB::table($table)->where($col, $productId)->count();
                }
            } catch (\Exception $e) {
                // abaikan tabel yang bermasalah
            }
        }

        return $counts;
    }

    /* ================= DETAIL ADJUSTMENT PER NETTO ================= */

    /**
     * Peta sumber tiap batch: Penerimaan (GR, exact via link / dugaan via
     * kecocokan qty+tanggal), Transfer movement (exact via link / dugaan),
     * SJ Supplier, atau Input manual. Plus penanda "mirip" jika ada batch
     * se-produk+varian+qty yang lebih dulu (indikasi input ganda).
     */
    private function batchSourceMap($batches): array
    {
        $map = [];
        $bidList = $batches->pluck('id')->toArray() ?: [0];

        // Exact: GR via link
        $grRows = DB::table('product_batches as b')
            ->join('goods_receipt_items as i', 'i.id', '=', 'b.goods_receipt_item_id')
            ->join('goods_receipts as g', 'g.id', '=', 'i.goods_receipt_id')
            ->whereIn('b.id', $bidList)
            ->select('b.id as bid', 'g.id as gr_id', 'g.sj_number')->get();
        foreach ($grRows as $r) {
            $map[$r->bid] = ['kind' => 'gr', 'exact' => true, 'label' => 'Penerimaan',
                'ref' => $r->sj_number, 'url' => route('admin.purchasing.goods_receipts.show', $r->gr_id)];
        }

        // Exact: SJ Supplier yang menunjuk batch
        $sjRows = DB::table('supplier_delivery_note_items as i')
            ->join('supplier_delivery_notes as d', 'd.id', '=', 'i.supplier_delivery_note_id')
            ->whereIn('i.product_batch_id', $bidList)
            ->select('i.product_batch_id as bid', 'd.id as sdn_id', 'd.sj_number')->get();
        foreach ($sjRows as $r) {
            if (!isset($map[$r->bid])) {
                $map[$r->bid] = ['kind' => 'sj', 'exact' => true, 'label' => 'SJ Supplier',
                    'ref' => $r->sj_number, 'url' => route('admin.purchasing.supplier_delivery_notes.show', $r->sdn_id)];
            }
        }

        foreach ($batches as $b) {
            // Exact: movement via link (diisi receive() / backfill)
            if ($b->incomingMovement && !isset($map[$b->id])) {
                $map[$b->id] = ['kind' => 'movement', 'exact' => true, 'label' => 'Transfer',
                    'ref' => $b->incomingMovement->reference_number,
                    'url' => route('admin.stock_movements.show', $b->stock_movement_id)];
            }
            if (isset($map[$b->id])) {
                continue;
            }
            // Dugaan transfer: movement completed ke gudang sama, produk+varian
            // sama, no batch sumber sama, diterima di hari yang sama (toleransi
            // selisih zona waktu ±8 jam). Qty boleh beda (selisih = temuan!).
            $mv = DB::table('stock_movement_items as i')
                ->join('stock_movements as m', 'm.id', '=', 'i.stock_movement_id')
                ->join('product_batches as sb', 'sb.id', '=', 'i.product_batch_id')
                ->where('m.status', 'completed')
                ->where('m.to_warehouse_id', $b->warehouse_id)
                ->where('i.product_id', $b->product_id)
                ->when($b->product_variant_id,
                    fn($q) => $q->where('i.product_variant_id', $b->product_variant_id),
                    fn($q) => $q->whereNull('i.product_variant_id'))
                ->where('sb.batch_no', $b->batch_no)
                ->whereRaw('DATE_ADD(m.received_at, INTERVAL 7 HOUR) BETWEEN DATE_SUB(?, INTERVAL 8 HOUR) AND DATE_ADD(?, INTERVAL 8 HOUR)', [$b->created_at, $b->created_at])
                ->select('m.id', 'm.reference_number', 'i.qty as kirim')
                ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, DATE_ADD(m.received_at, INTERVAL 7 HOUR), ?))', [$b->created_at])
                ->first();
            if ($mv) {
                $map[$b->id] = ['kind' => 'movement', 'exact' => false, 'label' => 'Transfer?',
                    'ref' => $mv->reference_number . " (kirim {$mv->kirim}, terima {$b->qty})",
                    'url' => route('admin.stock_movements.show', $mv->id)];
                continue;
            }
            // Dugaan penerimaan: GR item se-produk (+varian), qty sama, ≤14 hari sebelumnya
            $gr = DB::table('goods_receipt_items as i')
                ->join('goods_receipts as g', 'g.id', '=', 'i.goods_receipt_id')
                ->where('i.product_id', $b->product_id)
                ->when($b->product_variant_id,
                    fn($q) => $q->where(function ($w) use ($b) {
                        $w->where('i.product_variant_id', $b->product_variant_id)->orWhereNull('i.product_variant_id');
                    }),
                    fn($q) => $q->whereNull('i.product_variant_id'))
                ->where('i.quantity_received', $b->qty)
                ->whereDate('g.received_date', '>=', date('Y-m-d', strtotime($b->created_at . ' -14 days')))
                ->whereDate('g.received_date', '<=', date('Y-m-d', strtotime($b->created_at)))
                ->select('g.id as gr_id', 'g.sj_number', 'g.received_date')
                ->orderByDesc('g.received_date')->first();
            if ($gr) {
                $map[$b->id] = ['kind' => 'gr', 'exact' => false, 'label' => 'Penerimaan?',
                    'ref' => $gr->sj_number . " ({$gr->received_date})",
                    'url' => route('admin.purchasing.goods_receipts.show', $gr->gr_id)];
                continue;
            }
            $map[$b->id] = ['kind' => 'manual', 'exact' => false, 'label' => 'Input manual', 'ref' => null, 'url' => null];
        }

        // Penanda mirip: batch se-produk+varian+qty yang lebih dulu (mungkin input ganda)
        foreach ($batches as $b) {
            $older = DB::table('product_batches')
                ->where('product_id', $b->product_id)
                ->where('product_variant_id', $b->product_variant_id)
                ->where('qty', $b->qty)
                ->where('id', '<', $b->id)
                ->whereNull('deleted_at')
                ->orderBy('id')->pluck('id')->toArray();
            if (!empty($older)) {
                $map[$b->id]['mirip'] = $older;
            }
        }

        return $map;
    }

    private function adjustmentGuard()
    {
        $u = auth()->user();
        if (!$u->canEdit('access_adjustment_products') && !$u->canEdit('access_products')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses.'], 403);
        }

        return null;
    }

    /**
     * Detail adjustment 1 netto: batch+stok, transaksi, movement,
     * surat jalan (customer + supplier), penerimaan, PO, return, opname.
     * Semua aksi perbaikan batch ada di halaman ini.
     */
    public function show($nettoId)
    {
        if (!auth()->user()->hasPermission('access_adjustment_menu')
            && !auth()->user()->hasPermission('access_adjustment_products')
            && !auth()->user()->hasPermission('access_products_menu')) {
            abort(403, 'Anda tidak memiliki akses Adjustment Data.');
        }

        $netto = ProductNetto::with(['product.merek', 'variants'])->findOrFail($nettoId);
        $product = $netto->product;
        $variantIds = $netto->variants->pluck('id')->toArray();
        $vidList = $variantIds ?: [0];

        // Batch milik netto ini + batch produk tanpa varian (kandidat assign)
        $batches = \App\Models\ProductBatch::with(['warehouse:id,name', 'incomingMovement:id,reference_number'])
            ->withSum('transactionItems as k_trx', 'qty')
            ->withSum('supplierReturnItems as k_ret', 'qty')
            ->withSum(['stockMovementItems as k_mv' => fn($q) => $q->whereHas('stockMovement',
                fn($m) => $m->whereIn('status', ['transit', 'completed']))], 'qty')
            ->withCount(['stockMovementItems as mv_n' => fn($q) => $q->whereHas('stockMovement',
                fn($m) => $m->whereIn('status', ['transit', 'completed']))])
            ->where(function ($q) use ($product, $vidList) {
                $q->whereIn('product_variant_id', $vidList)
                    ->orWhere(function ($w) use ($product) {
                        $w->where('product_id', $product->id)->whereNull('product_variant_id');
                    });
            })->orderBy('id')->get();
        $batchIds = $batches->pluck('id')->toArray();
        $bidList = $batchIds ?: [0];

        // Arsip (soft-deleted) milik netto/produk ini — bisa dikembalikan
        $trashedBatches = \App\Models\ProductBatch::onlyTrashed()
            ->with('warehouse:id,name')
            ->where(function ($q) use ($product, $vidList) {
                $q->whereIn('product_variant_id', $vidList)
                    ->orWhere(function ($w) use ($product) {
                        $w->where('product_id', $product->id)->whereNull('product_variant_id');
                    });
            })->orderBy('deleted_at', 'desc')->get();

        // Varian lain se-produk (dropdown assign) + gudang (dropdown batch)
        $siblingVariants = \App\Models\ProductVariant::whereIn(
            'product_netto_id',
            ProductNetto::where('product_id', $product->id)->pluck('id')
        )->with('netto:id,netto_value,satuan')->orderBy('id')->get();
        $warehouses = \App\Models\Warehouse::orderBy('name')->get(['id', 'name']);

        // Kondisi "terkait netto ini": lewat batch, lewat varian,
        // atau baris product-level tanpa batch & varian (info, perlu dibereskan)
        $linked = function ($q) use ($bidList, $vidList, $product) {
            $q->whereIn('product_batch_id', $bidList)
                ->orWhereIn('product_variant_id', $vidList)
                ->orWhere(function ($w) use ($product) {
                    $w->where('product_id', $product->id)
                        ->whereNull('product_batch_id')->whereNull('product_variant_id');
                });
        };

        $transItems = DB::table('transaction_items as ti')
            ->leftJoin('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->leftJoin('product_batches as b', 'b.id', '=', 'ti.product_batch_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 't.warehouse_id')
            ->where(function ($q) use ($bidList, $vidList, $product) {
                $q->whereIn('ti.product_batch_id', $bidList)
                    ->orWhereIn('ti.product_variant_id', $vidList)
                    ->orWhere(function ($w) use ($product) {
                        $w->where('ti.product_id', $product->id)
                            ->whereNull('ti.product_batch_id')->whereNull('ti.product_variant_id');
                    });
            })
            ->select('ti.id', 'ti.transaction_id', 'ti.product_id', 'ti.qty', 'ti.price', 'ti.subtotal', 'ti.product_batch_id', 'ti.product_variant_id',
                't.transaction_code', 't.transaction_date', 't.transaction_type', 't.source', 't.warehouse_id', 'b.batch_no', 'w.name as warehouse_name')
            ->orderByDesc('ti.id')->limit(500)->get();

        $movements = DB::table('stock_movement_items as i')
            ->join('stock_movements as m', 'm.id', '=', 'i.stock_movement_id')
            ->leftJoin('warehouses as wf', 'wf.id', '=', 'm.from_warehouse_id')
            ->leftJoin('warehouses as wt', 'wt.id', '=', 'm.to_warehouse_id')
            ->leftJoin('product_batches as b', 'b.id', '=', 'i.product_batch_id')
            ->leftJoin('products as p', 'p.id', '=', 'i.product_id')
            ->leftJoin('product_variants as v', 'v.id', '=', 'i.product_variant_id')
            ->where(function ($q) use ($bidList, $vidList, $product) {
                $q->whereIn('i.product_batch_id', $bidList)
                    ->orWhereIn('i.product_variant_id', $vidList)
                    ->orWhere(function ($w) use ($product) {
                        $w->where('i.product_id', $product->id)
                            ->whereNull('i.product_batch_id')->whereNull('i.product_variant_id');
                    });
            })
            ->select('i.id', 'i.qty', 'i.product_id', 'i.product_variant_id', 'i.product_batch_id',
                'm.id as movement_id', 'm.reference_number', 'm.status', 'm.shipped_at', 'm.created_at',
                'wf.name as from_wh', 'wt.name as to_wh', 'b.batch_no',
                'p.name as product_name', 'v.sku_code')
            ->orderByDesc('i.id')->limit(500)->get();

        $sjCustomer = DB::table('delivery_note_items as i')
            ->join('delivery_notes as d', 'd.id', '=', 'i.delivery_note_id')
            ->leftJoin('product_batches as b', 'b.id', '=', 'i.product_batch_id')
            ->where(function ($q) use ($bidList, $product) {
                $q->whereIn('i.product_batch_id', $bidList)->orWhere('i.product_id', $product->id);
            })
            ->select('i.id', 'i.qty', 'i.product_batch_id', 'd.id as dn_id', 'd.delivery_note_no', 'd.transaction_date', 'd.customer_name', 'b.batch_no')
            ->orderByDesc('i.id')->limit(200)->get();

        $sjSupplier = DB::table('supplier_delivery_note_items as i')
            ->join('supplier_delivery_notes as d', 'd.id', '=', 'i.supplier_delivery_note_id')
            ->leftJoin('product_batches as b', 'b.id', '=', 'i.product_batch_id')
            ->whereIn('i.product_batch_id', $bidList)
            ->select('i.id', 'i.qty', 'i.product_batch_id', 'd.id as sdn_id', 'd.sj_number', 'd.delivery_note_number', 'd.transaction_date', 'b.batch_no')
            ->orderByDesc('i.id')->limit(200)->get();

        $receipts = DB::table('goods_receipt_items as i')
            ->join('goods_receipts as g', 'g.id', '=', 'i.goods_receipt_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'g.supplier_id')
            ->where(function ($q) use ($vidList, $product) {
                $q->whereIn('i.product_variant_id', $vidList)->orWhere('i.product_id', $product->id);
            })
            ->select('i.quantity_ordered', 'i.quantity_received', 'i.quantity_difference', 'i.batch_no', 'i.expiry_date',
                'g.id as gr_id', 'g.sj_number', 'g.received_date', 's.name as supplier_name')
            ->orderByDesc('i.id')->limit(200)->get();

        $pos = DB::table('purchase_order_items as i')
            ->join('purchase_orders as o', 'o.id', '=', 'i.purchase_order_id')
            ->where('i.product_id', $product->id)
            ->select('o.id as po_id', 'o.po_number', 'o.po_date', 'o.status', 'i.product_name', 'i.quantity', 'i.paid_qty', 'i.unit_price')
            ->orderByDesc('i.id')->limit(200)->get();

        $returns = DB::table('supplier_return_items as i')
            ->join('supplier_returns as r', 'r.id', '=', 'i.supplier_return_id')
            ->leftJoin('product_batches as b', 'b.id', '=', 'i.product_batch_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'r.supplier_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 'r.warehouse_id')
            ->where(function ($q) use ($bidList, $vidList, $product) {
                $q->whereIn('i.product_batch_id', $bidList)
                    ->orWhereIn('i.product_variant_id', $vidList)
                    ->orWhere(function ($w) use ($product) {
                        $w->where('i.product_id', $product->id)
                            ->whereNull('i.product_batch_id')->whereNull('i.product_variant_id');
                    });
            })
            ->select('i.qty', 'i.reason', 'i.product_batch_id', 'i.product_variant_id', 'r.id as ret_id', 'r.return_number', 'r.return_date', 'r.status', 'r.warehouse_id', 'b.batch_no', 's.name as supplier_name', 'w.name as warehouse_name')
            ->orderByDesc('i.id')->limit(200)->get();

        $opnames = DB::table('stock_opname_items as i')
            ->join('stock_opnames as o', 'o.id', '=', 'i.stock_opname_id')
            ->where(function ($q) use ($vidList, $product) {
                $q->whereIn('i.product_variant_id', $vidList)->orWhere('i.product_id', $product->id);
            })
            ->select('i.system_qty', 'i.physical_qty', 'i.difference', 'o.id as op_id', 'o.reference_number', 'o.status')
            ->orderByDesc('i.id')->limit(200)->get();

        $totalM = (int) $batches->sum('qty');
        $totalK = (int) ($batches->sum('k_trx') + $batches->sum('k_ret') + $batches->sum('k_mv'));
        $totalMv = (int) $batches->sum('k_mv');
        $totalT = (int) $batches->sum('k_trx');
        $totalR = (int) $batches->sum('k_ret');

        // RR (return received): info baris return yang DITERIMA gudang batch ini
        // (dokumen return gudangnya = gudang batch, tapi dari batch lain).
        // Kolom info saja — tidak mengubah S.
        $rrByBatch = [];
        foreach ($batches as $b) {
            $rrByBatch[$b->id] = (int) $returns->filter(fn($r) =>
                (int) ($r->warehouse_id ?? 0) === (int) $b->warehouse_id
                && !empty($r->product_batch_id)
                && (int) $r->product_batch_id !== (int) $b->id
                && (int) ($r->product_variant_id ?? 0) === (int) ($b->product_variant_id ?? 0)
            )->sum('qty');
        }
        $totalRR = array_sum($rrByBatch);

        // Peta sumber tiap batch: Penerimaan (GR) / Transfer (movement) /
        // SJ Supplier / Input manual. Link exact diutamakan, dugaan sebagai fallback.
        $sourceMap = $this->batchSourceMap($batches);

        // Peta batch untuk info M/K/S per baris transaksi
        $batchMap = $batches->mapWithKeys(function ($b) use ($rrByBatch) {
            $k = (int) ($b->k_trx ?? 0) + (int) ($b->k_ret ?? 0) + (int) ($b->k_mv ?? 0);
            $rr = (int) ($rrByBatch[$b->id] ?? 0);

            return [$b->id => ['no' => $b->batch_no, 'm' => (int) $b->qty, 'k' => $k, 'rr' => $rr, 's' => (int) $b->qty - $k + $rr]];
        });

        return view('admin.adjustment.products.show')->with([
            'sb' => 'AdjustmentProduct',
            'netto' => $netto, 'product' => $product,
            'batches' => $batches, 'batchMap' => $batchMap, 'sourceMap' => $sourceMap, 'trashedBatches' => $trashedBatches,
            'siblingVariants' => $siblingVariants, 'warehouses' => $warehouses,
            'transItems' => $transItems, 'movements' => $movements,
            'sjCustomer' => $sjCustomer, 'sjSupplier' => $sjSupplier,
            'receipts' => $receipts, 'pos' => $pos, 'returns' => $returns, 'opnames' => $opnames,
            'totalM' => $totalM, 'totalK' => $totalK, 'totalS' => $totalM - $totalK + $totalRR, 'totalMv' => $totalMv,
            'totalT' => $totalT, 'totalR' => $totalR, 'totalRR' => $totalRR, 'rrByBatch' => $rrByBatch,
        ]);
    }

    /* ================= AKSI PERBAIKAN BATCH ================= */

    public function createBatch(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $request->merge(['buy_price' => $this->parsePrice($request->input('buy_price'))]);
        $d = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'batch_no' => 'required|string|max:100',
            'qty' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'expiry_date' => 'nullable|date',
            'buy_price' => 'nullable|integer|min:0',
        ]);
        $d['expiry_date'] = normalize_expiry_date($d['expiry_date'] ?? null);
        if (!empty($d['product_variant_id'])) {
            $ok = \App\Models\ProductVariant::where('id', $d['product_variant_id'])
                ->whereHas('netto', fn($q) => $q->where('product_id', $d['product_id']))->exists();
            if (!$ok) {
                return response()->json(['status' => 'error', 'message' => 'Varian bukan milik produk ini.'], 422);
            }
        }
        // Anti input ganda: kombinasi produk+varian+gudang+no batch yang sama = baris yang sama.
        // ID beda-beda hanya untuk baris yang memang beda (gudang/varian lain = tracking transfer).
        $dup = \App\Models\ProductBatch::findDuplicate(
            $d['product_id'], $d['product_variant_id'] ?? null, $d['warehouse_id'] ?? null, $d['batch_no']
        );
        if ($dup) {
            return response()->json([
                'status' => 'error',
                'message' => "No batch '{$d['batch_no']}' sudah ada (#{$dup->id}, qty {$dup->qty}, dibuat {$dup->created_at}). Tambahkan qty lewat Edit pada baris tersebut.",
                'duplicate_id' => $dup->id,
            ], 422);
        }

        $b = \App\Models\ProductBatch::create([
            'product_id' => $d['product_id'],
            'product_variant_id' => $d['product_variant_id'] ?? null,
            'batch_no' => $d['batch_no'],
            'qty' => $d['qty'],
            'warehouse_id' => $d['warehouse_id'] ?? null,
            'expiry_date' => $d['expiry_date'] ?? null,
            'buy_price' => $d['buy_price'] ?? null,
        ]);
        Product::bulkSyncStock([$d['product_id']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Batch baru berhasil ditambah (tambah stok).',
            'id' => $b->id,
        ]);
    }

    public function updateBatch(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $request->merge(['buy_price' => $this->parsePrice($request->input('buy_price'))]);
        $d = $request->validate([
            'batch_id' => 'required|exists:product_batches,id,deleted_at,NULL',
            'batch_no' => 'required|string|max:100',
            'qty' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'expiry_date' => 'nullable|date',
            'buy_price' => 'nullable|integer|min:0',
        ]);
        $d['expiry_date'] = normalize_expiry_date($d['expiry_date'] ?? null);
        $b = \App\Models\ProductBatch::findOrFail($d['batch_id']);
        $dup = \App\Models\ProductBatch::findDuplicate(
            $b->product_id, $b->product_variant_id, $d['warehouse_id'] ?? null, $d['batch_no'], $b->id
        );
        if ($dup) {
            return response()->json(['status' => 'error',
                'message' => "No batch '{$d['batch_no']}' sudah dipakai baris #{$dup->id}. Pakai Merge Batch untuk menggabungkannya."], 422);
        }
        $b->update([
            'batch_no' => $d['batch_no'],
            'qty' => $d['qty'],
            'warehouse_id' => $d['warehouse_id'] ?? null,
            'expiry_date' => $d['expiry_date'] ?? null,
            'buy_price' => $d['buy_price'] ?? $b->buy_price,
        ]);
        Product::bulkSyncStock([$b->product_id]);

        return response()->json(['status' => 'success', 'message' => 'Batch berhasil diperbarui.']);
    }

    /**
     * Pasangkan varian ke batch tanpa varian (se-produk).
     * Item yang menunjuk batch ini tapi variannya null ikut dipasangkan.
     */
    public function assignBatchVariant(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate([
            'batch_id' => 'required|exists:product_batches,id,deleted_at,NULL',
            'variant_id' => 'required|exists:product_variants,id',
        ]);
        $b = \App\Models\ProductBatch::findOrFail($d['batch_id']);
        $ok = \App\Models\ProductVariant::where('id', $d['variant_id'])
            ->whereHas('netto', fn($q) => $q->where('product_id', $b->product_id))->exists();
        if (!$ok) {
            return response()->json(['status' => 'error', 'message' => 'Varian bukan milik produk batch ini.'], 422);
        }
        DB::transaction(function () use ($b, $d) {
            $b->update(['product_variant_id' => $d['variant_id']]);
            $variantCols = [
                'transaction_items' => 'product_variant_id',
                'supplier_return_items' => 'product_variant_id',
                'stock_movement_items' => 'product_variant_id',
                'goods_receipt_items' => 'product_variant_id',
                'branch_sale_items' => 'product_variant_id',
                'branch_stock_request_items' => 'product_variant_id',
                'branch_return_items' => 'product_variant_id',
                'warehouse_settlement_items' => 'product_variant_id',
                'stock_opname_items' => 'product_variant_id',
            ];
            foreach ($variantCols as $table => $col) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, $col)
                    && Schema::hasColumn($table, 'product_batch_id')) {
                    DB::table($table)->where('product_batch_id', $b->id)->whereNull($col)->update([$col => $d['variant_id']]);
                }
            }
            Product::bulkSyncStock([$b->product_id]);
        });

        return response()->json(['status' => 'success', 'message' => 'Batch berhasil dipasangkan ke varian.']);
    }

    public function batchUsage($id)
    {
        $b = \App\Models\ProductBatch::with('warehouse:id,name')->findOrFail($id);
        $counts = [];
        foreach (['transaction_items', 'supplier_return_items', 'stock_movement_items', 'supplier_delivery_note_items', 'delivery_note_items', 'branch_sale_items'] as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'product_batch_id')) {
                $counts[$t] = DB::table($t)->where('product_batch_id', $b->id)->count();
            }
        }

        return response()->json([
            'status' => 'success',
            'batch' => ['id' => $b->id, 'batch_no' => $b->batch_no, 'qty' => $b->qty,
                'warehouse' => $b->warehouse->name ?? '-', 'variant_id' => $b->product_variant_id],
            'counts' => $counts,
        ]);
    }

    /**
     * Tabel yang menunjuk product_batch_id (dipakai cek relasi + replace).
     */
    private function batchLinkTables(): array
    {
        return ['transaction_items', 'supplier_return_items', 'stock_movement_items', 'supplier_delivery_note_items', 'delivery_note_items', 'branch_sale_items'];
    }

    private function batchUsageCounts(int $batchId): array
    {
        $used = [];
        foreach ($this->batchLinkTables() as $t) {
            if (Schema::hasTable($t) && Schema::hasColumn($t, 'product_batch_id')) {
                $c = DB::table($t)->where('product_batch_id', $batchId)->count();
                if ($c > 0) {
                    $used[$t] = $c;
                }
            }
        }

        return $used;
    }

    /**
     * Hapus batch (kasus input 2x) = SOFT DELETE (arsip, bisa dikembalikan).
     * ATURAN: cek relasi dulu — jika batch dipakai dokumen, WAJIB pilih
     * batch pengganti se-produk; semua penunjukan + qty dipindah dulu
     * sehingga tidak ada data yatim/terputus, total stok tetap sama.
     */
    public function deleteBatch(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate([
            'batch_id' => 'required|exists:product_batches,id,deleted_at,NULL',
            'target_batch_id' => 'nullable|exists:product_batches,id,deleted_at,NULL|different:batch_id',
        ]);
        $b = \App\Models\ProductBatch::findOrFail($d['batch_id']);
        $used = $this->batchUsageCounts($b->id);

        // Tanpa relasi: arsip langsung
        if (empty($used)) {
            $pid = $b->product_id;
            $no = $b->batch_no;
            $b->delete();
            Product::bulkSyncStock([$pid]);

            return response()->json(['status' => 'success', 'message' => "Batch [$no] diarsipkan (tanpa relasi). Bisa dikembalikan dari arsip."]);
        }

        // Ada relasi: pengganti wajib
        if (empty($d['target_batch_id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Batch dipakai: ' . collect($used)->map(fn($c, $t) => "$t ($c)")->implode(', ') . '. Wajib pilih batch pengganti dulu.',
                'usage' => $used,
            ], 422);
        }
        $t = \App\Models\ProductBatch::findOrFail($d['target_batch_id']);
        if ($t->product_id !== $b->product_id) {
            return response()->json(['status' => 'error', 'message' => 'Batch pengganti beda produk.'], 422);
        }
        if ($b->product_variant_id && $t->product_variant_id
            && $b->product_variant_id !== $t->product_variant_id) {
            return response()->json(['status' => 'error', 'message' => 'Batch pengganti beda varian.'], 422);
        }

        $moved = array_sum($used);
        DB::transaction(function () use ($b, $t) {
            foreach ($this->batchLinkTables() as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'product_batch_id')) {
                    DB::table($table)->where('product_batch_id', $b->id)->update(['product_batch_id' => $t->id]);
                }
            }
            // Qty ikut pindah agar total stok tidak berubah
            $t->qty = (int) $t->qty + (int) $b->qty;
            if (!$t->product_variant_id && $b->product_variant_id) {
                $t->product_variant_id = $b->product_variant_id;
            }
            $t->save();
            $b->delete(); // soft delete
            Product::bulkSyncStock([$t->product_id]);
        });

        return response()->json(['status' => 'success',
            'message' => "Relasi ($moved baris) dipindah ke [{$t->batch_no}], qty digabung, batch [{$b->batch_no}] diarsipkan. Bisa dikembalikan dari arsip."]);
    }

    /**
     * Kembalikan batch dari arsip.
     */
    public function restoreBatch(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate(['batch_id' => 'required|exists:product_batches,id']);
        $b = \App\Models\ProductBatch::onlyTrashed()->findOrFail($d['batch_id']);
        $b->restore();
        Product::bulkSyncStock([$b->product_id]);

        return response()->json(['status' => 'success', 'message' => "Batch [{$b->batch_no}] dikembalikan dari arsip."]);
    }

    /**
     * Gabung batch duplikat: qty source pindah ke target,
     * semua penunjukan batch dipindah, source dihapus.
     */
    public function mergeBatch(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate([
            'source_batch_id' => 'required|integer|exists:product_batches,id,deleted_at,NULL',
            'target_batch_id' => 'required|integer|exists:product_batches,id,deleted_at,NULL|different:source_batch_id',
        ]);
        $s = \App\Models\ProductBatch::findOrFail($d['source_batch_id']);
        $t = \App\Models\ProductBatch::findOrFail($d['target_batch_id']);
        if ($s->product_id !== $t->product_id) {
            return response()->json(['status' => 'error', 'message' => 'Batch beda produk, tidak bisa digabung.'], 422);
        }
        if ($s->product_variant_id && $t->product_variant_id
            && $s->product_variant_id !== $t->product_variant_id) {
            return response()->json(['status' => 'error', 'message' => 'Batch beda varian. Pasangkan variannya dulu atau pilih target se-varian.'], 422);
        }
        DB::transaction(function () use ($s, $t) {
            $t->qty = (int) $t->qty + (int) $s->qty;
            if (!$t->product_variant_id && $s->product_variant_id) {
                $t->product_variant_id = $s->product_variant_id;
            }
            $t->save();
            foreach ($this->batchLinkTables() as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'product_batch_id')) {
                    DB::table($table)->where('product_batch_id', $s->id)->update(['product_batch_id' => $t->id]);
                }
            }
            $s->delete();
            Product::bulkSyncStock([$t->product_id]);
        });

        return response()->json(['status' => 'success', 'message' => "Batch [{$s->batch_no}] digabung ke [{$t->batch_no}]. Qty masuk dijumlahkan."]);
    }

    /* ================= HAPUS BARIS TRANSAKSI ================= */

    /**
     * Hapus 1 baris transaction_items (kasus input 2x) + hitung ulang
     * total_amount induk (rumus sama seperti TransactionController@update).
     * DITOLAK jika transaksi sudah paid/credit atau punya pembayaran.
     */
    /**
     * Edit baris transaksi: isi/ubah gudang (di header transaksi) + varian (di baris).
     * Aturan aman: mengisi yang NULL selalu boleh; MENGUBAH nilai lama hanya
     * jika transaksi belum paid/credit.
     */
    public function updateTransactionItem(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate([
            'item_id' => 'required|exists:transaction_items,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);
        $item = \App\Models\TransactionItem::findOrFail($d['item_id']);
        $trx = \App\Models\Transaction::findOrFail($item->transaction_id);
        $locked = in_array($trx->payment_status, ['paid', 'credit']);

        if (array_key_exists('variant_id', $d)) {
            if ($d['variant_id']) {
                $ok = \App\Models\ProductVariant::where('id', $d['variant_id'])
                    ->whereHas('netto', fn($q) => $q->where('product_id', $item->product_id))->exists();
                if (!$ok) {
                    return response()->json(['status' => 'error', 'message' => 'Varian bukan milik produk baris ini.'], 422);
                }
            }
            if ($item->product_variant_id && (int) $item->product_variant_id !== (int) ($d['variant_id'] ?? 0) && $locked) {
                return response()->json(['status' => 'error', 'message' => 'Varian lama tidak bisa diubah karena transaksi sudah ' . $trx->payment_status . '.'], 422);
            }
            $item->product_variant_id = $d['variant_id'] ?: null;
        }

        $whChanged = false;
        if (array_key_exists('warehouse_id', $d) && (int) ($trx->warehouse_id ?? 0) !== (int) ($d['warehouse_id'] ?? 0)) {
            if ($trx->warehouse_id && $locked) {
                return response()->json(['status' => 'error', 'message' => 'Gudang tidak bisa diubah karena transaksi sudah ' . $trx->payment_status . '.'], 422);
            }
            $trx->warehouse_id = $d['warehouse_id'] ?: null;
            $trx->save();
            $whChanged = true;
        }

        $item->save();
        Product::bulkSyncStock([$item->product_id]);

        $sibs = \App\Models\TransactionItem::where('transaction_id', $trx->id)->where('id', '!=', $item->id)->count();
        $msg = "Baris #{$item->id} diperbarui."
            . ($whChanged && $sibs > 0 ? " Gudang header ikut berubah (transaksi punya {$sibs} baris lain)." : '');

        return response()->json(['status' => 'success', 'message' => $msg]);
    }

    public function deleteTransactionItem(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate(['item_id' => 'required|exists:transaction_items,id']);
        $item = \App\Models\TransactionItem::findOrFail($d['item_id']);
        $trx = \App\Models\Transaction::findOrFail($item->transaction_id);

        if (in_array($trx->payment_status, ['paid', 'credit'])) {
            return response()->json(['status' => 'error', 'message' => 'Baris tidak bisa dihapus karena transaksi sudah ' . $trx->payment_status . '. Batalkan/void dari modul Transaksi.'], 422);
        }
        if (\App\Models\TransactionPayment::where('transaction_id', $trx->id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Baris tidak bisa dihapus karena transaksi sudah punya pembayaran. Koreksi via modul Transaksi/Finance.'], 422);
        }

        DB::transaction(function () use ($item, $trx) {
            // Ikut hapus anak bundle (parent_item_id) agar tidak yatim
            $childIds = \App\Models\TransactionItem::where('parent_item_id', $item->id)->pluck('id')->toArray();
            if (!empty($childIds)) {
                \App\Models\TransactionItem::whereIn('id', $childIds)->delete();
            }
            $productId = $item->product_id;
            $item->delete();

            // Hitung ulang total induk (cerminkan update(): subtotal + pajak − diskon + biaya lain)
            $remaining = (float) \App\Models\TransactionItem::where('transaction_id', $trx->id)->sum('subtotal');
            $fees = 0;
            foreach ((array) ($trx->other_fees ?? []) as $fee) {
                $fees += (float) ($fee['amount'] ?? 0);
            }
            $trx->update(['total_amount' => ($remaining + (float) $trx->tax_amount) - (float) $trx->discount + $fees]);

            Product::bulkSyncStock([$productId]);
        });

        $left = \App\Models\TransactionItem::where('transaction_id', $trx->id)->count();

        return response()->json(['status' => 'success',
            'message' => "Baris dihapus. Total transaksi {$trx->transaction_code} dihitung ulang. Sisa {$left} baris."]);
    }

        /**
     * Buatkan netto + varian untuk produk (terutama yang belum punya).
     * SKU & nama varian otomatis mengikuti konvensi modul Produk.
     */
    public function createNettoVariant(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate([
            'product_id' => 'required|exists:products,id',
            'netto' => 'required|string|max:50',
            'satuan' => 'nullable|string|max:20',
            'price' => 'nullable|integer|min:0',
        ]);
        $product = Product::with(['merek', 'category'])->findOrFail($d['product_id']);

        $netto = ProductNetto::firstOrCreate(
            ['product_id' => $product->id, 'netto_value' => trim($d['netto'])],
            ['satuan' => $d['satuan'] ?? null]
        );
        if (isset($d['satuan'])) {
            $netto->update(['satuan' => $d['satuan']]);
        }
        if ($netto->variants()->exists()) {
            return response()->json(['status' => 'error',
                'message' => "Netto [{$netto->netto_value}] produk ini sudah punya varian ({$netto->variants()->first()->sku_code})."], 422);
        }

        $merekCode = $product->merek->code ?? 'UNK';
        $categoryCode = $product->category->code ?? 'UNK';
        $productCode = preg_replace('/\s+/', '', strtoupper(trim($product->code ?: 'UNK')));
        $nettoPart = preg_replace('/[^0-9]/', '', $d['netto']);
        $generatedSku = strtoupper("{$merekCode}-{$categoryCode}-{$productCode}-{$nettoPart}");
        $finalSku = $generatedSku;
        $counter = 1;
        while (\App\Models\ProductVariant::whereRaw('LOWER(sku_code) = ?', [strtolower($finalSku)])->exists()) {
            $finalSku = $generatedSku . '-' . $counter++;
        }

        $variant = \App\Models\ProductVariant::create([
            'product_netto_id' => $netto->id,
            'sku_code' => $finalSku,
            'variant_name' => trim($product->name . ' ' . $d['netto'] . ' ' . ($d['satuan'] ?? '')),
            'price' => $d['price'] ?? 0,
            'price_real' => 0,
            'ray_store' => $d['price'] ?? 0,
            'stock' => 0,
        ]);

        return response()->json(['status' => 'success',
            'message' => "Netto + varian [{$variant->sku_code}] berhasil dibuat untuk [{$product->name}]."]);
    }

    /**
     * Parse harga format Indonesia ("48.729", "48729,00") ke integer.
     * Titik = pemisah ribuan (dibuang), koma = desimal (dibuang).
     */
    private function parsePrice($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $v = str_replace('.', '', (string) $value);
        $v = preg_replace('/,.*$/', '', $v);
        $v = preg_replace('/[^0-9]/', '', $v);

        return $v === '' ? null : (int) $v;
    }

    /* ================= LENGKAPI VARIAN KOSONG ================= */    /**
     * Kolom varian per baris dokumen (kecuali bundle).
     */
    private function variantLinkTables(): array
    {
        return [
            'transaction_items' => 'product_variant_id',
            'supplier_return_items' => 'product_variant_id',
            'stock_movement_items' => 'product_variant_id',
            'goods_receipt_items' => 'product_variant_id',
            'branch_sale_items' => 'product_variant_id',
            'branch_stock_request_items' => 'product_variant_id',
            'branch_return_items' => 'product_variant_id',
            'warehouse_settlement_items' => 'product_variant_id',
            'stock_opname_items' => 'product_variant_id',
        ];
    }

    /**
     * Baris dokumen yang variannya kosong padahal batch-nya jelas bervarian
     * (kasus netto 198: ti menunjuk batch 775/701 bervarian 189 tapi
     * product_variant_id-nya NULL). Isi otomatis dari batch.
     */
    private function emptyVariantPlan(array $batchIds): array
    {
        $plan = [];
        foreach ($this->variantLinkTables() as $table => $col) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $col) || !Schema::hasColumn($table, 'product_batch_id')) {
                continue;
            }
            $c = DB::table($table . ' as i')
                ->join('product_batches as b', 'b.id', '=', 'i.product_batch_id')
                ->whereIn('i.product_batch_id', $batchIds)
                ->whereNull('i.' . $col)
                ->whereNotNull('b.product_variant_id')
                ->count();
            if ($c > 0) {
                $plan[$table] = $c;
            }
        }

        return $plan;
    }

    private function nettoBatchIds(ProductNetto $netto): array
    {
        $variantIds = $netto->variants->pluck('id')->toArray() ?: [0];
        $ids = \App\Models\ProductBatch::where(function ($q) use ($netto, $variantIds) {
            $q->whereIn('product_variant_id', $variantIds)
                ->orWhere(function ($w) use ($netto) {
                    $w->where('product_id', $netto->product_id)->whereNull('product_variant_id');
                });
        })->pluck('id')->toArray();

        return $ids ?: [0];
    }

    public function fillEmptyVariantsPreview($nettoId)
    {
        $netto = ProductNetto::with('variants')->findOrFail($nettoId);
        $plan = $this->emptyVariantPlan($this->nettoBatchIds($netto));

        return response()->json(['status' => 'success', 'plan' => $plan, 'total' => array_sum($plan)]);
    }

    public function fillEmptyVariants(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate(['netto_id' => 'required|exists:product_nettos,id']);
        $netto = ProductNetto::with('variants')->findOrFail($d['netto_id']);
        $batchIds = $this->nettoBatchIds($netto);
        $plan = $this->emptyVariantPlan($batchIds);
        if (empty($plan)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada baris bervarian-kosong.'], 422);
        }
        DB::transaction(function () use ($batchIds, $netto) {
            foreach ($this->variantLinkTables() as $table => $col) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, $col) || !Schema::hasColumn($table, 'product_batch_id')) {
                    continue;
                }
                DB::table($table . ' as i')
                    ->join('product_batches as b', 'b.id', '=', 'i.product_batch_id')
                    ->whereIn('i.product_batch_id', $batchIds)
                    ->whereNull('i.' . $col)
                    ->whereNotNull('b.product_variant_id')
                    ->update(['i.' . $col => DB::raw('b.product_variant_id')]);
            }
            Product::bulkSyncStock([$netto->product_id]);
        });

        return response()->json(['status' => 'success',
            'message' => array_sum($plan) . ' baris dilengkapi variannya dari batch (' . collect($plan)->map(fn($c, $t) => "$t: $c")->implode(', ') . ').']);
    }

    /* ================= SYNC BATCH MOVEMENT ================= */

    /**
     * Data untuk modal Sync: item movement KHUSUS produk halaman ini
     * (satu sync boleh banyak batch, asal sesama produk yang sama)
     * + kandidat batch sumber per item (se-produk, gudang asal).
     */
    public function movementSyncData(Request $request, $movementId)
    {
        $m = \App\Models\StockMovement::with(['items.product:id,name', 'items.variant:id,sku_code', 'items.batch:id,batch_no,qty,warehouse_id,product_variant_id'])
            ->findOrFail($movementId);
        $onlyProduct = (int) $request->get('product_id');
        $items = [];
        foreach ($m->items as $it) {
            if ($onlyProduct && (int) $it->product_id !== $onlyProduct) {
                continue;
            }
            $cands = \App\Models\ProductBatch::with('variant:id,sku_code')
                ->where('product_id', $it->product_id)
                ->where(function ($q) use ($m) {
                    $q->where('warehouse_id', $m->from_warehouse_id)->orWhereNull('warehouse_id');
                })
                ->orderBy('id')
                ->get(['id', 'batch_no', 'qty', 'warehouse_id', 'product_variant_id'])
                ->map(fn($b) => [
                    'id' => $b->id,
                    'text' => "{$b->batch_no} (#{$b->id})" . ($b->variant ? " {$b->variant->sku_code}" : ' tanpa varian') . " M:{$b->qty}",
                ])->values();
            $items[] = [
                'item_id' => $it->id,
                'product' => ($it->product->name ?? '-') . " (#{$it->product_id})",
                'variant' => $it->variant->sku_code ?? ($it->product_variant_id ? "#{$it->product_variant_id}" : 'tanpa varian'),
                'qty' => (int) $it->qty,
                'batch_id' => $it->product_batch_id,
                'batch_no' => $it->batch->batch_no ?? null,
                'candidates' => $cands,
            ];
        }

        return response()->json(['status' => 'success',
            'movement' => ['id' => $m->id, 'ref' => $m->reference_number, 'status' => $m->status,
                'from' => $m->fromWarehouse->name ?? '-', 'to' => $m->toWarehouse->name ?? '-'],
            'items' => $items]);
    }

    /**
     * Sync: tetapkan batch sumber per item movement sekaligus.
     * Hanya untuk 1 produk (produk halaman ini); boleh banyak batch.
     * Keluar tercatat di batch terpilih (kolom Terkirim MV ikut berubah).
     */
    public function movementSync(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate([
            'movement_id' => 'required|exists:stock_movements,id',
            'product_id' => 'required|exists:products,id',
            'links' => 'required|array|min:1',
            'links.*.item_id' => 'required|exists:stock_movement_items,id',
            'links.*.batch_id' => 'required|exists:product_batches,id,deleted_at,NULL',
        ]);
        $m = \App\Models\StockMovement::findOrFail($d['movement_id']);
        DB::transaction(function () use ($m, $d) {
            foreach ($d['links'] as $link) {
                $it = \App\Models\StockMovementItem::where('id', $link['item_id'])
                    ->where('stock_movement_id', $m->id)->firstOrFail();
                if ((int) $it->product_id !== (int) $d['product_id']) {
                    throw new \Exception("Item #{$it->id} bukan produk halaman ini.");
                }
                $b = \App\Models\ProductBatch::findOrFail($link['batch_id']);
                if ((int) $b->product_id !== (int) $it->product_id) {
                    throw new \Exception("Item #{$it->id}: batch [{$b->batch_no}] beda produk.");
                }
                if ($b->warehouse_id && (int) $b->warehouse_id !== (int) $m->from_warehouse_id) {
                    throw new \Exception("Item #{$it->id}: batch [{$b->batch_no}] bukan milik gudang asal.");
                }
                if ($it->product_variant_id && $b->product_variant_id
                    && (int) $it->product_variant_id !== (int) $b->product_variant_id) {
                    throw new \Exception("Item #{$it->id}: batch [{$b->batch_no}] beda varian.");
                }
                $it->update([
                    'product_batch_id' => $b->id,
                    'product_variant_id' => $it->product_variant_id ?: $b->product_variant_id,
                ]);
            }
        });

        return response()->json(['status' => 'success',
            'message' => count($d['links']) . " item movement [{$m->reference_number}] tertaut ke batch sumber."]);
    }

    /* ================= REALOKASI FIFO ================= */    /**
     * Susun rencana realokasi: geser baris keluar utuh (FIFO, batch tertua
     * diisi dulu) agar tidak ada batch minus selama total varian mencukupi.
     */
    private function buildRealokasiMoves(ProductNetto $netto): array
    {
        $variantIds = $netto->variants->pluck('id')->toArray();
        if (empty($variantIds)) {
            return ['moves' => [], 'unresolved' => [], 'batches' => [], 'note' => 'Netto belum punya varian.'];
        }
        $batches = \App\Models\ProductBatch::whereIn('product_variant_id', $variantIds)
            ->orderBy('created_at')->orderBy('id')->get(['id', 'batch_no', 'qty', 'created_at']);
        if ($batches->count() < 2) {
            return ['moves' => [], 'unresolved' => [], 'batches' => $batches, 'note' => 'Butuh minimal 2 batch bervarian untuk realokasi.'];
        }

        $labels = $batches->pluck('batch_no', 'id');
        $spare = [];
        $itemsByBatch = [];
        foreach ($batches as $b) {
            $k = (int) DB::table('transaction_items')->where('product_batch_id', $b->id)->sum('qty')
                + (int) DB::table('supplier_return_items')->where('product_batch_id', $b->id)->sum('qty')
                + (int) DB::table('stock_movement_items as i')->join('stock_movements as m', 'm.id', '=', 'i.stock_movement_id')
                    ->where('i.product_batch_id', $b->id)->whereIn('m.status', ['transit', 'completed'])->sum('i.qty');
            $spare[$b->id] = (int) $b->qty - $k;
            $tis = DB::table('transaction_items as ti')->leftJoin('transactions as t', 't.id', '=', 'ti.transaction_id')
                ->where('ti.product_batch_id', $b->id)
                ->select('ti.id', 'ti.qty', DB::raw('COALESCE(t.transaction_date, t.created_at) as dt'), DB::raw("'ti' as kind"))->get();
            $sris = DB::table('supplier_return_items as i')->leftJoin('supplier_returns as r', 'r.id', '=', 'i.supplier_return_id')
                ->where('i.product_batch_id', $b->id)
                ->select('i.id', 'i.qty', DB::raw('COALESCE(r.return_date, r.created_at) as dt'), DB::raw("'sri' as kind"))->get();
            $itemsByBatch[$b->id] = $tis->concat($sris)
                ->sort(function ($a, $b2) {
                    return [$b2->dt, $b2->id] <=> [$a->dt, $a->id];
                })->values()->all();
        }

        $order = $batches->pluck('id')->toArray();
        $moves = [];
        $changed = true;
        $guard = 0;
        while ($changed && $guard++ < 200) {
            $changed = false;
            $neg = array_filter($spare, fn($s) => $s < 0);
            if (empty($neg)) {
                break;
            }
            asort($neg);
            foreach ($neg as $bid => $s) {
                foreach ($itemsByBatch[$bid] as $idx => $it) {
                    $target = null;
                    foreach ($order as $cand) {
                        if ($cand === $bid) {
                            continue;
                        }
                        if ($spare[$cand] >= $it->qty) {
                            $target = $cand;
                            break;
                        }
                    }
                    if ($target === null) {
                        continue;
                    }
                    $moves[] = [
                        'kind' => $it->kind, 'item_id' => $it->id, 'qty' => (int) $it->qty,
                        'from' => $bid, 'from_no' => $labels[$bid] ?? ('#' . $bid),
                        'to' => $target, 'to_no' => $labels[$target] ?? ('#' . $target),
                    ];
                    $spare[$bid] += $it->qty;
                    $spare[$target] -= $it->qty;
                    unset($itemsByBatch[$bid][$idx]);
                    $changed = true;
                    break;
                }
                if ($changed) {
                    break;
                }
            }
        }

        $unresolved = [];
        foreach ($spare as $bid => $s) {
            if ($s < 0) {
                $unresolved[] = ['batch_id' => $bid, 'batch_no' => $labels[$bid] ?? ('#' . $bid), 'sisa' => $s];
            }
        }

        return ['moves' => $moves, 'unresolved' => $unresolved, 'batches' => $batches,
            'note' => empty($moves) && empty($unresolved) ? 'Semua batch sudah sehat, tidak perlu realokasi.' : null];
    }

    public function realokasiPreview($nettoId)
    {
        $netto = ProductNetto::with('variants')->findOrFail($nettoId);
        $plan = $this->buildRealokasiMoves($netto);

        return response()->json(['status' => 'success'] + $plan);
    }

    public function realokasiExecute(Request $request)
    {
        if ($g = $this->adjustmentGuard()) {
            return $g;
        }
        $d = $request->validate(['netto_id' => 'required|exists:product_nettos,id']);
        $netto = ProductNetto::with('variants')->findOrFail($d['netto_id']);
        $plan = $this->buildRealokasiMoves($netto);
        if (empty($plan['moves'])) {
            return response()->json(['status' => 'error', 'message' => $plan['note'] ?? 'Tidak ada yang bisa direalokasi.'], 422);
        }
        DB::transaction(function () use ($plan) {
            foreach ($plan['moves'] as $m) {
                $table = $m['kind'] === 'sri' ? 'supplier_return_items' : 'transaction_items';
                DB::table($table)->where('id', $m['item_id'])->update(['product_batch_id' => $m['to']]);
            }
            Product::bulkSyncStock([$netto->product_id]);
        });

        $msg = count($plan['moves']) . ' baris keluar dipindah (FIFO).'
            . (!empty($plan['unresolved']) ? ' Sisa ' . count($plan['unresolved']) . ' batch masih minus (qty baris terlalu besar / stok total kurang).' : ' Semua batch sehat.');

        return response()->json(['status' => 'success', 'message' => $msg]);
    }
}
