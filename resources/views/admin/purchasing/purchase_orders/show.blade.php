@extends('master')

@section('title', 'Detail Purchase Order')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.purchase_orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Purchase Order {{ $po->po_number }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.purchase_orders.index') }}">Order Pembelian</a></div>
                <div class="breadcrumb-item">Detail PO</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>Purchase Order</h2>
                                <div class="invoice-number">Order {{ $po->po_number }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>Kepada (Supplier):</strong><br>
                                        {{ $po->supplier->name }}<br>
                                        {{ $po->supplier->address }}<br>
                                        {{ $po->supplier->city }}, {{ $po->supplier->province }} {{ $po->supplier->postal_code }}<br>
                                        Telp: {{ $po->supplier->phone }}
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>Status:</strong> 
                                        @php
                                            $badges = [
                                                'draft' => 'secondary',
                                                'submitted' => 'info',
                                                'approved' => 'primary',
                                                'received' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $badges[$po->status] }}">{{ strtoupper($po->status) }}</span><br><br>
                                        <strong>Tanggal Order:</strong><br>
                                        {{ $po->po_date->format('d F Y') }}<br><br>
                                        <strong>Estimasi Terima:</strong><br>
                                        {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d F Y') : '-' }}
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">Ringkasan Order</div>
                            <p class="section-lead">Semua item yang dipesan harus sesuai dengan spesifikasi.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tr>
                                        <th data-width="40">#</th>
                                        <th style="width: 25%;">Item</th>
                                        <th class="text-center" style="width: 10%;">Qty</th>
                                        <th class="text-right" style="width: 15%;">Harga</th>
                                        <th class="text-right" style="width: 15%;">Total</th>
                                        <th class="text-center">Keterangan</th>
                                    </tr>
                                    @foreach($po->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                        </td>
                                        <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                        <td class="text-right">Rp {{ rtrim(rtrim(number_format($item->unit_price, 2, ',', '.'), '0'), ',') }}</td>
                                        <td class="text-right">Rp {{ rtrim(rtrim(number_format($item->total, 2, ',', '.'), '0'), ',') }}</td>
                                        <td class="text-center">{{ $item->description ?: '-' }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-8">
                                    <div class="section-title">Catatan</div>
                                    <p class="text-muted">{{ $po->notes ?: 'Tidak ada catatan khusus.' }}</p>
                                    <div class="mt-4">
                                        <strong>Dibuat Oleh:</strong><br>
                                        {{ $po->creator->name }}<br>
                                        {{ $po->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                <div class="col-lg-4 text-right">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Subtotal</div>
                                        <div class="invoice-detail-value">Rp {{ rtrim(rtrim(number_format($po->subtotal, 2, ',', '.'), '0'), ',') }}</div>
                                    </div>
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Diskon ({{ $po->discount_type == 'percentage' ? $po->discount_value.'%' : 'Rp' }})</div>
                                        <div class="invoice-detail-value">- Rp {{ rtrim(rtrim(number_format($po->discount_amount, 2, ',', '.'), '0'), ',') }}</div>
                                    </div>
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Pajak ({{ $po->tax_percentage }}%)</div>
                                        <div class="invoice-detail-value">Rp {{ rtrim(rtrim(number_format($po->tax_amount, 2, ',', '.'), '0'), ',') }}</div>
                                    </div>
                                    <hr class="mt-2 mb-2">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">Total</div>
                                        <div class="invoice-detail-value invoice-detail-value-lg">Rp {{ rtrim(rtrim(number_format($po->total, 2, ',', '.'), '0'), ',') }}</div>
                                    </div>
                                    <hr class="mt-4 mb-4">
                                    <div class="text-right">
                                        <a href="{{ route('admin.purchasing.purchase_orders.edit', $po->id) }}" class="btn btn-warning btn-icon icon-left"><i class="fas fa-edit"></i> Edit PO</a>
                                        <a href="{{ route('admin.purchasing.purchase_orders.print', $po->id) }}" target="_blank" class="btn btn-primary btn-icon icon-left"><i class="fas fa-print"></i> Print PO</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
