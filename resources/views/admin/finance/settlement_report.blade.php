@extends('master')
@section('title', 'Laporan Pelunasan Supplier')
@section('content')
<div class="main-content">
<style>
    /* ===== ROOT VARIABLES ===== */
    :root {
        --teal-600: #0d9488;
        --teal-700: #0f766e;
        --teal-800: #115e59;
        --teal-50:  #f0fdfa;
        --teal-100: #ccfbf1;
        --slate-50:  #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-400: #94a3b8;
        --slate-600: #475569;
        --slate-700: #334155;
        --slate-800: #1e293b;
        --red-100: #fee2e2;
        --red-600: #dc2626;
        --red-700: #b91c1c;
        --amber-100: #fef3c7;
        --amber-600: #d97706;
        --blue-100: #dbeafe;
        --blue-600: #2563eb;
    }

    /* ===== PAGE WRAPPER ===== */
    .sett-page { font-family: 'Inter', 'Segoe UI', sans-serif; }

    /* ===== HERO HEADER ===== */
    .sett-hero {
        background: linear-gradient(135deg, var(--teal-700) 0%, var(--teal-800) 60%, #0c4a6e 100%);
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(13,148,136,.25);
    }
    .sett-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 260px; height: 260px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
    }
    .sett-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: 40%;
        width: 200px; height: 200px;
        background: rgba(255,255,255,.04);
        border-radius: 50%;
    }
    .sett-hero-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.15);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; color: #fff;
        backdrop-filter: blur(8px);
        margin-right: 18px;
        flex-shrink: 0;
    }
    .sett-hero h1 {
        color: #fff !important;
        font-size: 22px; font-weight: 800;
        letter-spacing: -.4px; margin: 0;
    }
    .sett-hero p { color: rgba(255,255,255,.7); margin: 2px 0 0; font-size: 13px; }
    .sett-breadcrumb { display: flex; gap: 8px; align-items: center; }
    .sett-breadcrumb a { color: rgba(255,255,255,.65); font-size: 12px; text-decoration: none; }
    .sett-breadcrumb a:hover { color: #fff; }
    .sett-breadcrumb span { color: rgba(255,255,255,.4); font-size: 11px; }
    .sett-breadcrumb .active { color: rgba(255,255,255,.9); font-size: 12px; }

    /* ===== ACTION BUTTONS ROW ===== */
    .sett-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 22px; }
    .btn-sett-primary {
        background: linear-gradient(135deg, var(--teal-600), var(--teal-800));
        color: #fff; border: none; border-radius: 10px;
        padding: 9px 18px; font-weight: 700; font-size: 13px;
        display: inline-flex; align-items: center; gap: 7px;
        box-shadow: 0 4px 14px rgba(13,148,136,.3);
        transition: all .2s; cursor: pointer; text-decoration: none;
    }
    .btn-sett-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(13,148,136,.4); color: #fff; text-decoration: none; }
    .btn-sett-secondary {
        background: #fff; color: var(--slate-700); border: 1.5px solid var(--slate-200);
        border-radius: 10px; padding: 9px 18px; font-weight: 600; font-size: 13px;
        display: inline-flex; align-items: center; gap: 7px;
        transition: all .2s; cursor: pointer; text-decoration: none;
    }
    .btn-sett-secondary:hover { background: var(--slate-50); border-color: var(--slate-300, #cbd5e1); color: var(--slate-800); transform: translateY(-1px); text-decoration: none; }
    .btn-sett-success {
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: #fff; border: none; border-radius: 10px;
        padding: 9px 18px; font-weight: 700; font-size: 13px;
        display: inline-flex; align-items: center; gap: 7px;
        box-shadow: 0 4px 12px rgba(22,163,74,.25); transition: all .2s; text-decoration: none;
    }
    .btn-sett-success:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(22,163,74,.35); color: #fff; text-decoration: none; }
    .btn-sett-danger {
        background: linear-gradient(135deg, var(--red-600), #9b1c1c);
        color: #fff; border: none; border-radius: 10px;
        padding: 9px 18px; font-weight: 700; font-size: 13px;
        display: inline-flex; align-items: center; gap: 7px;
        box-shadow: 0 4px 12px rgba(220,38,38,.25); transition: all .2s; text-decoration: none;
    }
    .btn-sett-danger:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220,38,38,.35); color: #fff; text-decoration: none; }
    .btn-pay-badge {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff; border: none; border-radius: 10px;
        padding: 9px 18px; font-weight: 700; font-size: 13px;
        display: none; align-items: center; gap: 7px;
        box-shadow: 0 4px 12px rgba(245,158,11,.35); transition: all .2s; cursor: pointer;
        animation: pulse-pay 1s ease infinite alternate;
    }
    .btn-pay-badge:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(245,158,11,.5); color: #fff; }
    @keyframes pulse-pay {
        from { box-shadow: 0 4px 12px rgba(245,158,11,.35); }
        to   { box-shadow: 0 8px 28px rgba(245,158,11,.6); }
    }

    /* ===== FILTER CARD ===== */
    .sett-filter-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid var(--slate-100);
        box-shadow: 0 4px 24px rgba(0,0,0,.04);
        padding: 22px 24px;
        margin-bottom: 22px;
    }
    .sett-filter-card .filter-title {
        font-size: 12px; font-weight: 700; color: var(--teal-600);
        text-transform: uppercase; letter-spacing: .8px;
        margin-bottom: 14px; display: flex; align-items: center; gap: 7px;
    }
    .sett-filter-card label {
        font-size: 11px; font-weight: 700; color: var(--slate-400);
        text-transform: uppercase; letter-spacing: .6px; margin-bottom: 5px;
        display: block;
    }
    .sett-filter-card .form-control {
        border-radius: 10px !important; border: 1.5px solid var(--slate-200) !important;
        height: 42px !important; font-size: 13px !important;
        color: var(--slate-700) !important; transition: border-color .2s;
    }
    .sett-filter-card .form-control:focus {
        border-color: var(--teal-600) !important;
        box-shadow: 0 0 0 3px rgba(13,148,136,.12) !important;
    }
    .btn-filter-apply {
        background: linear-gradient(135deg, var(--teal-600), var(--teal-700));
        color: #fff; border: none; border-radius: 10px;
        height: 42px; padding: 0 24px; font-weight: 700; font-size: 13px;
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 7px;
        box-shadow: 0 4px 12px rgba(13,148,136,.25); transition: all .2s;
    }
    .btn-filter-apply:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(13,148,136,.35); }

    /* ===== SUMMARY CARDS ===== */
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
    .sett-stat-value.small-val { font-size: 15px; }

    /* ===== SUPPLIER BANK CARD ===== */
    .sett-bank-card {
        background: linear-gradient(135deg, var(--teal-50), #e0fdf4);
        border: 1.5px solid var(--teal-100);
        border-radius: 16px; padding: 20px 22px;
        position: relative; overflow: hidden;
    }
    .sett-bank-card::before {
        content: '';
        position: absolute; top: -30px; right: -30px;
        width: 100px; height: 100px;
        background: rgba(13,148,136,.07); border-radius: 50%;
    }
    .sett-bank-label { font-size: 11px; font-weight: 700; color: var(--teal-700); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
    .sett-bank-name { font-size: 13px; font-weight: 600; color: var(--slate-700); }
    .sett-bank-number { font-size: 20px; font-weight: 800; color: var(--teal-700); letter-spacing: 1px; margin: 2px 0; }
    .sett-bank-holder { font-size: 12px; font-weight: 600; color: var(--slate-400); }

    /* ===== DATA TABLE ===== */
    .sett-table-card {
        background: #fff; border-radius: 16px;
        border: 1.5px solid var(--slate-100);
        box-shadow: 0 4px 24px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .sett-table-card-header {
        padding: 18px 24px;
        border-bottom: 1.5px solid var(--slate-100);
        display: flex; align-items: center; justify-content: space-between;
        background: var(--slate-50);
    }
    .sett-table-card-header h4 {
        font-size: 15px; font-weight: 700; color: var(--slate-800); margin: 0;
        display: flex; align-items: center; gap: 8px;
    }
    .sett-table-card-header h4 i { color: var(--teal-600); }

    #report-table { font-size: 13px !important; border: none !important; }
    #report-table thead th {
        background: var(--slate-50) !important;
        color: var(--slate-600) !important;
        font-weight: 700 !important; font-size: 11px !important;
        text-transform: uppercase; letter-spacing: .5px;
        padding: 14px 14px !important; border-top: none !important;
        border-bottom: 1.5px solid var(--slate-100) !important;
    }
    #report-table tbody tr { transition: background .15s; }
    #report-table tbody tr:hover { background: var(--teal-50) !important; }
    #report-table tbody td {
        padding: 14px !important; vertical-align: middle !important;
        border-bottom: 1px solid var(--slate-100) !important;
        border-top: none !important;
    }

    /* Cells */
    .sett-product-name { font-weight: 700; color: var(--slate-800); font-size: 13px; }
    .sett-product-sku  { font-size: 11px; color: var(--slate-400); margin-top: 2px; font-family: monospace; }
    .badge-supplier {
        background: var(--teal-50); color: var(--teal-700);
        border: 1px solid var(--teal-100); padding: 3px 10px;
        border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap;
    }
    .badge-no-supplier {
        background: var(--slate-100); color: var(--slate-400);
        border: 1px solid var(--slate-200); padding: 3px 10px;
        border-radius: 20px; font-size: 11px; font-style: italic;
    }
    .qty-pill {
        background: var(--blue-100); color: var(--blue-600);
        padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 12px;
        display: inline-block;
    }
    .cost-text { font-weight: 800; color: var(--red-700); font-size: 13px; }
    .hpp-text  { font-weight: 600; color: var(--slate-600); }

    .btn-detail-row {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: #fff; border: none; border-radius: 8px;
        padding: 6px 14px; font-size: 12px; font-weight: 600;
        display: inline-flex; align-items: center; gap: 5px;
        transition: all .2s; cursor: pointer;
    }
    .btn-detail-row:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(6,182,212,.35); }

    /* Checkbox */
    .check-item, #checkAll { width: 16px; height: 16px; accent-color: var(--teal-600); cursor: pointer; }

    /* ===== MODAL OVERRIDES ===== */
    .modal-content {
        border-radius: 20px !important;
        border: none !important;
        box-shadow: 0 30px 80px rgba(0,0,0,.18) !important;
        overflow: hidden;
    }
    .modal-header {
        background: linear-gradient(135deg, var(--teal-700), var(--teal-800));
        color: #fff; padding: 20px 26px !important; border: none !important;
    }
    .modal-header .modal-title { font-weight: 800; font-size: 16px; }
    .modal-header .close { color: rgba(255,255,255,.8) !important; opacity: 1 !important; font-size: 22px; text-shadow: none; }
    .modal-header .close:hover { color: #fff !important; }
    .modal-body { padding: 24px 26px !important; }
    .modal-footer { padding: 16px 26px !important; border-top: 1.5px solid var(--slate-100) !important; background: var(--slate-50); }

    /* Payment modal header distinct */
    #paymentModal .modal-header {
        background: linear-gradient(135deg, #7c3aed, #4c1d95);
    }

    /* Tab styling */
    .nav-tabs { border-bottom: 2px solid var(--slate-100) !important; }
    .nav-tabs .nav-link {
        border: none !important; color: var(--slate-400) !important;
        font-weight: 700; font-size: 13px; padding: 10px 18px;
        border-radius: 8px 8px 0 0; transition: all .2s;
    }
    .nav-tabs .nav-link.active {
        color: var(--teal-700) !important; background: transparent !important;
        border-bottom: 2px solid var(--teal-600) !important; margin-bottom: -2px;
    }
    .nav-tabs .nav-link:hover:not(.active) { color: var(--slate-700) !important; background: var(--slate-50) !important; }

    /* Detail table inside modal */
    .detail-inner-table { font-size: 12.5px !important; }
    .detail-inner-table thead th {
        background: var(--slate-50) !important; color: var(--slate-600) !important;
        font-weight: 700 !important; font-size: 11px !important;
        text-transform: uppercase; letter-spacing: .5px;
        padding: 10px 12px !important;
    }
    .detail-inner-table tbody td { padding: 10px 12px !important; vertical-align: middle !important; }

    /* Form group in modal */
    .modal-form-label {
        font-size: 11px; font-weight: 700; color: var(--slate-600);
        text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px;
    }
    .modal-form-control {
        border-radius: 10px !important; border: 1.5px solid var(--slate-200) !important;
        height: 42px !important; font-size: 13px !important;
        transition: border-color .2s !important;
    }
    .modal-form-control:focus {
        border-color: var(--teal-600) !important;
        box-shadow: 0 0 0 3px rgba(13,148,136,.12) !important;
    }
    textarea.modal-form-control { height: auto !important; }
    .total-badge-modal {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1.5px solid #fecaca; border-radius: 12px;
        padding: 14px 18px; margin-top: 6px;
    }
    .total-badge-modal .label { font-size: 11px; font-weight: 700; color: var(--red-600); text-transform: uppercase; letter-spacing: .6px; }
    .total-badge-modal .value { font-size: 22px; font-weight: 800; color: var(--red-700); }

    /* Processing overlay */
    .dataTables_processing {
        background: rgba(255,255,255,.92) !important;
        border-radius: 10px !important;
        border: 1.5px solid var(--slate-200) !important;
        box-shadow: 0 8px 24px rgba(0,0,0,.1) !important;
        color: var(--teal-700) !important; font-weight: 700 !important;
    }
</style>

<section class="section sett-page">

    {{-- ===== HERO HEADER ===== --}}
    <div class="sett-hero">
        <div class="d-flex align-items-center" style="position:relative;z-index:1;">
            <div class="sett-hero-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1>Laporan Pelunasan Supplier</h1>
                <p>Monitoring HPP & status pembayaran ke supplier / pabrik</p>
            </div>
            <div class="ml-auto sett-breadcrumb d-none d-md-flex">
                <a href="{{ url('admin') }}">Dashboard</a>
                <span>/</span>
                <a href="#">Finance</a>
                <span>/</span>
                <span class="active">Pelunasan</span>
            </div>
        </div>
    </div>

    <div class="section-body">

        {{-- ===== TOP ACTION BUTTONS ===== --}}
        <div class="sett-actions">
            <button type="button" id="btn-pay-selected" class="btn-pay-badge">
                <i class="fas fa-money-bill-wave"></i>
                Bayar Terpilih
                <span id="pay-count-badge" class="ml-1" style="background:rgba(0,0,0,.1);border-radius:20px;padding:1px 8px;font-size:11px;">0</span>
            </button>
            <a href="{{ route('admin.finance.settlement.payment_history') }}" class="btn-sett-secondary">
                <i class="fas fa-history"></i> Riwayat Pembayaran
            </a>
            <a href="#" id="btn-export-excel" class="btn-sett-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="#" id="btn-export-pdf" class="btn-sett-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>

        {{-- ===== FILTER CARD ===== --}}
        <div class="sett-filter-card">
            <div class="filter-title">
                <i class="fas fa-filter" style="color:var(--teal-600)"></i>
                Filter Laporan
            </div>
            <form id="filter-form">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label>Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', date('Y-m-01')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Tanggal Selesai</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Supplier / Pabrik</label>
                            <select class="form-control select2" id="supplier_id" name="supplier_id">
                                <option value="">Semua Supplier</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn-filter-apply">
                            <i class="fas fa-sync-alt"></i> Update Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="sett-summary-grid" id="summary-grid">
            <div class="sett-stat-card blue">
                <div class="sett-stat-icon blue">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="sett-stat-info">
                    <div class="sett-stat-label">Total Produk Terjual</div>
                    <div class="sett-stat-value" id="summary-total-qty">—</div>
                </div>
            </div>
            <div class="sett-stat-card red">
                <div class="sett-stat-icon red">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="sett-stat-info">
                    <div class="sett-stat-label">Total Pelunasan (HPP)</div>
                    <div class="sett-stat-value" id="summary-total-cost" style="font-size:18px;">—</div>
                </div>
            </div>
            <div class="sett-stat-card teal" id="supplier-info-wrapper" style="display:none;">
                <div class="sett-stat-icon teal">
                    <i class="fas fa-university"></i>
                </div>
                <div class="sett-stat-info">
                    <div class="sett-stat-label">Rekening Supplier</div>
                    <div class="sett-bank-name" id="supplier-bank-name">—</div>
                    <div class="sett-bank-number" id="supplier-account-no">—</div>
                    <div class="sett-bank-holder" id="supplier-account-name">—</div>
                </div>
            </div>
        </div>

        {{-- ===== DATA TABLE CARD ===== --}}
        <div class="sett-table-card">
            <div class="sett-table-card-header">
                <h4><i class="fas fa-table"></i> Daftar Item Belum Lunas</h4>
                <span id="table-range-label" class="badge" style="background:var(--teal-50);color:var(--teal-700);font-size:11px;padding:5px 12px;border-radius:20px;font-weight:700;border:1px solid var(--teal-100);"></span>
            </div>
            <div class="table-responsive px-3 pt-2 pb-3">
                <table class="table" id="report-table" width="100%">
                    <thead>
                        <tr>
                            <th width="30px" class="text-center">
                                <input type="checkbox" id="checkAll" title="Pilih semua">
                            </th>
                            <th width="40px">#</th>
                            <th>Nama Produk</th>
                            <th>Supplier</th>
                            <th class="text-right">HPP Satuan</th>
                            <th class="text-center">Total Terjual</th>
                            <th class="text-right">Total HPP</th>
                            <th width="90px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</section>
</div>

{{-- ===== MODAL DETAIL TRANSAKSI ===== --}}
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel"><i class="fas fa-chart-bar mr-2"></i>Detail Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered detail-inner-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Transaksi</th>
                                <th>Tanggal</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">HPP Satuan</th>
                                <th class="text-right">Total HPP</th>
                            </tr>
                        </thead>
                        <tbody id="detail-sales-tbody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL PEMBAYARAN ===== --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-payment" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave mr-2"></i>Konfirmasi Pembayaran Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="products" id="pay_products_json">

                    {{-- Highlighted System Total --}}
                    <div class="alert mb-4" style="background: linear-gradient(135deg, var(--teal-50), #e0fdf4); border: 1.5px solid var(--teal-100); border-radius: 12px; padding: 16px 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1" style="color: var(--teal-800); font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Total Tagihan
                                </h6>
                                <div style="font-size: 11px; color: var(--teal-600);">Berdasarkan item yang dipilih</div>
                            </div>
                            <div id="pay_total_amount_display" style="font-size: 24px; font-weight: 900; color: var(--teal-700); letter-spacing: -0.5px;">
                                Rp 0
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="modal-form-label">Nominal Pembayaran Aktual <span class="text-danger">*</span></label>
                        <input type="number" name="actual_payment_amount" id="actual_payment_amount" class="form-control modal-form-control" required placeholder="Masukkan nominal yang dibayarkan">
                    </div>

                    <div class="form-group">
                        <label class="modal-form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-control modal-form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="modal-form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control modal-form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="modal-form-label">Bukti Pembayaran</label>
                        <label for="proof_input" style="display:block; border:2px dashed var(--slate-200);border-radius:10px;padding:18px;text-align:center;cursor:pointer;transition:border-color .2s; margin-bottom: 0;" id="upload-area">
                            <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--slate-400);margin-bottom:6px;"></i>
                            <div style="font-size:12px;color:var(--slate-400);">Klik atau seret file ke sini</div>
                            <div style="font-size:11px;color:var(--slate-400);margin-top:4px;">JPG, PNG, PDF — Maks. 2 MB</div>
                            <input type="file" name="payment_proof" id="proof_input" accept="image/*,.pdf" style="display:none;">
                        </label>
                        <div id="file-preview" class="mt-2" style="display:none; font-size:12px; color:var(--teal-700); font-weight:600;"></div>
                    </div>

                    <div class="form-group">
                        <label class="modal-form-label">Catatan</label>
                        <textarea name="notes" class="form-control modal-form-control" rows="3" style="height:auto!important; border-radius:10px!important;" placeholder="Tambahkan catatan jika perlu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:8px;">Batal</button>
                    <button type="submit" class="btn" id="btn-confirm-pay"
                        style="background:linear-gradient(135deg,#7c3aed,#4c1d95);color:#fff;border-radius:8px;font-weight:700;padding:9px 22px;box-shadow:0 4px 14px rgba(124,58,237,.3);">
                        <i class="fas fa-check mr-1"></i> Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    /* ============================
       DATATABLE INIT
       ============================ */
    var table = $('#report-table').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('admin.finance.settlement.data') }}",
            type: "GET",
            data: function(d) {
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
                d.supplier_id = $('#supplier_id').val();
            }
        },
        columns: [
            {
                // col 0 — checkbox
                data: null, orderable: false, searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    // Build the JSON payload safely — no quotes inside attribute
                    var payload = JSON.stringify({
                        product_id : String(row.product_id),
                        variant_id : row.product_variant_id ? String(row.product_variant_id) : '',
                        cost       : parseFloat(row.raw_total_cost) || 0
                    });
                    // Encode for HTML attribute (replace double-quotes with &quot;)
                    var safePayload = payload.replace(/"/g, '&quot;');
                    return '<input type="checkbox" class="check-item" data-product="' + safePayload + '">';
                }
            },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            {
                // col 2 — product name  (server sends plain text via ->editColumn, we render it)
                data: 'product_name', searchable: false,
                render: function(data, type, row) {
                    // data is the server-rendered HTML string (may contain <div> wrapper)
                    // Strip any outer wrapper and rebuild cleanly
                    var tmp = $('<div>').html(data);
                    var plainName = tmp.text().trim() || data;
                    var sku = row.sku_code
                        ? '<div class="sett-product-sku">' + $('<div>').text(row.sku_code).html() + '</div>'
                        : '';
                    return '<div class="sett-product-name">' + $('<div>').text(plainName).html() + '</div>' + sku;
                }
            },
            {
                // col 3 — supplier badge
                data: 'supplier_name', searchable: false,
                render: function(data) {
                    if (data) {
                        return '<span class="badge-supplier">' + $('<div>').text(data).html() + '</span>';
                    }
                    return '<span class="badge-no-supplier">Tanpa Supplier</span>';
                }
            },
            // col 4 — HPP satuan
            { data: 'buy_price', name: 'buy_price', className: 'text-right hpp-text', searchable: false },
            {
                // col 5 — qty pill
                data: 'total_qty', name: 'total_qty', className: 'text-center', searchable: false,
                render: function(data) {
                    return '<span class="qty-pill">' + data + '</span>';
                }
            },
            {
                // col 6 — total cost
                data: 'total_cost', name: 'total_cost', className: 'text-right', searchable: false,
                render: function(data) {
                    return '<span class="cost-text">' + data + '</span>';
                }
            },
            {
                // col 7 — aksi (detail button built client-side, safe from XSS)
                data: null, name: 'action', orderable: false, searchable: false, className: 'text-center',
                render: function(data, type, row) {
                    var pid  = encodeURIComponent(row.product_id || '');
                    var vid  = encodeURIComponent(row.product_variant_id || '');
                    var plainName = $('<div>').html(row.product_name || '').text();
                    var pname = $('<div>').text(plainName).html();
                    var vname = $('<div>').text(row.variant_name || '').html();
                    return '<button type="button" class="btn-detail-row btn-detail"'
                        + ' data-product-id="' + row.product_id + '"'
                        + ' data-variant-id="' + (row.product_variant_id || '') + '"'
                        + ' data-buy-price="' + (row.raw_buy_price || 0) + '"'
                        + ' data-product-name="' + pname + '"'
                        + ' data-variant-name="' + vname + '">'
                        + '<i class="fas fa-eye"></i> Detail'
                        + '</button>';
                }
            }
        ],
        order: [[5, 'desc']],
        language: {
            processing: '<i class="fas fa-spinner fa-spin mr-2"></i> Memuat data...',
            emptyTable: '<div style="padding:20px;color:var(--slate-400);"><i class="fas fa-inbox" style="font-size:28px;margin-bottom:8px;display:block;"></i>Tidak ada data yang tersedia</div>',
            zeroRecords: '<div style="padding:20px;color:var(--slate-400);"><i class="fas fa-search" style="font-size:28px;margin-bottom:8px;display:block;"></i>Tidak ada hasil yang cocok</div>'
        }
    });

    /* ============================
       FILTER SUBMIT
       ============================ */
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
        updateExportUrls();
        updateRangeLabel();
    });

    /* ============================
       ON XHR — UPDATE SUMMARY
       ============================ */
    table.on('xhr', function() {
        const json    = table.ajax.json();
        if (!json || !json.summary) return;
        const summary = json.summary;

        $('#summary-total-qty').text(
            new Intl.NumberFormat('id-ID').format(summary.total_qty || 0) + ' pcs'
        );
        $('#summary-total-cost').text(
            'Rp ' + new Intl.NumberFormat('id-ID').format(summary.total_cost || 0)
        );

        if (summary.supplier) {
            $('#supplier-info-wrapper').show();
            $('#supplier-bank-name').text(summary.supplier.bank_name || '—');
            $('#supplier-account-no').text(summary.supplier.account_number || '—');
            $('#supplier-account-name').text(summary.supplier.account_holder_name || '—');
        } else {
            $('#supplier-info-wrapper').hide();
        }

        $('#checkAll').prop('checked', false);
        togglePayButton();
        updateRangeLabel();
    });

    function updateRangeLabel() {
        const s = $('#start_date').val();
        const e = $('#end_date').val();
        if (s && e) {
            const fmt = d => new Date(d).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});
            $('#table-range-label').text(fmt(s) + ' – ' + fmt(e));
        }
    }
    updateRangeLabel();

    /* ============================
       CHECKBOX LOGIC
       ============================ */
    $('#checkAll').on('change', function() {
        $('.check-item').prop('checked', $(this).prop('checked'));
        togglePayButton();
    });

    $(document).on('change', '.check-item', function() {
        const allChecked = ($('.check-item').length === $('.check-item:checked').length);
        $('#checkAll').prop('checked', allChecked);
        togglePayButton();
    });

    function togglePayButton() {
        const count = $('.check-item:checked').length;
        if (count > 0) {
            $('#pay-count-badge').text(count);
            $('#btn-pay-selected').css('display', 'inline-flex');
        } else {
            $('#btn-pay-selected').css('display', 'none');
        }
    }

    /* ============================
       OPEN PAYMENT MODAL
       ============================ */
    $('#btn-pay-selected').on('click', function() {
        const selectedProducts = [];
        let totalCost = 0;
        $('.check-item:checked').each(function() {
            // Parse back the JSON payload from the HTML attribute
            var raw = $(this).attr('data-product');
            var prod;
            try { prod = JSON.parse(raw); } catch(e) { prod = $(this).data('product'); }
            if (!prod) return;
            selectedProducts.push({ product_id: prod.product_id, variant_id: prod.variant_id });
            totalCost += parseFloat(prod.cost) || 0;
        });
        if (selectedProducts.length === 0) return;

        // Auto-fill supplier if a single supplier is filtered
        const supplierId = $('#supplier_id').val();
        if (supplierId) {
            $('#paymentModal select[name=supplier_id]').val(supplierId);
        }

        $('#pay_products_json').val(JSON.stringify(selectedProducts));
        $('#pay_total_amount_display').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalCost));
        $('#actual_payment_amount').val(totalCost);
        $('#paymentModal').modal('show');
    });

    /* ============================
       UPLOAD AREA INTERACTION
       ============================ */
    $('#proof_input').on('change', function() {
        if (this.files.length > 0) {
            $('#file-preview').show().html(
                `<i class="fas fa-paperclip mr-1"></i> ${this.files[0].name}`
            );
            $('#upload-area').css('border-color', 'var(--teal-600)');
        }
    });

    /* ============================
       SUBMIT PAYMENT
       ============================ */
    $('#form-payment').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');

        const $btn = $('#btn-confirm-pay').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        $.ajax({
            url: "{{ route('admin.finance.settlement.pay') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    $('#paymentModal').modal('hide');
                    $('#form-payment')[0].reset();
                    $('#file-preview').hide();
                    $('#upload-area').css('border-color', 'var(--slate-200)');
                    table.ajax.reload();
                    showToast('success', 'Pembayaran berhasil disimpan!');
                } else {
                    showToast('error', res.message || 'Gagal memproses pembayaran');
                }
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat memproses pembayaran';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors)[0][0];
                }
                showToast('error', msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Konfirmasi Pembayaran');
            }
        });
    });

    /* ============================
       EXPORT URLS
       ============================ */
    function updateExportUrls() {
        const s = $('#start_date').val(), e = $('#end_date').val(), sid = $('#supplier_id').val();
        const params = `?start_date=${s}&end_date=${e}&supplier_id=${sid}`;
        $('#btn-export-excel').attr('href', "{{ route('admin.finance.settlement.export.excel') }}" + params);
        $('#btn-export-pdf').attr('href', "{{ route('admin.finance.settlement.export.pdf') }}" + params);
    }
    updateExportUrls();

    /* ============================
       DETAIL MODAL
       ============================ */
            $(document).on('click', '.btn-detail', function() {
                const productId = $(this).data('product-id');
                const variantId = $(this).data('variant-id');
                const buyPrice = $(this).data('buy-price');
                const productName = $(this).data('product-name');
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                $('#detailModalLabel').text(`Detail: ${productName}`);
                $('#detail-sales-tbody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                $('#detail-payments-tbody').html('<tr><td colspan="6" class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Loading...</td></tr>');
                $('#detailModal').modal('show');

                $.ajax({
                    url: "{{ route('admin.finance.settlement.detail') }}",
                    method: "GET",
                    data: {
                        product_id: productId,
                        variant_id: variantId,
                        buy_price: buyPrice,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        let totalSalesHpp = 0;
                        let htmlSales = '';
                        if (!response.sales || response.sales.length === 0) {
                            htmlSales = '<tr><td colspan="6" class="text-center">Tidak ada transaksi ditemukan.</td></tr>';
                        } else {
                            response.sales.forEach((item, index) => {
                                const date = new Date(item.created_at).toLocaleString('id-ID', {
                                    day: '2-digit', month: '2-digit', year: 'numeric',
                                    hour: '2-digit', minute: '2-digit'
                                });
                                totalSalesHpp += parseFloat(item.total_hpp || 0);
                                htmlSales += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>
                                            <div class="font-weight-700 text-primary">${item.transaction_code || '#' + item.id}</div>
                                            <div class="small text-muted">${item.invoice_number || ''}</div>
                                        </td>
                                        <td>${date}</td>
                                        <td class="text-center"><span class="qty-pill">${item.qty}</span></td>
                                        <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.buy_price)}</td>
                                        <td class="text-right font-weight-700 text-danger">Rp ${new Intl.NumberFormat('id-ID').format(item.total_hpp)}</td>
                                    </tr>`;
                            });
                            htmlSales += `<tr style="background:var(--slate-50);">
                                <td colspan="5" class="text-right font-weight-700">Total HPP Belum Lunas</td>
                                <td class="text-right font-weight-800 text-danger">Rp ${new Intl.NumberFormat('id-ID').format(totalSalesHpp)}</td>
                            </tr>`;
                        }
                        $('#detail-sales-tbody').html(htmlSales);

                        let htmlPayments = '';
                        if (!response.payments || response.payments.length === 0) {
                            htmlPayments = `<tr><td colspan="6" class="text-center py-3 text-muted"><i class="fas fa-inbox mr-1"></i> Belum ada riwayat pembayaran.</td></tr>`;
                        } else {
                            response.payments.forEach((item, index) => {
                                const date = new Date(item.payment_date).toLocaleDateString('id-ID', {
                                    day:'2-digit', month:'2-digit', year:'numeric'
                                });
                                htmlPayments += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>
                                            <div class="font-weight-700">PAY-${item.id}</div>
                                            ${item.payment_proof ? `<a href="/storage/${item.payment_proof}" target="_blank" class="small text-info"><i class="fas fa-file-invoice mr-1"></i>Lihat Bukti</a>` : ''}
                                        </td>
                                        <td>${date}</td>
                                        <td class="text-center"><span class="qty-pill">${item.qty}</span></td>
                                        <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.buy_price)}</td>
                                        <td class="text-right font-weight-700" style="color:var(--teal-700)">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                                    </tr>`;
                            });
                        }
                        $('#detail-payments-tbody').html(htmlPayments);
                    },
                    error: function() {
                        const errRow = cols => `<tr><td colspan="${cols}" class="text-center text-danger py-3"><i class="fas fa-exclamation-circle mr-1"></i>Gagal mengambil data.</td></tr>`;
                        $('#detail-sales-tbody').html(errRow(6));
                        $('#detail-payments-tbody').html(errRow(6));
                    }
                });
            });

    /* ============================
       TOAST HELPER
       ============================ */
    function showToast(type, message) {
        const color = type === 'success' ? 'var(--teal-700)' : 'var(--red-600)';
        const icon  = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        const toast = $(`
            <div style="
                position:fixed; bottom:28px; right:28px; z-index:99999;
                background:#fff; color:${color}; border-left:4px solid ${color};
                border-radius:12px; padding:14px 22px; box-shadow:0 8px 30px rgba(0,0,0,.15);
                font-weight:700; font-size:14px; display:flex; align-items:center; gap:10px;
                animation: slideInToast .3s ease;
            ">
                <i class="fas ${icon}" style="font-size:18px;"></i> ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3500);
    }
});
</script>

<style>
@keyframes slideInToast {
    from { transform: translateX(60px); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
</style>
@endsection
