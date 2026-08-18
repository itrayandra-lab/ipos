@extends('master')

@section('title', 'Detail Surat Jalan Supplier')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.supplier_delivery_notes.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Surat Jalan Supplier #{{ $sdn->sj_number }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.supplier_delivery_notes.index') }}">Surat Jalan Supplier</a></div>
                <div class="breadcrumb-item">Detail</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Surat Jalan Supplier</h2>
                                <div class="invoice-number">SJS #{{ $sdn->sj_number }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Dari (Supplier):</strong><br>
                                        {{ $sdn->supplier->name }}<br>
                                        {{ $sdn->supplier->address }}<br>
                                        {{ $sdn->supplier->city }}, {{ $sdn->supplier->province }}<br>
                                        No. SJ Fisik: <strong>{{ $sdn->delivery_note_number ?? '-' }}</strong>
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Informasi:</strong><br>
                                        Tanggal: {{ $sdn->transaction_date->format('d F Y') }}<br>
                                        Dicatat Oleh: {{ $sdn->user->name ?? '-' }}<br>
                                        Tidak menambah stok (link stok existing)
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Daftar Item Batch</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md text-nowrap">
                                    <thead>
                                        <tr>
                                            <th data-width="40">#</th>
                                            <th>Nama Produk</th>
                                            <th class="text-center">Batch No</th>
                                            <th>Gudang</th>
                                            <th class="text-center">Qty Dikirim</th>
                                            <th class="text-center">Stok Awal Batch</th>
                                            <th class="text-center">Stok Saat Ini</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sdn->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($item->batch && $item->batch->product)
                                                    {{ $item->batch->product->merek->name ?? '' }} {{ $item->batch->product->name }}
                                                    @if($item->batch->variant && $item->batch->variant->netto)
                                                        {{ $item->batch->variant->netto->netto_value }} {{ $item->batch->variant->netto->satuan }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center"><span class="badge badge-info">{{ $item->batch->batch_no ?? '-' }}</span></td>
                                            <td>{{ $item->batch->warehouse->name ?? '-' }}</td>
                                            <td class="text-center font-weight-bold">{{ $item->qty }}</td>
                                            <td class="text-center">{{ $item->batch->qty ?? '-' }}</td>
                                            <td class="text-center font-weight-bold">{{ $item->batch ? $item->batch->current_stock : '-' }}</td>
                                            <td>{{ $item->notes ?: '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="section-title">Catatan</div>
                                    <p class="text-muted">{{ $sdn->notes ?: 'Tidak ada catatan.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-md-right">
                    <div class="float-lg-left mb-lg-0 mb-3">
                        <button class="btn btn-primary btn-icon icon-left" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
