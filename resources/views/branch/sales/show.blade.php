@extends('master')
@section('title', 'Detail Penjualan')
@push('styles')
<style>
    .btn-edit-premium {
        border-radius: 50px;
        padding: 8px 22px;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.5px;
        box-shadow: 0 3px 10px rgba(255, 193, 7, 0.25);
        transition: all 0.3s ease;
    }
    .btn-edit-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(255, 193, 7, 0.35);
    }
    .info-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
    }
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }
</style>
@endpush
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('branch.sales.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Penjualan <code>{{ $sale->reference_number }}</code></h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Informasi Penjualan</h4>
                            <div class="card-header-action">
                                <a href="{{ route('branch.sales.edit', $sale->id) }}" class="btn btn-warning btn-edit-premium"><i class="fas fa-edit mr-1"></i> Edit</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">No. Referensi</span><code>{{ $sale->reference_number }}</code>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Tanggal</span><span>{{ $sale->sale_date->format('d M Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Dicatat oleh</span><span>{{ $sale->user->name ?? '-' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Saluran</span><strong>{{ $sale->source ? ucfirst($sale->source) : 'Langsung (Toko)' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Gudang</span><strong>{{ $sale->warehouse->name ?? '-' }}</strong>
                                </li>
                                @if($sale->customer_name)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Customer</span><span>{{ $sale->customer_name }}</span>
                                </li>
                                @endif
                                @if($sale->external_order_id)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">No. Pesanan</span><span>{{ $sale->external_order_id }}</span>
                                </li>
                                @endif
                                @if($sale->payment_receipt)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Bukti Bayar</span>
                                    <a href="{{ asset($sale->payment_receipt) }}" target="_blank" class="badge badge-info"><i class="fas fa-file-invoice mr-1"></i> Lihat</a>
                                </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Total</span>
                                    <strong class="text-success">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong>
                                </li>
                                @if($sale->notes)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Catatan</div>
                                    <div>{{ $sale->notes }}</div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h4>Item Terjual</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr><th>#</th><th>Produk</th><th>Batch</th>
                                        <th class="text-center">Qty</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @php
                                                    $merek = $item->product->merek->name ?? '';
                                                    $nama = $item->product->name ?? '';
                                                    $netto = $item->variant->netto ?? null;
                                                    $nettoLabel = $netto ? trim(($netto->netto_value ?? '') . ' ' . ($netto->satuan ?? '')) : '';
                                                @endphp
                                                {{ trim("$merek $nama $nettoLabel") ?: '-' }}
                                            </td>
                                            <td><code>{{ $item->batch->batch_no ?? '-' }}</code></td>
                                            <td class="text-center">{{ number_format($item->qty_sold, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-right font-weight-bold">Total</td>
                                            <td class="text-right font-weight-bold text-success">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
