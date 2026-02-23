@extends('master')

@section('title', 'Detail Penerimaan Barang')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.goods_receipts.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Surat Penerimaan Barang #{{ $gr->sj_number }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.goods_receipts.index') }}">Penerimaan Barang</a></div>
                <div class="breadcrumb-item">Detail SJ</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Penerimaan Barang (SJ Internal)</h2>
                                <div class="invoice-number">SJ #{{ $gr->sj_number }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Dari (Supplier):</strong><br>
                                        {{ $gr->supplier->name }}<br>
                                        {{ $gr->supplier->address }}<br>
                                        {{ $gr->supplier->city }}, {{ $gr->supplier->province }}<br>
                                        No. Surat Jalan Supplier: <strong>{{ $gr->delivery_note_number }}</strong>
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Informasi Penerimaan:</strong><br>
                                        Tanggal Terima: {{ $gr->received_date->format('d F Y') }}<br>
                                        Diterima Oleh: {{ $gr->receiver->name }}<br>
                                        Referensi PO: {{ $gr->purchaseOrder ? $gr->purchaseOrder->po_number : '-' }}<br>
                                        Status: <span class="badge badge-success">{{ strtoupper($gr->status) }}</span>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Daftar Barang Diterima</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md text-nowrap">
                                    <thead>
                                        <tr>
                                            <th data-width="40">#</th>
                                            <th>Nama Produk</th>
                                            <th>Deskripsi</th>
                                            <th class="text-center">Satuan</th>
                                            <th class="text-center">Qty Pesan</th>
                                            <th class="text-center">Qty Terima</th>
                                            <th class="text-center">Selisih</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gr->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-center">{{ $item->satuan }}</td>
                                            <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity_ordered, 2, ',', '.'), '0'), ',') }}</td>
                                            <td class="text-center font-weight-bold">{{ rtrim(rtrim(number_format($item->quantity_received, 2, ',', '.'), '0'), ',') }}</td>
                                            <td class="text-center">
                                                @if($item->quantity_difference == 0)
                                                    <span class="text-success">Match</span>
                                                @else
                                                    <span class="text-danger">{{ rtrim(rtrim(number_format($item->quantity_difference, 2, ',', '.'), '0'), ',') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->notes ?: '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="section-title">Catatan Penerimaan</div>
                                    <p class="text-muted">{{ $gr->notes ?: 'Tidak ada catatan.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-md-right">
                    <div class="float-lg-left mb-lg-0 mb-3">
                        <button class="btn btn-primary btn-icon icon-left" onclick="window.print()"><i class="fas fa-print"></i> Cetak SJ Internal</button>
                    </div>
                    <button class="btn btn-secondary btn-icon icon-left" onclick="location.reload()"><i class="fas fa-undo"></i> Sinkron Ulang</button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
