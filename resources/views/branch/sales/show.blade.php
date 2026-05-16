@extends('master')
@section('title', 'Detail Penjualan')
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
                        <div class="card-header"><h4>Informasi Penjualan</h4></div>
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
                                    <span class="text-muted">Gudang</span><strong>{{ $sale->warehouse->name ?? '-' }}</strong>
                                </li>
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
