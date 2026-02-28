@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Detail Surat Jalan (Manual)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></div>
                <div class="breadcrumb-item">Detail</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $deliveryNote->delivery_note_no }}</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sales.delivery_notes.print', ['id' => $deliveryNote->id, 'type' => 'manual']) }}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-print"></i> Cetak
                                </a>
                                <a href="{{ route('admin.sales.delivery_notes.edit', $deliveryNote->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Informasi Customer</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th style="width: 30%">Nama</th>
                                            <td>: {{ $deliveryNote->customer_name ?? ($deliveryNote->customer ? $deliveryNote->customer->name : 'Umum') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Telepon</th>
                                            <td>: {{ $deliveryNote->customer_phone ?? ($deliveryNote->customer ? $deliveryNote->customer->phone : '-') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Detail Dokumen</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th style="width: 40%">Tanggal</th>
                                            <td>: {{ \Carbon\Carbon::parse($deliveryNote->transaction_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tipe Pengiriman</th>
                                            <td>: {{ strtoupper($deliveryNote->delivery_type) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">No</th>
                                            <th>Produk / Barang</th>
                                            <th style="width: 15%">Jumlah</th>
                                            <th style="width: 15%">Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deliveryNote->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->product ? $item->product->name : 'N/A' }} ({{ $item->batch ? $item->batch->batch_no : 'N/A' }})</td>
                                            <td>{{ $item->qty }}</td>
                                            <td>{{ $item->product ? $item->product->pieces : 'pcs' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($deliveryNote->notes)
                            <div class="mt-4">
                                <h6>Catatan:</h6>
                                <p>{{ $deliveryNote->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
