@extends('master')

@section('title', 'Detail Return Barang')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Return Barang</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item">Return Barang</div>
                <div class="breadcrumb-item">Detail</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Return To Supplier</h2>
                                <div class="invoice-number">Order #{{ $return->return_number }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Supplier:</strong><br>
                                        {{ $return->supplier->name }}<br>
                                        {{ $return->supplier->address }}<br>
                                        {{ $return->supplier->phone }}
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Gudang Asal:</strong><br>
                                        {{ $return->warehouse->name }}<br>
                                        {{ $return->warehouse->address }}
                                    </address>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Metode:</strong><br>
                                        Return Fisik & Stok<br>
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Tanggal Return:</strong><br>
                                        {{ date('d F, Y', strtotime($return->return_date)) }}<br><br>
                                        <strong>Status:</strong><br>
                                        <span class="badge badge-success">{{ strtoupper($return->status) }}</span>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Ringkasan Item</div>
                            <p class="section-lead">Item berikut telah dikembalikan dan stok telah dikurangi.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tr>
                                        <th data-width="40">#</th>
                                        <th>Item</th>
                                        <th class="text-center">Batch No</th>
                                        <th class="text-center">Quantity</th>
                                    </tr>
                                    @foreach($return->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $merek = $item->product->merek ? $item->product->merek->name . ' ' : '';
                                                $netto = ($item->variant && $item->variant->netto) ? ' ' . $item->variant->netto->netto_value . ' ' . $item->variant->netto->satuan : '';
                                            @endphp
                                            <strong>{{ $merek }}{{ $item->product->name }}{{ $netto }}</strong>
                                            @if($item->reason)
                                                <br><small class="text-danger">Keterangan: {{ $item->reason }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->batch ? $item->batch->batch_no : '-' }}</td>
                                        <td class="text-center">{{ $item->qty }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="section-title">Catatan</div>
                                    <p class="section-lead">{{ $return->notes ?: 'Tidak ada catatan.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-md-right">
                    <div class="float-lg-left mb-lg-0 mb-3">
                        <a href="{{ route('admin.purchasing.returns.print', $return->id) }}" target="_blank" class="btn btn-primary btn-icon icon-left"><i class="fas fa-print"></i> Print Dokumen</a>
                        <a href="{{ route('admin.purchasing.returns.edit', $return->id) }}" class="btn btn-warning btn-icon icon-left"><i class="fas fa-edit"></i> Edit</a>
                    </div>
                    <a href="{{ route('admin.purchasing.returns.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
