@extends('master')
@section('title', 'Alokasi Pelunasan per PO')
@section('content')
<div class="main-content">
    @php
        $productLabel = function ($product) {
            $brand = trim((string) ($product->merek_name ?? ''));
            $name = trim((string) ($product->product_name ?? ''));
            $netto = trim((string) ($product->netto_value ?? ''));
            $uom = trim((string) ($product->satuan ?? ''));
            $label = trim(implode(' ', array_filter([$brand, $name, $netto, $uom])));
            return $label !== '' ? $label : 'Produk';
        };
        $formatQty = fn($qty) => rtrim(rtrim(number_format((float) $qty, 2, ',', '.'), '0'), ',');
    @endphp

    <section class="section sett-page sett-allocation-page">
        {{-- ===== PAGE HEADER ===== --}}
        <div class="sett-hero">
            <div class="d-flex align-items-center" style="position:relative;z-index:1;">
                <div class="sett-hero-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h1>Alokasi Pelunasan per PO</h1>
                    <p>{{ $selectedCount }} transaksi terpilih · FIFO berdasarkan PO tertua · Pembayaran diproses per PO</p>
                </div>
                <div class="ml-auto">
                    <a href="{{ route('admin.finance.settlement.index', array_filter($filters)) }}" class="btn-sett-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Transaksi
                    </a>
                </div>
            </div>
        </div>

        <div class="section-body">
        {{-- ===== STAT CARDS ===== --}}
        <div class="sett-summary-grid">
            <div class="sett-stat-card blue">
                <div class="sett-stat-icon blue">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div class="sett-stat-info">
                    <div class="sett-stat-label">Transaksi Dipilih</div>
                    <div class="sett-stat-value">{{ $selectedCount }} transaksi</div>
                </div>
            </div>
            <div class="sett-stat-card teal">
                <div class="sett-stat-icon teal">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="sett-stat-info">
                    <div class="sett-stat-label">PO Terdampak</div>
                    <div class="sett-stat-value">{{ $purchaseOrders->count() }} PO</div>
                </div>
            </div>
            <div class="sett-stat-card red">
                <div class="sett-stat-icon red">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="sett-stat-info">
                    <div class="sett-stat-label">Total Pelunasan</div>
                    <div class="sett-stat-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- ===== UNMATCHED WARNING ===== --}}
        @if($unmatchedQty > 0)
            <div class="alert alert-warning border-warning mb-3">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 mr-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark">Tidak semua transaksi dapat dialokasikan</div>
                        <div class="small text-dark">
                            {{ $formatQty($unmatchedQty) }} pcs dari transaksi terpilih belum memiliki kapasitas PO yang dapat
                            dilunasi, sehingga tidak masuk dalam total pembayaran.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== TABS ===== --}}
        <div class="sett-table-card">
            <div class="card-header bg-white border-bottom-0 py-3">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active show" data-toggle="tab" href="#products-tab" role="tab" aria-controls="products-tab">
                            <i class="fas fa-box mr-2"></i> Pelunasan per Produk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#purchase-orders-tab" role="tab" aria-controls="purchase-orders-tab">
                            <i class="fas fa-file-invoice mr-2"></i> Pelunasan per PO
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content p-4">
                {{-- ================= PER PRODUK ================= --}}
                <div class="tab-pane fade show active" id="products-tab" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 sett-allocation-table sett-products-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="po-column">No. PO</th>
                                    <th class="supplier-column">Supplier</th>
                                    <th class="product-column">Produk</th>
                                    <th class="qty-column text-center">Qty</th>
                                    <th class="hpp-column text-right">HPP per PO</th>
                                    <th class="total-column text-right">Total Pelunasan</th>
                                    <th class="action-column text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $purchaseOrder)
                                    @foreach($purchaseOrder->products as $product)
                                        <tr>
                                            <td class="font-weight-bold text-dark" style="overflow-wrap: anywhere;">
                                                {{ $product->po_number }}
                                            </td>
                                            <td class="text-dark">
                                                <span class="badge badge-light border">{{ $product->supplier_name }}</span>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $productLabel($product) }}</div>
                                            </td>
                                            <td class="text-center qty-cell">
                                                <span class="qty-value">{{ $formatQty($product->qty) }}</span>
                                            </td>
                                            <td class="text-right hpp-cell">
                                                Rp {{ number_format($product->buy_price, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right font-weight-bold text-danger">
                                                Rp {{ number_format($product->subtotal, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary btn-pay-po"
                                                    data-po-id="{{ $product->purchase_order_id }}"
                                                    data-supplier-id="{{ $product->supplier_id }}"
                                                    data-po-number="{{ $product->po_number }}"
                                                    data-supplier-name="{{ $product->supplier_name }}"
                                                    data-product-label="{{ $productLabel($product) }}"
                                                    data-transaction-item-ids="{{ implode(',', $product->transaction_item_ids) }}"
                                                    data-total-amount="{{ $product->subtotal }}"
                                                >
                                                    <i class="fas fa-money-bill-wave mr-1"></i> Bayar Produk
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-30"></i>
                                            <div class="font-weight-bold text-dark">Tidak ada produk yang dapat dilunasi.</div>
                                            <div class="text-muted small">Semua transaksi telah lunas atau tidak memiliki kapasitas PO.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ================= PER PO ================= --}}
                <div class="tab-pane fade" id="purchase-orders-tab" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 sett-allocation-table sett-po-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="po-number-column">No. PO</th>
                                    <th class="po-supplier-column">Supplier</th>
                                    <th class="po-products-column">Produk</th>
                                    <th class="po-details-column">Rincian Transaksi</th>
                                    <th class="po-total-column text-right">Total Pelunasan</th>
                                    <th class="po-action-column text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $purchaseOrder)
                                    <tr>
                                        <td class="font-weight-bold text-dark align-middle" style="overflow-wrap: anywhere;">
                                            {{ $purchaseOrder->po_number }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-light border">{{ $purchaseOrder->supplier_name }}</span>
                                        </td>
                                        <td class="align-middle">
                                            @foreach($purchaseOrder->products as $product)
                                                <div class="po-product-line">
                                                    <span class="po-product-name">{{ $productLabel($product) }}</span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="align-middle">
                                            @foreach($purchaseOrder->products as $product)
                                                <div class="po-detail-line">
                                                    <span class="po-calculation">{{ $formatQty($product->qty) }} × Rp {{ number_format($product->buy_price, 0, ',', '.') }}</span>
                                                    <strong class="po-subtotal">Rp {{ number_format($product->subtotal, 0, ',', '.') }}</strong>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="text-right font-weight-bold text-danger align-middle po-total-cell">
                                            Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center align-middle po-action-cell">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary btn-pay-po po-pay-button"
                                                data-po-id="{{ $purchaseOrder->purchase_order_id }}"
                                                data-supplier-id="{{ $purchaseOrder->supplier_id }}"
                                                data-po-number="{{ $purchaseOrder->po_number }}"
                                                data-supplier-name="{{ $purchaseOrder->supplier_name }}"
                                                data-product-label="{{ $purchaseOrder->products->map(fn($p) => $productLabel($p))->implode(', ') }}"
                                                data-transaction-item-ids="{{ implode(',', $purchaseOrder->transaction_item_ids) }}"
                                                data-total-amount="{{ $purchaseOrder->total_amount }}"
                                            >
                                                <i class="fas fa-money-bill-wave mr-1"></i> Bayar PO
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-30"></i>
                                            <div class="font-weight-bold text-dark">Tidak ada PO yang dapat dilunasi.</div>
                                            <div class="text-muted small">Semua PO telah dibayar atau tidak memiliki kapasitas.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="thead-light bg-light">
                                <tr>
                                    <th colspan="4" class="text-right font-weight-bold text-dark">Total Semua PO</th>
                                    <th class="text-right text-danger font-weight-bold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="poPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <form id="po-payment-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="purchase_order_id" id="modal-po-id">
                <input type="hidden" name="supplier_id" id="modal-supplier-id">
                <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                <div id="modal-transaction-ids"></div>

                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title font-weight-bold text-dark">
                        <i class="fas fa-money-bill-wave mr-2 text-teal"></i>Konfirmasi Pembayaran PO
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 bg-teal-light rounded">
                        <div id="modal-po-label" class="font-weight-bold text-dark"></div>
                        <div id="modal-product-label" class="small mt-1 text-muted"></div>
                        <hr class="my-3 border-teal-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total pembayaran:</span>
                            <strong id="modal-total-amount" class="text-danger h5">Rp 0</strong>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Bukti Pembayaran</label>
                        <input type="file" name="payment_proof" class="form-control-file" accept="image/jpeg,image/png,application/pdf">
                        <small class="form-text text-muted">JPG, PNG, atau PDF; maksimal 2 MB.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan jika perlu..."></textarea>
                    </div>
                    <div class="text-danger small mt-2" id="po-payment-error" style="display:none;"></div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal btn-payment-confirm" id="btn-confirm-po-payment">
                        <i class="fas fa-check mr-1"></i> Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    :root {
        --teal-600: #2a9d8f;
        --teal-50: #e9f7f6;
        --border-teal-light: #b2dfdb;
    }
    .border-left-teal { border-left: 4px solid #2a9d8f !important; }
    .border-left-info { border-left: 4px solid #17a2b8 !important; }
    .border-left-danger { border-left: 4px solid #dc3545 !important; }
    .text-teal { color: #2a9d8f !important; }
    .btn-teal { background-color: #2a9d8f; border-color: #2a9d8f; color: #fff; }
    .btn-teal:hover { background-color: #21867a; border-color: #21867a; color: #fff; }
    .bg-teal-light { background-color: #e9f7f6 !important; }
    .border-teal-light { border-color: #b2dfdb !important; }
    .badge-light.border { border: 1px solid #dee2e6; background: #fff; color: #495057; }
    .badge-info { background: #17a2b8; color: #fff; }
    .text-muted { color: #6c757d !important; }
    .card-header-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.25rem;
        font-weight: 600;
    }
    .card-header-tabs .nav-link.active,
    .card-header-tabs .nav-link.show {
        color: #2a9d8f;
        border-bottom-color: #2a9d8f;
        background: transparent;
    }
    .card-header-tabs .nav-link:hover {
        color: #2a9d8f;
        background: transparent;
    }
    .thead-light th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        color: #495057;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .btn-payment-confirm:disabled {
        opacity: 0.7;
        pointer-events: none;
    }
    .opacity-50 { opacity: 0.5; }
    .opacity-30 { opacity: 0.3; }
    .sett-allocation-page { font-family: 'Inter', 'Segoe UI', sans-serif; }
    .sett-allocation-page .sett-hero {
        background: linear-gradient(135deg, #0f766e 0%, #115e59 60%, #0c4a6e 100%);
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(13,148,136,.25);
    }
    .sett-allocation-page .sett-hero::after {
        content: '';
        position: absolute;
        width: 220px;
        height: 220px;
        right: -70px;
        top: -110px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        pointer-events: none;
    }
    .sett-allocation-page .sett-hero-icon {
        width: 52px;
        height: 52px;
        margin-right: 18px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: rgba(255,255,255,.15);
        font-size: 22px;
        flex-shrink: 0;
    }
    .sett-allocation-page .sett-hero h1 {
        color: #fff;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -.4px;
        margin: 0;
    }
    .sett-allocation-page .sett-hero p {
        color: rgba(255,255,255,.78);
        font-size: 13px;
        margin: 2px 0 0;
    }
    .sett-allocation-page .sett-hero .btn-sett-secondary {
        position: relative;
        z-index: 1;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        color: #0f766e;
        background: #fff;
        border: 1px solid rgba(255,255,255,.7);
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .12);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .sett-allocation-page .sett-hero .btn-sett-secondary:hover {
        color: #115e59;
        background: #f0fdfa;
        box-shadow: 0 7px 18px rgba(15, 23, 42, .18);
        transform: translateY(-1px);
        text-decoration: none;
    }
    .sett-allocation-page .sett-hero .btn-sett-secondary:focus {
        outline: 3px solid rgba(255,255,255,.45);
        outline-offset: 2px;
    }
    .sett-allocation-page .sett-table-card {
        background: #fff;
        border: 1.5px solid #f1f5f9;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .sett-allocation-page .sett-allocation-table {
        min-width: 900px;
        font-size: 13px;
    }
    .sett-allocation-page .sett-products-table {
        min-width: 980px;
        table-layout: fixed;
    }
    .sett-allocation-page .sett-products-table .po-column { width: 16%; }
    .sett-allocation-page .sett-products-table .supplier-column { width: 14%; }
    .sett-allocation-page .sett-products-table .product-column { width: 28%; }
    .sett-allocation-page .sett-products-table .qty-column { width: 7%; }
    .sett-allocation-page .sett-products-table .hpp-column { width: 11%; }
    .sett-allocation-page .sett-products-table .total-column { width: 11%; }
    .sett-allocation-page .sett-products-table .action-column { width: 13%; }
    .sett-allocation-page .sett-po-table {
        min-width: 1180px;
        table-layout: fixed;
    }
    .sett-allocation-page .sett-po-table .po-number-column { width: 15%; }
    .sett-allocation-page .sett-po-table .po-supplier-column { width: 14%; }
    .sett-allocation-page .sett-po-table .po-products-column { width: 27%; }
    .sett-allocation-page .sett-po-table .po-details-column { width: 18%; }
    .sett-allocation-page .sett-po-table .po-total-column { width: 12%; }
    .sett-allocation-page .sett-po-table .po-action-column { width: 14%; }
    .sett-allocation-page .sett-po-table .po-number-column,
    .sett-allocation-page .sett-po-table .po-supplier-column,
    .sett-allocation-page .sett-po-table .po-total-column,
    .sett-allocation-page .sett-po-table .po-action-column {
        white-space: nowrap;
    }
    .sett-allocation-page .sett-po-table .po-product-line,
    .sett-allocation-page .sett-po-table .po-detail-line {
        min-height: 48px;
        padding: 9px 0;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
    }
    .sett-allocation-page .sett-po-table .po-product-line:last-child,
    .sett-allocation-page .sett-po-table .po-detail-line:last-child {
        border-bottom: 0;
    }
    .sett-allocation-page .sett-po-table .po-product-name {
        color: #1e293b;
        font-weight: 600;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }
    .sett-allocation-page .sett-po-table .po-detail-line {
        justify-content: space-between;
        gap: 12px;
    }
    .sett-allocation-page .sett-po-table .po-calculation {
        color: #64748b;
        white-space: nowrap;
    }
    .sett-allocation-page .sett-po-table .po-subtotal {
        color: #1e293b;
        white-space: nowrap;
        text-align: right;
    }
    .sett-allocation-page .sett-po-table .po-total-cell {
        font-size: 13px;
        white-space: nowrap;
    }
    .sett-allocation-page .sett-po-table .po-action-cell {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    .sett-allocation-page .sett-po-table .po-pay-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 108px;
        padding: 6px 12px;
        white-space: nowrap;
    }
    .sett-allocation-page .sett-products-table .qty-cell {
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .sett-allocation-page .sett-products-table .qty-value {
        color: #1e293b;
        font-weight: 700;
    }
    .sett-allocation-page .sett-products-table .hpp-cell {
        white-space: nowrap;
        font-size: 12px;
        color: #64748b;
    }
    .sett-allocation-page .sett-products-table .po-column,
    .sett-allocation-page .sett-products-table .supplier-column,
    .sett-allocation-page .sett-products-table .qty-column,
    .sett-allocation-page .sett-products-table .hpp-column,
    .sett-allocation-page .sett-products-table .total-column,
    .sett-allocation-page .sett-products-table .action-column {
        white-space: nowrap;
    }
    .sett-allocation-page .sett-allocation-table thead th {
        padding: 14px 12px;
        color: #475569;
        background: #f8fafc;
        border-top: 0;
        border-bottom: 1.5px solid #e2e8f0;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
        vertical-align: middle;
    }
    .sett-allocation-page .sett-allocation-table tbody td {
        padding: 13px 12px;
        border-top: 0;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .sett-allocation-page .sett-allocation-table tbody tr:hover { background: #f0fdfa; }
    .sett-allocation-page .card-header-tabs .nav-link {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .sett-allocation-page .card-header-tabs .nav-link.active {
        color: #0f766e;
        border-bottom: 2px solid #0d9488;
    }
    @media (max-width: 767.98px) {
        .sett-allocation-page .sett-hero { padding: 20px; margin-bottom: 20px; }
        .sett-allocation-page .sett-hero .d-flex { align-items: flex-start !important; flex-wrap: wrap; }
        .sett-allocation-page .sett-hero .ml-auto { width: 100%; margin: 16px 0 0 !important; }
        .sett-allocation-page .sett-hero .btn-sett-secondary { justify-content: center; width: 100%; }
        .sett-allocation-page .sett-hero h1 { font-size: 19px; }
        .sett-allocation-page .sett-hero p { max-width: 100%; line-height: 1.5; }
        .sett-allocation-page .tab-content { padding: 12px !important; }
    }

    /* ===== SUMMARY CARDS (matching settlement-report) ===== */
    :root {
        --slate-50:  #f8fafc;
        --slate-100: #f1f5f9;
        --slate-400: #94a3b8;
        --slate-600: #475569;
        --slate-800: #1e293b;
        --blue-100: #dbeafe;
        --blue-600: #2563eb;
        --teal-600: #0d9488;
        --teal-100: #ccfbf1;
        --red-100: #fee2e2;
        --red-600: #dc2626;
    }
    .sett-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 22px; }
    .sett-stat-card {
        background: #fff; border-radius: 16px;
        border: 1.5px solid var(--slate-100);
        box-shadow: 0 4px 20px rgba(0,0,0,.04);
        padding: 20px 22px;
        display: flex; align-items: flex-start; gap: 16px;
        transition: transform .2s, box-shadow .2s;
        position: relative; overflow: hidden;
    }
    .sett-stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.09); }
    .sett-stat-card::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0;
        height: 3px;
    }
    .sett-stat-card.blue::after   { background: linear-gradient(90deg, var(--blue-600), #60a5fa); }
    .sett-stat-card.red::after    { background: linear-gradient(90deg, var(--red-600), #f87171); }
    .sett-stat-card.teal::after   { background: linear-gradient(90deg, var(--teal-600), #34d399); }
    .sett-stat-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .sett-stat-icon.blue  { background: var(--blue-100);  color: var(--blue-600); }
    .sett-stat-icon.red   { background: var(--red-100);   color: var(--red-600); }
    .sett-stat-icon.teal  { background: var(--teal-100);  color: var(--teal-600); }
    .sett-stat-info { flex: 1; min-width: 0; }
    .sett-stat-label { font-size: 11px; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 4px; }
    .sett-stat-value { font-size: 22px; font-weight: 800; color: var(--slate-800); line-height: 1.1; }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    $('.btn-pay-po').on('click', function() {
        const $button = $(this);
        const rawIds = String($button.data('transaction-item-ids') || '');
        const transactionItemIds = rawIds.split(',').map(function(id) {
            return id.trim();
        }).filter(function(id) {
            return id !== '';
        });

        if (transactionItemIds.length === 0) {
            alert('Daftar transaksi tidak valid. Muat ulang halaman.');
            return;
        }

        $('#modal-po-id').val($button.data('po-id'));
        $('#modal-supplier-id').val($button.data('supplier-id'));
        $('#modal-po-label').text($button.data('po-number') + ' — ' + $button.data('supplier-name'));
        $('#modal-product-label').text('Produk: ' + ($button.data('product-label') || '-'));
        $('#modal-total-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format($button.data('total-amount')));
        $('#po-payment-error').hide().text('');
        $('#modal-transaction-ids').empty();
        transactionItemIds.forEach(function(id) {
            $('<input>', { type: 'hidden', name: 'transaction_item_ids[]', value: id }).appendTo('#modal-transaction-ids');
        });
        $('#poPaymentModal').modal('show');
    });

    $('#po-payment-form').on('submit', function(event) {
        event.preventDefault();
        const $button = $('#btn-confirm-po-payment').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        $('#po-payment-error').hide().text('');

        $.ajax({
            url: "{{ route('admin.finance.settlement.pay') }}",
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#poPaymentModal').modal('hide');
                    window.location.href = response.redirect || "{{ route('admin.finance.settlement.index') }}";
                } else {
                    $('#po-payment-error').text(response.message || 'Pembayaran gagal diproses.').show();
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memproses pembayaran.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors)[0][0];
                }
                $('#po-payment-error').text(message).show();
            },
            complete: function() {
                $button.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Konfirmasi Pembayaran');
            }
        });
    });
});
</script>
@endpush
@endsection
