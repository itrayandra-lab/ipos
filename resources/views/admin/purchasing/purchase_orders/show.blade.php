@extends('master')
@section('title', 'Detail Purchase Order - ')

@push('styles')
<style>
    /* Premium Purchasing Aesthetic */
    .section-header {
        background: #fff;
        padding: 20px 25px !important;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px !important;
        border-left: 5px solid #0d9488;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .section-header h1 {
        font-weight: 800 !important;
        color: #1e293b !important;
        margin-bottom: 0;
    }

    .card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
        margin-bottom: 30px;
    }
    
    .invoice-premium {
        background: white;
        padding: 40px;
        border-radius: 20px;
    }

    .info-label {
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .info-value {
        color: #1e293b;
        font-weight: 600;
        font-size: 15px;
    }

    .table-premium thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 15px 20px !important;
    }
    .table-premium tbody td {
        padding: 15px 20px !important;
        vertical-align: middle;
        color: #334155;
    }

    .badge-status {
        font-weight: 800;
        text-transform: uppercase;
        font-size: 11px;
        padding: 6px 15px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }
    .bg-soft-draft { background: #f1f5f9; color: #64748b; }
    .bg-soft-submitted { background: #e0f2fe; color: #0284c7; }
    .bg-soft-approved { background: #dcfce7; color: #16a34a; }
    .bg-soft-received { background: #ecfdf5; color: #10b981; border: 1px solid #10b981; }
    .bg-soft-cancelled { background: #fee2e2; color: #dc2626; }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .summary-label {
        color: #64748b;
        font-weight: 600;
    }
    .summary-value {
        font-weight: 800;
        color: #1e293b;
    }
    .grand-total-row {
        border-top: 2px dashed #e2e8f0;
        padding-top: 20px;
        margin-top: 20px;
        font-size: 22px;
        color: #0d9488;
    }

    .btn-premium {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: white !important;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
    }
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3);
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.purchasing.purchase_orders.index') }}" class="btn btn-icon mr-3"><i class="fas fa-arrow-left"></i></a>
                <h1>Detail Purchase Order</h1>
            </div>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.purchase_orders.index') }}">PO</a></div>
                <div class="breadcrumb-item active">{{ $po->po_number }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice-premium shadow-sm">
                <!-- Header Info -->
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h2 style="font-weight: 800; color: #0d9488;">PURCHASE ORDER</h2>
                            <p class="text-muted font-weight-bold" style="letter-spacing: 1px;">NO: {{ $po->po_number }}</p>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="info-label">Supplier</div>
                                <div class="info-value">{{ $po->supplier->name }}</div>
                                <div class="small text-muted">{{ $po->supplier->address }}</div>
                                <div class="small text-muted">{{ $po->supplier->phone }}</div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Gudang Tujuan</div>
                                <div class="info-value">{{ $po->warehouse->name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <div class="mb-4">
                            @php
                                $statusClasses = [
                                    'draft' => 'bg-soft-draft',
                                    'submitted' => 'bg-soft-submitted',
                                    'approved' => 'bg-soft-approved',
                                    'received' => 'bg-soft-received',
                                    'cancelled' => 'bg-soft-cancelled'
                                ];
                            @endphp
                            <span class="badge badge-status {{ $statusClasses[$po->status] }}">{{ $po->status }}</span>
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="info-label">Tanggal Order</div>
                                <div class="info-value">{{ $po->po_date->format('d F Y') }}</div>
                            </div>
                            <div class="col-md-5">
                                <div class="info-label">Estimasi Terima</div>
                                <div class="info-value text-teal">{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d F Y') : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Items -->
                <div class="table-responsive mb-5">
                    <table class="table table-premium">
                        <thead>
                            <tr>
                                <th width="50px">#</th>
                                <th>Deskripsi Item Produk</th>
                                <th width="120px" class="text-center">Qty</th>
                                <th width="180px" class="text-right">Harga Satuan</th>
                                <th width="180px" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $index => $item)
                            <tr>
                                <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $item->product_name }}</div>
                                    <div class="small text-muted">{{ $item->description ?: '-' }}</div>
                                </td>
                                <td class="text-center font-weight-bold">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary -->
                <div class="row">
                    <div class="col-md-7">
                        <div class="info-label">Catatan Order</div>
                        <div class="p-3 bg-light rounded" style="min-height: 100px; border-left: 4px solid #e2e8f0;">
                            {{ $po->notes ?: 'Tidak ada catatan khusus untuk order ini.' }}
                        </div>
                        <div class="mt-4 row">
                            <div class="col-md-6">
                                <div class="info-label">Dibuat Oleh</div>
                                <div class="info-value">{{ $po->creator->name }}</div>
                                <div class="small text-muted">{{ $po->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="summary-item">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Diskon {{ $po->discount_type == 'percentage' ? '('.$po->discount_value.'%)' : '' }}</span>
                            <span class="summary-value text-danger">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Pajak (PPN {{ $po->tax_percentage }}%)</span>
                            <span class="summary-value">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-item grand-total-row">
                            <span class="font-weight-800">Grand Total</span>
                            <span class="font-weight-800">Rp {{ number_format($po->total, 0, ',', '.') }}</span>
                        </div>

                        <div class="mt-5 d-flex justify-content-end">
                            <a href="{{ route('admin.purchasing.purchase_orders.edit', $po->id) }}" class="btn btn-warning px-4 mr-2" style="border-radius: 12px; font-weight: 700;">
                                <i class="fas fa-edit mr-1"></i> Edit PO
                            </a>
                            <a href="{{ route('admin.purchasing.purchase_orders.print', $po->id) }}" target="_blank" class="btn btn-premium px-4">
                                <i class="fas fa-print mr-1"></i> Print PO
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
