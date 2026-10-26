@extends('master')
@section('title', 'Adjustment Detail Netto')
@section('content')
@php
    $canEdit = auth()->user()->canEdit('access_adjustment_products') || auth()->user()->canEdit('access_products');
    $nettoLabel = trim(($netto->netto_value ?? '') . ' ' . ($netto->satuan ?? ''));
    $fmt = fn($v) => number_format((float) ($v ?? 0), 0, ',', '.');
@endphp
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Adjustment Netto</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.adjustment.products.index') }}">Adjustment List Produk</a></div>
                    <div class="breadcrumb-item">Detail Netto</div>
                </div>
            </div>

            <div class="section-body">
                {{-- Identitas --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-1">{{ $product->merek->name ?? '-' }} — {{ $product->name }}</h5>
                        <div class="text-muted small mb-2">
                            Kode: {{ $product->code ?? '-' }} | ID Produk: {{ $product->id }} |
                            Netto: <strong>{{ $nettoLabel !== '' ? $nettoLabel : '(tanpa netto)' }}</strong> (ID Netto: {{ $netto->id }}) |
                            SKU: {{ $netto->variants->pluck('sku_code')->filter()->implode(', ') ?: '-' }}
                        </div>
                        <div class="d-flex flex-wrap" style="gap: 8px;">
                            <span class="badge badge-primary p-2">M (masuk): {{ $fmt($totalM) }}</span>
                            <span class="badge badge-secondary p-2">K Transaksi: {{ $fmt($totalT) }}</span>
                            @if($totalR > 0)<span class="badge badge-warning p-2">K Return: {{ $fmt($totalR) }}</span>@endif
                            @if($totalRR > 0)<span class="badge badge-success p-2">RR Diterima: {{ $fmt($totalRR) }}</span>@endif
                            @if($totalMv > 0)<span class="badge badge-info p-2">K Movement: {{ $fmt($totalMv) }}</span>@endif
                            <span class="badge p-2 {{ $totalS < 0 ? 'badge-danger' : 'badge-success' }}">S (sisa): {{ $fmt($totalS) }}</span>
                        </div>
                        <div class="mt-3 d-flex flex-wrap" style="gap: 8px;">
                            @if($canEdit)
                            <button class="btn btn-sm btn-info" id="btn-realokasi">Realokasi FIFO</button>
                            <button class="btn btn-sm btn-warning" id="btn-fill-variant">Lengkapi Varian Kosong</button>
                            <button class="btn btn-sm btn-success" id="btn-add-batch">Tambah Batch (Stok Masuk)</button>
                            @endif
                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-light">Halaman Produk</a>
                            <a href="{{ route('admin.products.pricing') }}" class="btn btn-sm btn-light">Halaman Pricing</a>
                        </div>
                    </div>
                </div>

                {{-- Batch & Stok + aksi perbaikan --}}
                <div class="card mb-3">
                    <div class="card-header"><h4>Batch & Stok ({{ $batches->count() }} batch)</h4></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>No Batch</th><th>Gudang</th><th>Kadaluarsa (Bln/Thn)</th>
                                        <th class="text-right">M</th><th class="text-right">T</th><th class="text-right">R</th><th class="text-right">RR</th><th class="text-right">MV</th><th class="text-right">S</th>
                                        <th>Sumber</th>
                                        @if($canEdit)<th width="190px">Perbaikan</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $b)
                                    @php
                                        $kt = (int) ($b->k_trx ?? 0);
                                        $kr = (int) ($b->k_ret ?? 0);
                                        $km = (int) ($b->k_mv ?? 0);
                                        $k = $kt + $kr + $km;
                                        $rr = (int) ($rrByBatch[$b->id] ?? 0);
                                        $s = (int) $b->qty - $k + $rr;
                                    @endphp
                                    <tr class="{{ $s < 0 ? 'table-danger' : '' }}">
                                        <td><strong>{{ $b->batch_no }}</strong><br><small class="text-muted">#{{ $b->id }}</small></td>
                                        <td>{{ $b->warehouse->name ?? '-' }}</td>
                                        <td>{{ optional($b->expiry_date)->format('M Y') ?? '-' }}</td>
                                        <td class="text-right">{{ $fmt($b->qty) }}</td>
                                        <td class="text-right">{{ $fmt($kt) }}</td>
                                        <td class="text-right">{{ $kr > 0 ? $fmt($kr) : '-' }}</td>
                                        <td class="text-right">{{ ($rrByBatch[$b->id] ?? 0) > 0 ? $fmt($rrByBatch[$b->id]) : '-' }}</td>
                                        <td class="text-right"><small>{{ $km > 0 ? $fmt($km) : '-' }}</small></td>
                                        <td class="text-right"><strong>{{ $fmt($s) }}</strong></td>
                                        <td>
                                            @php $src = $sourceMap[$b->id] ?? null; @endphp
                                            @if($src && $src['kind'] === 'gr')
                                                <span class="badge badge-info">{{ $src['label'] }}{{ $src['exact'] ? '' : '?' }}</span><br>
                                                <a href="{{ $src['url'] }}"><small>{{ $src['ref'] }}</small></a>
                                            @elseif($src && $src['kind'] === 'movement')
                                                <span class="badge badge-primary">{{ $src['label'] }}{{ $src['exact'] ? '' : '?' }}</span><br>
                                                <a href="{{ $src['url'] }}"><small>{{ $src['ref'] }}</small></a>
                                                @if(isset($src['kirim']) && (int) $src['kirim'] !== (int) $b->qty)
                                                    <br><span class="badge badge-warning">
                                                        @if($src['exact'])
                                                            M beda; seharusnya {{ $fmt($src['kirim']) }}
                                                        @else
                                                            Kandidat kirim {{ $fmt($src['kirim']) }}; verifikasi sebelum koreksi
                                                        @endif
                                                    </span>
                                                @endif
                                            @elseif($src && $src['kind'] === 'sj')
                                                <span class="badge badge-secondary">{{ $src['label'] }}</span><br>
                                                <a href="{{ $src['url'] }}"><small>{{ $src['ref'] }}</small></a>
                                            @else
                                                <span class="text-muted small">Input manual</span>
                                            @endif
                                            @if($src && !empty($src['mirip']))
                                                <br><small class="text-danger">mirip #{{ implode(', #', $src['mirip']) }}</small>
                                            @endif
                                        </td>
                                        @if($canEdit)
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-light btn-edit-batch"
                                                    data-id="{{ $b->id }}" data-no="{{ $b->batch_no }}" data-qty="{{ $b->qty }}"
                                                    data-wh="{{ $b->warehouse_id }}" data-exp="{{ optional($b->expiry_date)->format('Y-m') }}"
                                                    data-buy="{{ (int) $b->buy_price }}">Edit</button>
                                                <button class="btn btn-sm btn-light btn-merge-batch" data-id="{{ $b->id }}" data-no="{{ $b->batch_no }}">Merge</button>
                                                @if(!$b->product_variant_id)
                                                <button class="btn btn-sm btn-warning btn-assign-batch" data-id="{{ $b->id }}" data-no="{{ $b->batch_no }}">Varian</button>
                                                @endif
                                                @if($src && $src['kind'] === 'movement')
                                                <button class="btn btn-sm btn-outline-info btn-link-incoming-mv" data-id="{{ $b->id }}">Link MV</button>
                                                @endif
                                                <button class="btn btn-sm btn-danger btn-delete-batch" data-id="{{ $b->id }}" data-no="{{ $b->batch_no }}">Hapus</button>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr><td colspan="10" class="text-center text-muted">Belum ada batch untuk netto ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Arsip batch (soft delete) --}}
                @if($trashedBatches->count())
                <div class="card mb-3 border-warning">
                    <div class="card-header"><h4>Arsip Batch ({{ $trashedBatches->count() }}) — terhapus, bisa dikembalikan</h4></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>No Batch</th><th>Gudang</th><th class="text-right">Qty</th><th>Dihapus</th>@if($canEdit)<th></th>@endif</tr></thead>
                                <tbody>
                                    @foreach($trashedBatches as $tb)
                                    <tr class="table-warning">
                                        <td><strong>{{ $tb->batch_no }}</strong><br><small class="text-muted">#{{ $tb->id }}</small></td>
                                        <td>{{ $tb->warehouse->name ?? '-' }}</td>
                                        <td class="text-right">{{ number_format((int) $tb->qty, 0, ',', '.') }}</td>
                                        <td><small>{{ $tb->deleted_at }}</small></td>
                                        @if($canEdit)
                                        <td><button class="btn btn-sm btn-success btn-restore-batch" data-id="{{ $tb->id }}" data-no="{{ $tb->batch_no }}">Kembalikan</button></td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Transaksi penjualan --}}
                <div class="card mb-3">
                    <div class="card-header"><h4>Transaksi Terkait ({{ $transItems->count() }})</h4></div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped table-sm mb-0">
                                <thead><tr><th>Tanggal</th><th>Kode</th><th>Tipe</th><th>Gudang</th><th class="text-right">Qty</th><th class="text-right">Harga</th><th>Batch (sumber ambil)</th>@if($canEdit)<th>Aksi</th>@endif</tr></thead>
                                <tbody>
                                    @forelse($transItems as $t)
                                    <tr>
                                        <td>{{ $t->transaction_date ?? '-' }}</td>
                                        <td><a href="{{ route('admin.transactions.show', $t->transaction_id) }}">{{ $t->transaction_code ?? ('#' . $t->id) }}</a></td>
                                        <td><small>{{ $t->transaction_type ?? '-' }} / {{ $t->source ?? '-' }}</small></td>
                                        <td><small>{{ $t->warehouse_name ?? '-' }}</small></td>
                                        <td class="text-right">{{ $fmt($t->qty) }}</td>
                                        <td class="text-right">{{ $fmt($t->price) }}</td>
                                        <td>
                                            @if($t->product_batch_id)
                                                @php $bm = $batchMap->get($t->product_batch_id); @endphp
                                                <small><strong>{{ $bm['no'] ?? $t->batch_no ?? ('#' . $t->product_batch_id) }} (#{{ $t->product_batch_id }})</strong>
                                                @if($bm)<br><span class="text-muted">M: {{ $fmt($bm['m']) }} K: {{ $fmt($bm['k']) }} S: {{ $fmt($bm['s']) }}</span>
                                                @else<br><span class="text-muted">di luar netto ini</span>@endif</small>
                                            @else<span class="badge badge-warning">baris tanpa batch</span>@endif
                                            @if(!$t->product_variant_id) <span class="badge badge-warning">baris tanpa varian</span>@endif
                                        </td>
                                        @if($canEdit)
                                        <td>
                                            <button class="btn btn-sm btn-light btn-edit-ti" data-id="{{ $t->id }}" data-code="{{ $t->transaction_code ?? ('#' . $t->id) }}" data-wh="{{ $t->warehouse_id }}" data-var="{{ $t->product_variant_id }}" data-pid="{{ $t->product_id }}">Edit</button>
                                            <button class="btn btn-sm btn-danger btn-del-ti" data-id="{{ $t->id }}" data-qty="{{ $t->qty }}" data-sub="{{ $t->subtotal }}" data-code="{{ $t->transaction_code ?? ('#' . $t->id) }}">Hapus</button>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted">Tidak ada transaksi.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Movement --}}
                <div class="card mb-3">
                    <div class="card-header"><h4>Movement Stok ({{ $movements->count() }})</h4></div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-striped table-sm mb-0">
                                <thead><tr><th>Referensi</th><th>Dari → Ke</th><th class="text-right">Qty</th><th>Batch</th><th>Status</th><th>Tanggal</th>@if($canEdit)<th>Aksi</th>@endif</tr></thead>
                                <tbody>
                                    @forelse($movements as $m)
                                    <tr>
                                        <td><a href="{{ route('admin.stock_movements.show', $m->movement_id) }}"><small>{{ $m->reference_number }}</small></a><br><small class="text-muted">{{ $m->product_name ?? '' }}</small></td>
                                        <td><small>{{ $m->from_wh ?? '-' }} → {{ $m->to_wh ?? '-' }}</small></td>
                                        <td class="text-right">{{ $fmt($m->qty) }}</td>
                                        <td>@if($m->batch_no)<small>{{ $m->batch_no }} (#{{ $m->product_batch_id }})</small>@else<span class="badge badge-warning">baris tanpa batch</span>@endif</td>
                                        <td><small>{{ $m->status }}</small></td>
                                        <td><small>{{ $m->shipped_at ?? $m->created_at }}</small></td>
                                        @if($canEdit)
                                        <td><button class="btn btn-sm btn-info btn-sync-mv" data-id="{{ $m->movement_id }}" data-ref="{{ $m->reference_number }}">Sync</button></td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="text-center text-muted">Tidak ada movement.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Surat jalan customer --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h4>Surat Jalan Customer ({{ $sjCustomer->count() }})</h4></div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead><tr><th>No SJ</th><th>Tanggal</th><th>Customer</th><th class="text-right">Qty</th><th>Batch</th></tr></thead>
                                        <tbody>
                                            @forelse($sjCustomer as $s)
                                            <tr>
                                                <td><a href="{{ route('admin.sales.delivery_notes.show', $s->dn_id) }}"><small>{{ $s->delivery_note_no }}</small></a></td>
                                                <td><small>{{ $s->transaction_date }}</small></td>
                                                <td><small>{{ $s->customer_name ?? '-' }}</small></td>
                                                <td class="text-right">{{ $fmt($s->qty) }}</td>
                                                <td><small>{{ $s->batch_no ?? '-' }}@if($s->product_batch_id) (#{{ $s->product_batch_id }})@endif</small></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted">Tidak ada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Surat jalan supplier --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h4>Surat Jalan Supplier ({{ $sjSupplier->count() }})</h4></div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead><tr><th>No SJ</th><th>No Supplier</th><th>Tanggal</th><th class="text-right">Qty</th><th>Batch</th></tr></thead>
                                        <tbody>
                                            @forelse($sjSupplier as $s)
                                            <tr>
                                                <td><a href="{{ route('admin.purchasing.supplier_delivery_notes.show', $s->sdn_id) }}"><small>{{ $s->sj_number }}</small></a></td>
                                                <td><small>{{ $s->delivery_note_number ?? '-' }}</small></td>
                                                <td><small>{{ $s->transaction_date }}</small></td>
                                                <td class="text-right">{{ $fmt($s->qty) }}</td>
                                                <td><small>{{ $s->batch_no ?? '-' }}@if($s->product_batch_id) (#{{ $s->product_batch_id }})@endif</small></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted">Tidak ada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Penerimaan (stok masuk) --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h4>Penerimaan Barang / Stok Masuk ({{ $receipts->count() }})</h4></div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead><tr><th>No GR</th><th>Tgl Terima</th><th>Supplier</th><th class="text-right">Order</th><th class="text-right">Terima</th><th>Batch</th></tr></thead>
                                        <tbody>
                                            @forelse($receipts as $r)
                                            <tr>
                                                <td><a href="{{ route('admin.purchasing.goods_receipts.show', $r->gr_id) }}"><small>{{ $r->sj_number }}</small></a></td>
                                                <td><small>{{ $r->received_date }}</small></td>
                                                <td><small>{{ $r->supplier_name ?? '-' }}</small></td>
                                                <td class="text-right">{{ $fmt($r->quantity_ordered) }}</td>
                                                <td class="text-right">{{ $fmt($r->quantity_received) }}</td>
                                                <td><small>{{ $r->batch_no ?? '-' }}</small></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" class="text-center text-muted">Tidak ada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- PO --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h4>Purchase Order ({{ $pos->count() }})</h4></div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead><tr><th>No PO</th><th>Tanggal</th><th class="text-right">Qty</th><th class="text-right">Dibayar</th><th>Status</th></tr></thead>
                                        <tbody>
                                            @forelse($pos as $p)
                                            <tr>
                                                <td><a href="{{ route('admin.purchasing.purchase_orders.show', $p->po_id) }}"><small>{{ $p->po_number }}</small></a></td>
                                                <td><small>{{ $p->po_date }}</small></td>
                                                <td class="text-right">{{ $fmt($p->quantity) }}</td>
                                                <td class="text-right">{{ $fmt($p->paid_qty) }}</td>
                                                <td><small>{{ $p->status }}</small></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted">Tidak ada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Return supplier --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h4>Return ke Supplier ({{ $returns->count() }} baris, total {{ $fmt($returns->sum('qty')) }} pcs)</h4></div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead><tr><th>No Return</th><th>Tanggal</th><th>Supplier</th><th>Gudang</th><th class="text-right">Qty</th><th>Batch (sumber)</th><th>Alasan</th><th>Status</th></tr></thead>
                                        <tbody>
                                            @forelse($returns as $r)
                                            <tr>
                                                <td><a href="{{ route('admin.purchasing.returns.show', $r->ret_id) }}"><small>{{ $r->return_number }}</small></a></td>
                                                <td><small>{{ $r->return_date }}</small></td>
                                                <td><small>{{ $r->supplier_name ?? '-' }}</small></td>
                                                <td><small>{{ $r->warehouse_name ?? '-' }}</small></td>
                                                <td class="text-right">{{ $fmt($r->qty) }}</td>
                                                <td><small>{{ $r->batch_no ?? '-' }}@if($r->product_batch_id) (#{{ $r->product_batch_id }})@endif
                                                @php $bm = $r->product_batch_id ? $batchMap->get($r->product_batch_id) : null; @endphp
                                                @if($bm)<br><span class="text-muted">M: {{ $fmt($bm['m']) }} K: {{ $fmt($bm['k']) }} S: {{ $fmt($bm['s']) }}</span>
                                                @elseif($r->product_batch_id)<br><span class="text-muted">di luar netto ini</span>
                                                @else<br><span class="badge badge-warning">baris tanpa batch</span>@endif</small></td>
                                                <td><small>{{ $r->reason ?? '-' }}</small></td>
                                                <td><small>{{ $r->status }}</small></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="8" class="text-center text-muted">Tidak ada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Opname --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header"><h4>Stock Opname ({{ $opnames->count() }})</h4></div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead><tr><th>Referensi</th><th class="text-right">Sistem</th><th class="text-right">Fisik</th><th class="text-right">Selisih</th><th>Status</th></tr></thead>
                                        <tbody>
                                            @forelse($opnames as $o)
                                            <tr>
                                                <td><a href="{{ url('admin/manage-master/stock-opname/' . $o->op_id) }}"><small>{{ $o->reference_number }}</small></a></td>
                                                <td class="text-right">{{ $fmt($o->system_qty) }}</td>
                                                <td class="text-right">{{ $fmt($o->physical_qty) }}</td>
                                                <td class="text-right">{{ $fmt($o->difference) }}</td>
                                                <td><small>{{ $o->status }}</small></td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted">Tidak ada.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if($canEdit)
    {{-- Modal batch (tambah/edit) --}}
    <div class="modal fade" id="batchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="batchModalTitle">Tambah Batch</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="f-batch-id">
                    <div class="form-group" id="wrap-variant">
                        <label>Varian</label>
                        <select id="f-variant" class="form-control">
                            @foreach($siblingVariants as $v)
                            <option value="{{ $v->id }}" {{ $netto->variants->firstWhere('id', $v->id) ? 'selected' : '' }}>
                                {{ $v->sku_code }} — {{ trim(($v->netto->netto_value ?? '') . ' ' . ($v->netto->satuan ?? '')) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>No Batch</label><input type="text" id="f-batch-no" class="form-control"></div>
                    <div class="form-row">
                        <div class="form-group col-6"><label>Qty Masuk (M)</label><input type="number" id="f-qty" class="form-control" min="0" value="0"></div>
                        <div class="form-group col-6"><label>Harga Beli</label><input type="text" id="f-buy" class="form-control" placeholder="mis. 48.729" inputmode="numeric"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6"><label>Gudang</label>
                            <select id="f-wh" class="form-control"><option value="">--</option>
                                @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-6"><label>Kadaluarsa (Bulan/Tahun)</label><input type="month" id="f-exp" class="form-control"></div>
                    </div>
                    <div class="alert alert-warning d-none" id="batch-dup-warn"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-save-batch">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal merge batch --}}
    <div class="modal fade" id="mergeBatchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Merge Batch</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Source (dihapus): <strong id="mb-source"></strong></p>
                    <div id="mb-usage" class="mb-3"><em>Memuat...</em></div>
                    <div class="form-group"><label>Gabung ke batch target:</label><select id="mb-target" class="form-control"></select></div>
                    <div class="alert alert-danger mb-0">Qty masuk source dijumlahkan ke target, semua penunjukan dipindah.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="btn-do-merge-batch">Gabung</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal edit baris transaksi (gudang + varian) --}}
    <div class="modal fade" id="editTiModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Baris Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Baris: <strong id="eti-info"></strong></p>
                    <div class="form-group"><label>Gudang (header transaksi):</label>
                        <select id="eti-warehouse" class="form-control">
                            <option value="">— kosong —</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Gudang menempel di header transaksi (berlaku untuk semua barisnya).</small>
                    </div>
                    <div class="form-group"><label>Varian baris ini:</label>
                        <select id="eti-variant" class="form-control">
                            <option value="">— tanpa varian —</option>
                            @foreach($siblingVariants as $v)
                            <option value="{{ $v->id }}">{{ $v->sku_code }} — {{ trim(($v->netto->netto_value ?? '') . ' ' . ($v->netto->satuan ?? '')) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-do-edit-ti">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal assign varian --}}
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pasangkan Varian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Batch: <strong id="as-batch"></strong></p>
                    <div class="form-group"><label>Varian se-produk:</label>
                        <select id="as-variant" class="form-control">
                            @foreach($siblingVariants as $v)
                            <option value="{{ $v->id }}">{{ $v->sku_code }} — {{ trim(($v->netto->netto_value ?? '') . ' ' . ($v->netto->satuan ?? '')) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-do-assign">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal hapus batch (cek relasi + wajib replace) --}}
    <div class="modal fade" id="deleteBatchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus (Arsipkan) Batch</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Batch: <strong id="db-batch"></strong></p>
                    <div id="db-usage" class="mb-3"><em>Memeriksa relasi...</em></div>
                    <div class="form-group" id="db-target-wrap">
                        <label>Batch pengganti (wajib — se-produk):</label>
                        <select id="db-target" class="form-control"></select>
                        <small class="text-muted">Semua penunjukan + qty dipindah ke pengganti dulu, baru batch ini diarsipkan. Tidak ada data yatim.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btn-do-delete-batch">Arsipkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal sync movement --}}
    <div class="modal fade" id="linkIncomingMvModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tautkan Movement Masuk & Koreksi M</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Batch: <strong id="im-batch"></strong> | M saat ini: <strong id="im-current"></strong></p>
                    <div class="form-group">
                        <label>Pilih movement yang benar:</label>
                        <select id="im-item" class="form-control"></select>
                    </div>
                    <div id="im-preview" class="alert alert-warning mb-0">Pilih movement untuk melihat perubahan M.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info" id="btn-do-link-incoming-mv" disabled>Tautkan & Koreksi M</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal sync movement --}}
    <div class="modal fade" id="syncMvModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sync Batch Sumber Movement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Movement: <strong id="sm-ref"></strong> <span id="sm-route" class="text-muted"></span></p>
                    <div id="sm-items"><em>Memuat item...</em></div>
                    <small class="text-muted">Hanya produk halaman ini. Satu sync boleh banyak batch, asal sesama produk yang sama.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info" id="btn-do-sync-mv">Simpan Sync</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal realokasi --}}
    <div class="modal fade" id="realokasiModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Realokasi FIFO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body"><div id="realokasi-body"><em>Memuat rencana...</em></div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-info" id="btn-do-realokasi">Jalankan Realokasi</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        $(document).ready(function() {
            var csrf = "{{ csrf_token() }}";
            var productId = {{ $product->id }};
            var nettoId = {{ $netto->id }};
            var allBatches = @json($batches->map(fn($b) => ['id' => $b->id, 'no' => $b->batch_no])->values());

            function reload() { location.reload(); }
            function err(xhr, def) { Swal.fire('Gagal', xhr.responseJSON?.message || def, 'error'); }
            // Format ribuan gaya Indonesia: 48729 -> 48.729
            function fmtRibuan(v) {
                var d = String(v ?? '').replace(/\D/g, '');
                return d ? d.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
            }
            // Bersihkan input harga ke digit bulat: 48.729 / 48729,00 -> 48729
            function cleanHarga(v) {
                return String(v ?? '').replace(/\./g, '').split(',')[0].replace(/\D/g, '');
            }

            // ---- Tambah / Edit batch ----
            $('#btn-add-batch').on('click', function() {
                $('#batchModalTitle').text('Tambah Batch (Stok Masuk)');
                $('#f-batch-id').val('');
                $('#wrap-variant').show();
                $('#f-batch-no').val(''); $('#f-qty').val(0); $('#f-buy').val('');
                $('#f-wh').val(''); $('#f-exp').val('');
                $('#batch-dup-warn').addClass('d-none').text('');
                $('#batchModal').modal('show');
            });
            $('.btn-edit-batch').on('click', function() {
                $('#batchModalTitle').text('Edit Batch #' + $(this).data('id'));
                $('#f-batch-id').val($(this).data('id'));
                $('#wrap-variant').hide();
                $('#f-batch-no').val($(this).data('no'));
                $('#f-qty').val($(this).data('qty'));
                $('#f-buy').val(fmtRibuan($(this).data('buy')));
                $('#f-wh').val($(this).data('wh') || '');
                $('#f-exp').val($(this).data('exp') || '');
                $('#batchModal').modal('show');
            });
            // Format otomatis saat ketik harga
            $('#f-buy').on('input', function() {
                var pos = this.selectionStart;
                var before = this.value.length;
                this.value = fmtRibuan(this.value.split(',')[0]);
                this.selectionStart = this.selectionEnd = Math.max(0, pos + (this.value.length - before));
            });
            $('#btn-save-batch').on('click', function() {
                var id = $('#f-batch-id').val();
                var url = id ? "{{ route('admin.adjustment.batches.update') }}" : "{{ route('admin.adjustment.batches.store') }}";
                var data = {
                    _token: csrf, batch_id: id || undefined,
                    product_id: productId, product_variant_id: $('#f-variant').val(),
                    batch_no: $('#f-batch-no').val(), qty: $('#f-qty').val(),
                    warehouse_id: $('#f-wh').val() || null, expiry_date: $('#f-exp').val() || null,
                    buy_price: cleanHarga($('#f-buy').val()) || null
                };
                $.ajax({ url: url, type: 'POST', data: data,
                    beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                    success: (res) => Swal.fire('Berhasil', res.message, 'success').then(reload),
                    error: (xhr) => err(xhr, 'Gagal menyimpan batch')
                });
            });

            // ---- Merge batch ----
            var mergeSource = null;
            $('.btn-merge-batch').on('click', function() {
                mergeSource = $(this).data('id');
                $('#mb-source').text($(this).data('no') + ' (#' + mergeSource + ')');
                $('#mb-usage').html('<em>Memuat...</em>');
                var opts = allBatches.filter(b => b.id != mergeSource)
                    .map(b => `<option value="${b.id}">${$('<div>').text(b.no).html()} (#${b.id})</option>`).join('');
                $('#mb-target').html(opts);
                $('#mergeBatchModal').modal('show');
                $.get("{{ url('admin/adjustment/products/batches') }}/" + mergeSource + "/usage", function(res) {
                    var rows = Object.entries(res.counts).filter(([k,v]) => v > 0)
                        .map(([k,v]) => `<li>${k}: <strong>${v}</strong></li>`).join('');
                    $('#mb-usage').html(`<p>Qty: <strong>${res.batch.qty}</strong> | Gudang: ${$('<div>').text(res.batch.warehouse).html()}</p>` +
                        (rows ? `<ul>${rows}</ul>` : '<p class="text-muted">Tidak ada penunjukan.</p>'));
                });
            });
            $('#btn-do-merge-batch').on('click', function() {
                if (!$('#mb-target').val()) {
                    Swal.fire('Peringatan', 'Tidak ada batch pengganti. Tambah batch dulu.', 'warning');
                    return;
                }
                Swal.fire({ title: 'Gabung batch?', text: 'Qty dijumlahkan, penunjukan dipindah, source dihapus.',
                    icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, gabung!', cancelButtonText: 'Batal' })
                .then((r) => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: "{{ route('admin.adjustment.batches.merge') }}", type: 'POST',
                        data: { _token: csrf, source_batch_id: mergeSource, target_batch_id: $('#mb-target').val() },
                        beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                        success: (res) => { $('#mergeBatchModal').modal('hide'); Swal.fire('Berhasil', res.message, 'success').then(reload); },
                        error: (xhr) => err(xhr, 'Gagal merge batch')
                    });
                });
            });

            // ---- Assign varian ----
            var assignBatch = null;
            $('.btn-assign-batch').on('click', function() {
                assignBatch = $(this).data('id');
                $('#as-batch').text($(this).data('no') + ' (#' + assignBatch + ')');
                $('#assignModal').modal('show');
            });
            $('#btn-do-assign').on('click', function() {
                $.ajax({ url: "{{ route('admin.adjustment.batches.assign_variant') }}", type: 'POST',
                    data: { _token: csrf, batch_id: assignBatch, variant_id: $('#as-variant').val() },
                    beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                    success: (res) => { $('#assignModal').modal('hide'); Swal.fire('Berhasil', res.message, 'success').then(reload); },
                    error: (xhr) => err(xhr, 'Gagal memasangkan varian')
                });
            });

            // ---- Hapus batch = cek relasi + wajib replace ke batch lain ----
            var delBatch = null, delNeedTarget = false;
            $('.btn-delete-batch').on('click', function() {
                delBatch = $(this).data('id');
                delNeedTarget = false;
                $('#db-batch').text($(this).data('no') + ' (#' + delBatch + ')');
                $('#db-usage').html('<em>Memeriksa relasi...</em>');
                $('#db-target-wrap').hide();
                $('#btn-do-delete-batch').prop('disabled', true);
                var opts = allBatches.filter(b => b.id != delBatch)
                    .map(b => `<option value="${b.id}">${$('<div>').text(b.no).html()} (#${b.id})</option>`).join('');
                $('#db-target').html(opts);
                $('#deleteBatchModal').modal('show');
                $.get("{{ url('admin/adjustment/products/batches') }}/" + delBatch + "/usage", function(res) {
                    var rows = Object.entries(res.counts).filter(([k,v]) => v > 0)
                        .map(([k,v]) => `<li>${k}: <strong>${v}</strong></li>`).join('');
                    if (!rows) {
                        $('#db-usage').html('<div class="alert alert-success mb-0">Tidak dipakai dokumen mana pun — bisa diarsipkan langsung.</div>');
                        $('#btn-do-delete-batch').text('Arsipkan Langsung').prop('disabled', false);
                    } else if (!opts) {
                        $('#db-usage').html(`<p>Batch ini dipakai:</p><ul>${rows}</ul><div class="alert alert-danger mb-0">Tidak ada batch pengganti. Tambah batch dulu.</div>`);
                        $('#btn-do-delete-batch').prop('disabled', true);
                    } else {
                        delNeedTarget = true;
                        $('#db-usage').html(`<p>Batch ini dipakai:</p><ul>${rows}</ul>`);
                        $('#db-target-wrap').show();
                        $('#btn-do-delete-batch').text('Ganti & Arsipkan').prop('disabled', false);
                    }
                }).fail(() => { $('#db-usage').html('<div class="alert alert-danger">Gagal memeriksa relasi.</div>'); });
            });
            $('#btn-do-delete-batch').on('click', function() {
                var target = $('#db-target').val();
                if (delNeedTarget && !target) {
                    Swal.fire('Peringatan', 'Pilih dulu batch pengganti.', 'warning');
                    return;
                }
                Swal.fire({ title: delNeedTarget ? 'Ganti relasi & arsipkan?' : 'Arsipkan batch?',
                    text: delNeedTarget ? 'Semua penunjukan + qty pindah ke pengganti, batch ini diarsipkan.' : 'Batch diarsipkan (bisa dikembalikan).',
                    icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, lanjutkan!', cancelButtonText: 'Batal' })
                .then((r) => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: "{{ route('admin.adjustment.batches.delete') }}", type: 'POST',
                        data: { _token: csrf, batch_id: delBatch, target_batch_id: delNeedTarget ? target : null },
                        beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                        success: (res) => { $('#deleteBatchModal').modal('hide'); Swal.fire('Berhasil', res.message, 'success').then(reload); },
                        error: (xhr) => err(xhr, 'Gagal menghapus batch')
                    });
                });
            });
            // ---- Kembalikan batch dari arsip ----
            $('.btn-restore-batch').on('click', function() {
                var id = $(this).data('id');
                $.ajax({ url: "{{ route('admin.adjustment.batches.restore') }}", type: 'POST',
                    data: { _token: csrf, batch_id: id },
                    beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                    success: (res) => Swal.fire('Berhasil', res.message, 'success').then(reload),
                    error: (xhr) => err(xhr, 'Gagal mengembalikan batch')
                });
            });

            // ---- Hapus baris transaksi (input 2x, total induk dihitung ulang) ----
            $('.btn-del-ti').on('click', function() {
                var id = $(this).data('id');
                Swal.fire({ title: 'Hapus baris transaksi ' + $(this).data('code') + '?',
                    html: `Qty: <strong>${$(this).data('qty')}</strong> | Subtotal: <strong>Rp ${Number($(this).data('sub')).toLocaleString('id-ID')}</strong><br>Total transaksi induk akan dihitung ulang. Ditolak jika transaksi sudah dibayar.`,
                    icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal' })
                .then((r) => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: "{{ route('admin.adjustment.transaction_items.delete') }}", type: 'POST',
                        data: { _token: csrf, item_id: id },
                        beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                        success: (res) => Swal.fire('Berhasil', res.message, 'success').then(reload),
                        error: (xhr) => err(xhr, 'Gagal menghapus baris')
                    });
                });
            });

            // ---- Edit baris transaksi (gudang + varian) ----
            var editTi = null;
            $('.btn-edit-ti').on('click', function() {
                editTi = $(this).data('id');
                $('#eti-info').text($(this).data('code') + ' (baris #' + editTi + ')');
                $('#eti-warehouse').val($(this).data('wh') || '');
                $('#eti-variant').val($(this).data('var') || '');
                $('#editTiModal').modal('show');
            });
            $('#btn-do-edit-ti').on('click', function() {
                $.ajax({ url: "{{ route('admin.adjustment.transaction_items.update') }}", type: 'POST',
                    data: { _token: csrf, item_id: editTi, warehouse_id: $('#eti-warehouse').val() || null, variant_id: $('#eti-variant').val() || null },
                    beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                    success: (res) => { $('#editTiModal').modal('hide'); Swal.fire('Berhasil', res.message, 'success').then(reload); },
                    error: (xhr) => err(xhr, 'Gagal menyimpan baris')
                });
            });

            // ---- Lengkapi varian kosong dari batch ----
            $('#btn-fill-variant').on('click', function() {
                $.get("{{ url('admin/adjustment/products/netto') }}/" + nettoId + "/fill-variant-preview", function(res) {
                    if (!res.total) {
                        Swal.fire('Bersih', 'Tidak ada baris bervarian-kosong.', 'success');
                        return;
                    }
                    var rows = Object.entries(res.plan).map(([k,v]) => `<li>${k}: <strong>${v}</strong> baris</li>`).join('');
                    Swal.fire({ title: 'Lengkapi varian kosong?',
                        html: `<p>Isi varian dari batch-nya (total <strong>${res.total}</strong> baris):</p><ul>${rows}</ul>`,
                        icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, lengkapi!', cancelButtonText: 'Batal' })
                    .then((r) => {
                        if (!r.isConfirmed) return;
                        $.ajax({ url: "{{ route('admin.adjustment.netto.fill_variant') }}", type: 'POST',
                            data: { _token: csrf, netto_id: nettoId },
                            beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                            success: (res2) => Swal.fire('Berhasil', res2.message, 'success').then(reload),
                            error: (xhr) => err(xhr, 'Gagal melengkapi varian')
                        });
                    });
                }).fail(() => Swal.fire('Gagal', 'Gagal memeriksa.', 'error'));
            });

            // ---- Sync batch sumber movement (semua item sekaligus) ----
            var syncMvId = null;
            $('.btn-sync-mv').on('click', function() {
                syncMvId = $(this).data('id');
                $('#sm-items').html('<em>Memuat item...</em>');
                $('#syncMvModal').modal('show');
                $.get("{{ url('admin/adjustment/products/movements') }}/" + syncMvId + "/sync-data", { product_id: productId }, function(res) {
                    $('#sm-ref').text(res.movement.ref + ' (' + res.movement.status + ')');
                    $('#sm-route').text(res.movement.from + ' → ' + res.movement.to);
                    var html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Produk</th><th>Varian</th><th class="text-right">Qty</th><th>Batch sumber</th></tr></thead><tbody>';
                    res.items.forEach(function(it) {
                        var opts = it.candidates.map(c =>
                            `<option value="${c.id}" ${c.id == it.batch_id ? 'selected' : ''}>${$('<div>').text(c.text).html()}</option>`).join('');
                        html += `<tr><td><small>${$('<div>').text(it.product).html()}</small></td>` +
                            `<td><small>${$('<div>').text(it.variant).html()}</small></td>` +
                            `<td class="text-right">${it.qty}</td>` +
                            `<td><select class="form-control form-control-sm sm-batch" data-item="${it.item_id}">${opts || '<option value="">-- tidak ada kandidat --</option>'}</select></td></tr>`;
                    });
                    $('#sm-items').html(html + '</tbody></table></div>');
                }).fail(() => $('#sm-items').html('<div class="alert alert-danger">Gagal memuat.</div>'));
            });
            $('#btn-do-sync-mv').on('click', function() {
                var links = $('.sm-batch').map(function() { return { item_id: $(this).data('item'), batch_id: $(this).val() }; }).get();
                if (!links.length || links.some(l => !l.batch_id)) {
                    Swal.fire('Peringatan', 'Semua item harus punya batch sumber.', 'warning');
                    return;
                }
                    $.ajax({ url: "{{ route('admin.adjustment.movements.sync') }}", type: 'POST',
                        data: { _token: csrf, movement_id: syncMvId, product_id: productId, links: links },
                    beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                    success: (res) => { $('#syncMvModal').modal('hide'); Swal.fire('Berhasil', res.message, 'success').then(reload); },
                    error: (xhr) => err(xhr, 'Gagal sync movement')
                });
            });

            // ---- Tautkan movement penerimaan dan koreksi M ----
            var incomingBatchId = null;
            $('.btn-link-incoming-mv').on('click', function() {
                incomingBatchId = $(this).data('id');
                $('#im-batch').text($(this).closest('tr').find('td:first strong').text() + ' (#' + incomingBatchId + ')');
                $('#im-current').text($(this).closest('tr').find('td:nth-child(4)').text().trim());
                $('#im-item').html('<option value="">Memuat kandidat movement...</option>');
                $('#im-preview').text('Pilih movement untuk melihat perubahan M.');
                $('#btn-do-link-incoming-mv').prop('disabled', true);
                $('#linkIncomingMvModal').modal('show');

                $.get("{{ url('admin/adjustment/products/batches') }}/" + incomingBatchId + "/incoming-movements")
                    .done(function(res) {
                        var opts = (res.items || []).map(function(item) {
                            var label = item.reference_number + ' | ' + item.from_warehouse + ' → ' +
                                item.to_warehouse + ' | diterima ' + (item.received_at || '-') +
                                ' | qty ' + item.qty + ' | item #' + item.item_id;
                            return `<option value="${item.item_id}" data-qty="${item.qty}">${$('<div>').text(label).html()}</option>`;
                        }).join('');
                        $('#im-current').text(res.batch.qty);
                        $('#im-batch').text(res.batch.batch_no + ' (#' + res.batch.id + ') — ' + res.batch.warehouse);
                        $('#im-item').html('<option value="">-- pilih movement --</option>' + opts);
                        if (!opts) {
                            $('#im-item').html('<option value="">Tidak ada movement yang cocok</option>');
                            $('#im-preview').text('Pastikan movement sudah completed, gudang tujuan, produk/varian, dan no batch cocok.');
                        }
                    })
                    .fail(function(xhr) {
                        $('#im-item').html('<option value="">Gagal memuat kandidat</option>');
                        err(xhr, 'Gagal memuat movement masuk');
                    });
            });

            $('#im-item').on('change', function() {
                var selected = $(this).find(':selected');
                var qty = selected.data('qty');
                var current = Number($('#im-current').text());
                var valid = $(this).val() !== '';
                $('#btn-do-link-incoming-mv').prop('disabled', !valid);
                $('#im-preview').text(valid
                    ? 'M akan disamakan dari ' + current + ' menjadi ' + qty +
                        ' sesuai qty yang dikirim pada movement. S akan dihitung ulang setelah ditautkan.'
                    : 'Pilih movement untuk melihat perubahan M.');
            });

            $('#btn-do-link-incoming-mv').on('click', function() {
                var movementItemId = $('#im-item').val();
                if (!incomingBatchId || !movementItemId) return;
                var qty = $('#im-item').find(':selected').data('qty');
                Swal.fire({
                    title: 'Tautkan movement?',
                    text: 'M batch akan menjadi ' + qty + ' sesuai qty movement yang dipilih. K transaksi/movement tetap tercatat terpisah.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Tautkan & koreksi M',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.adjustment.batches.link_movement') }}",
                        type: 'POST',
                        data: { _token: csrf, batch_id: incomingBatchId, movement_item_id: movementItemId },
                        beforeSend: () => $.LoadingOverlay('show'),
                        complete: () => $.LoadingOverlay('hide'),
                        success: (res) => {
                            $('#linkIncomingMvModal').modal('hide');
                            Swal.fire('Berhasil', res.message, 'success').then(reload);
                        },
                        error: (xhr) => err(xhr, 'Gagal menautkan movement masuk')
                    });
                });
            });

            // ---- Realokasi FIFO ----
            $('#btn-realokasi').on('click', function() {
                $('#realokasi-body').html('<em>Memuat rencana...</em>');
                $('#realokasiModal').modal('show');
                $.get("{{ url('admin/adjustment/products/netto') }}/" + nettoId + "/realokasi-preview", function(res) {
                    if (res.note && !res.moves.length && !res.unresolved.length) {
                        $('#realokasi-body').html(`<div class="alert alert-success">${$('<div>').text(res.note).html()}</div>`);
                        $('#btn-do-realokasi').hide();
                        return;
                    }
                    $('#btn-do-realokasi').show();
                    var mv = (res.moves || []).map(m =>
                        `<li>${m.kind === 'sri' ? 'Return' : 'Jual'} #${m.item_id} (qty ${m.qty}): <strong>${$('<div>').text(m.from_no).html()}</strong> → <strong>${$('<div>').text(m.to_no).html()}</strong></li>`).join('');
                    var un = (res.unresolved || []).map(u =>
                        `<li><strong>${$('<div>').text(u.batch_no).html()}</strong> masih minus ${u.sisa}</li>`).join('');
                    $('#realokasi-body').html(
                        `<p>Rencana pemindahan (FIFO, baris utuh): <strong>${res.moves.length}</strong></p>` +
                        (mv ? `<ul>${mv}</ul>` : '<p class="text-muted">Tidak ada baris yang bisa digeser.</p>') +
                        (un ? `<div class="alert alert-warning mb-0">Belum tuntas:<ul class="mb-0">${un}</ul></div>` : '') +
                        (res.note ? `<p class="text-muted">${$('<div>').text(res.note).html()}</p>` : '')
                    );
                }).fail((xhr) => $('#realokasi-body').html('<div class="alert alert-danger">Gagal memuat.</div>'));
            });
            $('#btn-do-realokasi').on('click', function() {
                Swal.fire({ title: 'Jalankan realokasi?', text: 'Hanya memindah penunjukan batch (tidak mengubah qty/rupiah).',
                    icon: 'question', showCancelButton: true, confirmButtonText: 'Jalankan', cancelButtonText: 'Batal' })
                .then((r) => {
                    if (!r.isConfirmed) return;
                    $.ajax({ url: "{{ route('admin.adjustment.netto.realokasi') }}", type: 'POST',
                        data: { _token: csrf, netto_id: nettoId },
                        beforeSend: () => $.LoadingOverlay('show'), complete: () => $.LoadingOverlay('hide'),
                        success: (res) => { $('#realokasiModal').modal('hide'); Swal.fire('Berhasil', res.message, 'success').then(reload); },
                        error: (xhr) => err(xhr, 'Gagal realokasi')
                    });
                });
            });
        });
    </script>
    @endpush
@endsection
