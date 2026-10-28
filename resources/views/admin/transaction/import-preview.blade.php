@extends('master')
@section('title', 'Preview Import Transaksi - ')

@push('styles')
<style>
    .import-header { background:#fff; border-left:5px solid #0d9488; border-radius:12px; padding:18px 22px; margin-bottom:22px; box-shadow:0 4px 20px rgba(0,0,0,.05); }
    .import-header h1 { margin:0; color:#1e293b; font-size:22px; font-weight:800; }
    .summary-card { height:100%; border:1px solid #e2e8f0; border-radius:12px; padding:16px 18px; background:#fff; }
    .summary-card small { color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase; }
    .summary-card strong { color:#0f172a; display:block; font-size:22px; margin-top:4px; }
    .mapping-table th, .order-table th { background:#f8fafc; color:#64748b; font-size:11px; text-transform:uppercase; }
    .mapping-table td, .order-table td { vertical-align:middle; font-size:13px; }
    .mapping-table { table-layout:fixed; width:100%; }
    .mapping-table .product-pos-column { width:360px; max-width:360px; overflow:hidden; }
    .mapping-table .product-pos-cell { width:360px; max-width:360px; overflow:hidden; }
    .mapping-table .product-pos-cell .select2-container { max-width:100% !important; overflow:hidden; }
    .mapping-table .product-pos-cell .select2-selection__rendered { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .mapping-table .product-pos-cell .select2-dropdown { max-width:360px; overflow:hidden; }
    .mapping-table .product-pos-cell .select2-results__option { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .select2-container { width:100% !important; }
    .select2-container--default .select2-selection--single { height:38px; border-color:#d1d5db; border-radius:6px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        @php
            $duplicateOrderNumbers = collect($preview['orders'])
                ->where('already_imported', true)
                ->pluck('number')
                ->all();
        @endphp
        <div class="import-header d-flex justify-content-between align-items-center">
            <div>
                <h1>Preview & Sinkronisasi Produk</h1>
                <div class="text-muted mt-1">File: {{ $preview['source_file'] }}</div>
            </div>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        @if (session()->has('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Data pada halaman ini belum disimpan. Cocokkan setiap nama produk file dengan produk/varian POS, lalu tentukan saluran dan gudang. Baris dengan nomor pesanan sama akan dibuat sebagai satu transaksi; nomor pesanan sumber disimpan di catatan transaksi.
            Status “Siap diimpor” pada ringkasan adalah status awal preview. Produk POS yang tidak dipilih akan dilewati saat import. Jika semua produk dalam suatu pesanan tidak dicocokkan, transaksi untuk pesanan itu tidak dibuat.
            @if ($preview['format'] === 'marketplace')
                Hanya pesanan berstatus <strong>Selesai</strong> yang akan diproses.
            @endif
        </div>
        @if ($preview['skipped_rows'] > 0)
            <div class="alert alert-warning">
                {{ $preview['skipped_rows'] }} baris dilewati
                @if ($preview['format'] === 'marketplace')
                    karena statusnya bukan Selesai atau data pentingnya tidak lengkap.
                @else
                    karena data pentingnya tidak lengkap.
                @endif
            </div>
        @endif
        @if (count($duplicateOrderNumbers) > 0)
            <div class="alert alert-warning">
                {{ count($duplicateOrderNumbers) }} nomor pesanan sudah pernah diimpor. Pesanan tersebut akan dilewati saat konfirmasi.
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-3 mb-2"><div class="summary-card"><small>Baris siap diimpor</small><strong>{{ count($preview['rows']) }}</strong></div></div>
            <div class="col-md-3 mb-2"><div class="summary-card"><small>Nomor transaksi/pesanan</small><strong>{{ count($preview['orders']) }}</strong></div></div>
            <div class="col-md-3 mb-2"><div class="summary-card"><small>Nama produk perlu dicocokkan</small><strong>{{ count($preview['products']) }}</strong></div></div>
            <div class="col-md-3 mb-2"><div class="summary-card"><small>Pesanan sudah diimpor</small><strong>{{ count($duplicateOrderNumbers) }}</strong></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">Ringkasan Transaksi per Nomor Pesanan</h4></div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-bordered order-table">
                    <thead>
                        <tr>
                            <th>No. Pesanan / Transaksi</th>
                            <th>Tanggal</th>
                            <th>Nama produk</th>
                            <th>Jumlah baris produk</th>
                            <th>Total produk</th>
                            <th>Status import</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['orders'] as $order)
                            <tr>
                                <td>{{ $order['number'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($order['date'])->format('d/m/Y') }}</td>
                                <td>
                                    @foreach ($order['products'] as $product)
                                        <div>{{ $product['name'] }} <span class="text-muted">× {{ $product['qty'] }}</span></div>
                                    @endforeach
                                </td>
                                <td>{{ $order['rows'] }}</td>
                                <td>Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
                                <td>
                                    @if ($order['already_imported'])
                                        <span class="badge badge-warning">Sudah diimpor — akan dilewati</span>
                                    @else
                                        <span class="badge badge-success">Siap diimpor</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.transactions.import.confirm') }}" id="confirm-import-form">
            @csrf
            <input type="hidden" name="token" value="{{ $preview['token'] }}">

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">Pengaturan Transaksi</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="import-source">Saluran</label>
                            <select class="form-control" id="import-source" name="source" required>
                                <option value="">Pilih saluran</option>
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel->slug }}" {{ old('source') === $channel->slug ? 'selected' : '' }}>{{ $channel->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="import-warehouse">Gudang</label>
                            <select class="form-control" id="import-warehouse" name="warehouse_id" required>
                                <option value="">Pilih gudang</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ (string) old('warehouse_id') === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @if ($warehouses->isEmpty())
                                <small class="text-danger">Akun Anda belum memiliki akses ke gudang aktif.</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Pencocokan Produk POS</h4>
                    <small class="text-muted">Format pilihan: merek, nama produk, netto, dan satuan (UOM).</small>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered mapping-table">
                        <thead>
                            <tr>
                                <th>Nama produk pada file</th>
                                <th>SKU / variasi file</th>
                                <th>Jumlah baris</th>
                                <th class="product-pos-column">Produk POS yang sesuai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview['products'] as $key => $product)
                                <tr>
                                    <td>{{ $product['name'] }}</td>
                                    <td>
                                        {{ $product['sku'] ?: '-' }}
                                        @if ($product['variant_name'])
                                            <small class="text-muted d-block">{{ $product['variant_name'] }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $product['rows'] }}</td>
                                    <td class="product-pos-cell">
                                        <select class="form-control product-map" name="product_map[{{ $key }}]" data-selected="{{ old('product_map.' . $key) ?? ($preview['auto_matches'][$key] ?? '') }}">
                                            <option value="">Tidak dicocokkan — lewati produk ini</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Data Pesanan yang Akan Diproses dihapus sesuai permintaan --}}

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-success btn-lg" id="confirm-import-button" {{ $warehouses->isEmpty() || $channels->isEmpty() ? 'disabled' : '' }}>
                    <i class="fas fa-check mr-1"></i> Konfirmasi Import
                </button>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var productSearchUrl = @json(route('admin.transactions.import.products'));
    $('.product-map').select2({
        placeholder: 'Cari merek, nama produk, netto, atau satuan...',
        minimumInputLength: 2,
        ajax: {
            url: productSearchUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (response) {
                return response;
            }
        }
    });

    $('.product-map').each(function () {
        var $select = $(this);
        var selected = $select.data('selected');
        if (!selected) return;

        $.getJSON(productSearchUrl, { selected: selected }).done(function (response) {
            var option = response.results.find(function (item) { return item.id === selected; });
            if (option) {
                $select.append(new Option(option.text, option.id, true, true)).trigger('change');
            }
        });
    });

    $('#confirm-import-form').on('submit', function () {
        $('#confirm-import-button').prop('disabled', true).addClass('btn-progress');
    });
});
</script>
@endpush
