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
        text-align: center !important;
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

    /* Status pelunasan */
    .pay-badge {
        display: inline-block; margin-top: 5px; padding: 2px 9px;
        border-radius: 20px; font-size: 10px; font-weight: 800;
        letter-spacing: .3px; text-transform: uppercase; white-space: nowrap;
    }
    .pay-badge-lunas { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .pay-badge-sebagian { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

    /* Baris yang sudah lunas: diredupkan & tidak bisa dipilih */
    #report-table tbody tr.row-lunas { background: #fafafa; }
    #report-table tbody tr.row-lunas td { opacity: .62; }
    #report-table tbody tr.row-lunas .cost-text { text-decoration: line-through; color: var(--slate-400); }
    #report-table tbody tr.row-lunas .check-item { cursor: not-allowed; }

    /* Checkbox */
    .check-item, #checkAll { width: 16px; height: 16px; accent-color: var(--teal-600); cursor: pointer; }

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
                <p>Pilih transaksi yang belum lunas untuk melihat alokasi FIFO per PO</p>
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
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

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
        <p class="text-muted mb-3" style="font-size:12px;">
            Pilih satu atau beberapa transaksi yang akan dibayar, lalu klik <strong>Bayar Terpilih</strong>.
            Alokasi ke PO dan proses pembayaran dilakukan pada halaman berikutnya.
        </p>

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
                    <div class="sett-stat-label">Total Qty Transaksi</div>
                    <div class="sett-stat-value" id="summary-total-qty">—</div>
                </div>
            </div>
            <div class="sett-stat-card red">
                <div class="sett-stat-icon red">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="sett-stat-info">
                    <div class="sett-stat-label">Total Nilai Transaksi</div>
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
                <h4><i class="fas fa-table"></i> Daftar Transaksi Pelunasan Supplier</h4>
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
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Supplier</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-right">Total Nilai Transaksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</section>
</div>

{{-- Alokasi PO dan pembayaran dilakukan pada halaman terpisah. --}}

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
                    var disabled = row.payment_status === 'lunas';
                    return '<input type="checkbox" class="check-item" data-transaction-item-id="' + row.transaction_item_id + '"'
                        + (disabled ? ' disabled title="Transaksi sudah lunas"' : '') + '>';
                }
            },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            {
                data: 'transaction_code', searchable: true, className: 'text-center',
                render: function(data, type, row) {
                    return '<div class="font-weight-bold">' + $('<div>').text(data || ('#' + row.transaction_item_id)).html() + '</div>'
                        + (row.invoice_number ? '<small class="text-muted">' + $('<div>').text(row.invoice_number).html() + '</small>' : '');
                }
            },
            {
                data: 'transaction_date',
                searchable: false,
                className: 'text-center',
                render: function(data) {
                    if (!data) return '-';
                    const parsed = new Date(String(data).replace(' ', 'T'));
                    return Number.isNaN(parsed.getTime()) ? $('<div>').text(data).html() : parsed.toLocaleDateString('id-ID');
                }
            },
            {
                data: 'product_name', searchable: true,
                render: function(data) {
                    return $('<div>').text(data || '').html();
                }
            },
            {
                data: 'supplier_name', searchable: false, className: 'text-center',
                render: function(data) {
                    if (data) {
                        return '<span class="badge-supplier">' + $('<div>').text(data).html() + '</span>';
                    }
                    return '<span class="badge-no-supplier">Tanpa Supplier</span>';
                }
            },
            {
                data: 'total_qty', searchable: false, className: 'text-center',
                render: function(data, type, row) {
                    var qty = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(data || 0);
                    var hpp = new Intl.NumberFormat('id-ID').format(row.unit_hpp || 0);
                    return '<span class="qty-pill">' + qty + '</span>'
                        + '<span class="hpp-text ml-1">&times; Rp ' + hpp + '</span>';
                }
            },
            {
                data: 'total_cost', name: 'total_cost', className: 'text-right', searchable: false,
                render: function(data, type, row) {
                    var html = '<span class="cost-text">Rp ' + new Intl.NumberFormat('id-ID').format(data || 0) + '</span>';

                    if (row.payment_status === 'lunas') {
                        html += '<div><span class="pay-badge pay-badge-lunas"><i class="fas fa-check-circle mr-1"></i>Lunas</span></div>';
                    } else if (row.payment_status === 'sebagian') {
                        html += '<div><span class="pay-badge pay-badge-sebagian">Sebagian</span></div>';
                    }

                    return html;
                }
            }
        ],
        order: [[3, 'desc']],
        createdRow: function(row, data) {
            if (data.payment_status === 'lunas') {
                $(row).addClass('row-lunas');
            }
        },
        language: {
            processing: '<i class="fas fa-spinner fa-spin mr-2"></i> Memuat data...',
            emptyTable: '<div style="padding:20px;color:var(--slate-400);"><i class="fas fa-inbox" style="font-size:28px;margin-bottom:8px;display:block;"></i>Tidak ada data yang tersedia</div>',
            zeroRecords: '<div style="padding:20px;color:var(--slate-400);"><i class="fas fa-search" style="font-size:28px;margin-bottom:8px;display:block;"></i>Tidak ada hasil yang cocok</div>'
        }
    }).on('error.dt', function(e, settings, techNote, message) {
        console.error('DataTable Ajax error:', message);
        var xhr = settings.jqXHR;
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
            alert('Error: ' + xhr.responseJSON.error);
        } else if (xhr && xhr.responseText && xhr.responseText.indexOf('<!DOCTYPE') > -1) {
            alert('Sesi anda telah berakhir. Silakan refresh halaman dan login kembali.');
        } else {
            alert('Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
    });

    /* ============================
       FILTER SUBMIT
       ============================ */
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        selectedTransactionItems.clear();
        $('#checkAll').prop('checked', false);
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
            new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(summary.total_qty || 0) + ' pcs'
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
    const selectedTransactionItems = new Set();
    table.on('draw', function() {
        $('.check-item').each(function() {
            this.checked = selectedTransactionItems.has(String($(this).data('transaction-item-id')));
        });
        togglePayButton();
    });

    $('#checkAll').on('change', function() {
        $('.check-item:not(:disabled)').prop('checked', $(this).prop('checked'));
        $('.check-item:not(:disabled)').each(function() {
            const id = String($(this).data('transaction-item-id'));
            if (this.checked) selectedTransactionItems.add(id);
            else selectedTransactionItems.delete(id);
        });
        togglePayButton();
    });

    $(document).on('change', '.check-item', function() {
        const id = String($(this).data('transaction-item-id'));
        if (this.checked) selectedTransactionItems.add(id);
        else selectedTransactionItems.delete(id);
        const selectableItems = $('.check-item:not(:disabled)');
        const allChecked = (selectableItems.length === $('.check-item:not(:disabled):checked').length);
        $('#checkAll').prop('checked', allChecked);
        togglePayButton();
    });

    function togglePayButton() {
        const count = selectedTransactionItems.size;
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
        if (selectedTransactionItems.size === 0) return;
        const $form = $('<form>', {
            method: 'POST',
            action: "{{ route('admin.finance.settlement.allocate') }}"
        }).append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
        selectedTransactionItems.forEach(id => {
            $form.append($('<input>', { type: 'hidden', name: 'transaction_item_ids[]', value: id }));
        });
        ['start_date', 'end_date', 'supplier_id'].forEach(name => {
            $form.append($('<input>', { type: 'hidden', name: name, value: $('#' + name).val() }));
        });
        $('body').append($form);
        $form.trigger('submit');
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
});
</script>
@endsection
