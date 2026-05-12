@extends('master')
@section('title', 'Harga Produk — Pricing Engine')
@section('content')

    <style>
        :root {
            --clr-primary: #0f766e;
            --clr-primary-light: #ccfbf1;
            --clr-success: #10b981;
            --clr-warning: #f59e0b;
            --clr-danger: #ef4444;
            --clr-info: #3b82f6;
            --radius-card: 16px;
        }

        /* ── Page wrapper ── */
        .pricing-page {
            /* background: #f8fafc; */
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* ── Section header ── */
        .section-header {
            background: #ffffff !important;
            padding: 20px 25px !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
            margin: 0 0 30px 0 !important;
            border-top: none !important;
            border-right: none !important;
            border-bottom: none !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            position: relative !important;
            z-index: 10 !important;
            overflow: hidden !important;
            background-clip: padding-box !important;
        }

        .section-header::after,
        .section-header::before {
            display: none !important;
        }

        .section-header h1 {
            font-size: 20px !important;
            font-weight: 800 !important;
            color: #1e293b !important;
            letter-spacing: -0.025em;
            margin-bottom: 0 !important;
        }

        .section-header-breadcrumb {
            background: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
            backdrop-filter: none !important;
            border: none !important;
            margin-top: 0 !important;
        }

        .section-header-breadcrumb .breadcrumb-item,
        .section-header-breadcrumb .breadcrumb-item a {
            color: #64748b !important;
            font-weight: 500 !important;
            font-size: 11px !important;
        }

        .section-header-breadcrumb .breadcrumb-item.active {
            color: #0f766e !important;
            font-weight: 700 !important;
        }

        /* ── Stat cards ── */
        .stat-card {
            border-radius: var(--radius-card);
            border: 1px solid rgba(241, 245, 249, 0.8);
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            margin-bottom: 20px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, .08);
            border-color: var(--clr-primary);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .stat-card .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
            color: #1e293b;
        }

        .stat-card .stat-label {
            font-size: .68rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── Main card ── */
        .main-card {
            border-radius: var(--radius-card);
            border: none;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        .main-card .card-header {
            background: #fff;
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .main-card .card-header h4 {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.9rem;
            margin: 0;
        }

        /* ── Toolbar ── */
        .toolbar-btn {
            border-radius: 8px !important;
            font-weight: 700 !important;
            font-size: .75rem !important;
            padding: 7px 14px !important;
        }

        .btn-recalc-all {
            background: linear-gradient(135deg, #0f766e, #0d9488) !important;
            color: #fff !important;
            border: none !important;
        }

        .btn-recalc-all:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        /* ── Filter pills ── */
        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 15px;
            border-radius: 10px;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            transition: all .2s;
            background: #fff;
            color: #64748b;
        }

        .filter-pill.active {
            border-color: var(--clr-primary);
            background: var(--clr-primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(15, 118, 110, .2);
        }

        .filter-pill:not(.active):hover {
            background: #f8fafc;
            border-color: var(--clr-primary);
            color: var(--clr-primary);
        }

        /* ── Table tweaks ── */
        #pricing-table {
            font-size: .8rem;
        }

        #pricing-table thead th {
            background: #f8fafc !important;
            color: #64748b;
            font-weight: 700;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-top: none;
            padding: 12px 10px;
            white-space: nowrap;
        }

        #pricing-table tbody td {
            vertical-align: middle;
            padding: 12px;
            border-color: #f1f5f9;
        }

        #pricing-table tbody tr {
            transition: background .15s;
        }

        #pricing-table tbody tr:hover {
            background: #fafffe;
        }

        /* ── Input fields in Modal ── */
        .form-control-premium {
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            font-weight: 600 !important;
            padding: 10px 15px !important;
            height: auto !important;
        }

        .form-control-premium:focus {
            border-color: var(--clr-primary) !important;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, .1) !important;
        }

        /* ── Toast notification ── */
        .toast-msg {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            padding: 12px 20px;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: .88rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(80px);
            opacity: 0;
            transition: all .3s;
            max-width: 360px;
        }

        .toast-msg.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-msg.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .toast-msg.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .toast-msg.info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        /* ── DataTables Padding Fix ── */
        .dataTables_wrapper {
            padding: 20px 0;
        }

        .dataTables_length,
        .dataTables_filter {
            padding: 0 24px 15px 24px;
        }

        .dataTables_info,
        .dataTables_paginate {
            padding: 15px 24px 0 24px;
        }

        /* ── Empty state ── */
        .dataTables_empty {
            color: #94a3b8;
            font-size: .9rem;
            padding: 40px 0 !important;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .stat-card {
                padding: 16px;
            }

            .stat-icon {
                width: 42px;
                height: 42px;
                font-size: 1.1rem;
            }

            .stat-card .stat-value {
                font-size: 1.4rem;
            }
        }
    </style>

    <div class="main-content">
        <div class="pricing-page">
            <section class="section">

                {{-- ── Header ── --}}
                <div class="section-header">
                    <h1><i class="fas fa-tags mr-2"></i>Harga Produk</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                        <div class="breadcrumb-item">Produk</div>
                        <div class="breadcrumb-item active">Harga</div>
                    </div>
                </div>

                <div class="section-body">

                    {{-- ── Stat Cards ── --}}
                    <div class="row">
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background:#e0f2fe;color:#0369a1;">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div>
                                    <div class="stat-value" id="stat-total">{{ $stats['total'] }}</div>
                                    <div class="stat-label">Total Varian</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background:#dcfce7;color:#166534;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <div class="stat-value" id="stat-approved">{{ $stats['approved'] }}</div>
                                    <div class="stat-label">Approved</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background:#fef9c3;color:#854d0e;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div class="stat-value" id="stat-pending">{{ $stats['pending'] }}</div>
                                    <div class="stat-label">Pending</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background:#fee2e2;color:#991b1b;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <div class="stat-value" id="stat-belum">{{ $stats['belumHitung'] }}</div>
                                    <div class="stat-label">Belum Dihitung</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Main Table Card ── --}}
                    <div class="card main-card">
                        <div class="card-header">
                            <div>
                                <h4><i class="fas fa-calculator mr-2 text-primary"></i>Kalkulasi & Persetujuan Harga</h4>
                                <small class="text-muted">Klik Proses → Atur Parameter → Simpan → Approve</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap" style="gap:8px;">
                                {{-- Filter pills --}}
                                <div id="filter-container" class="d-flex" style="gap:6px;flex-wrap:wrap;">
                                    <span class="filter-pill active" data-filter="all">
                                        <i class="fas fa-list fa-xs"></i> Semua
                                    </span>
                                    <span class="filter-pill" data-filter="belum">
                                        <i class="fas fa-exclamation-circle fa-xs text-danger"></i> Belum Hitung
                                    </span>
                                    <span class="filter-pill" data-filter="pending">
                                        <i class="fas fa-clock fa-xs text-warning"></i> Pending
                                    </span>
                                    <span class="filter-pill" data-filter="approved">
                                        <i class="fas fa-check-circle fa-xs text-success"></i> Approved
                                    </span>
                                </div>
                                <button id="btn-recalc-all" class="btn toolbar-btn btn-recalc-all ml-2">
                                    <i class="fas fa-sync-alt mr-1"></i> Hitung Semua
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="pricing-table" width="100%">
                                    <thead>
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Produk</th>
                                            <th width="140">Tier Produk</th>
                                            <th width="100">Pajak</th>
                                            <th width="130">HPP Modal</th>
                                            <th width="200">Margin HPP</th>
                                            <th width="140">Ray Store</th>
                                            <th width="130">HET Product</th>
                                            <th width="110">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>{{-- end section-body --}}
            </section>
        </div>
    </div>

    {{-- ── Modal Proses Pricing ── --}}
    <div class="modal fade" id="modal-process" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius:20px; overflow:hidden; border:none; box-shadow:0 20px 50px rgba(0,0,0,0.2);">
                <div class="modal-header bg-primary text-white py-4 px-4">
                    <h5 class="modal-title font-weight-bold" id="modal-title">Proses Pricing</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-pricing">
                    @csrf
                    <input type="hidden" name="id" id="inp-id">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="small font-weight-bold text-muted text-uppercase mb-2 d-block">Nama Produk</label>
                            <div id="display-name" class="h6 font-weight-bold text-dark mb-0" style="line-height:1.5;">-
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted text-uppercase">Tier Produk</label>
                                    <select name="tier_id" id="inp-tier" class="form-control form-control-premium">
                                        <option value="">Tanpa Tier</option>
                                        @foreach($tiers as $tier)
                                            <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted text-uppercase">Status Pajak</label>
                                    <select name="tax_status" id="inp-tax" class="form-control form-control-premium">
                                        <option value="1">PPN</option>
                                        <option value="0">Non PPN</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted text-uppercase">HPP Modal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="border-radius:10px 0 0 10px; border-right:none;">Rp</span>
                                        </div>
                                        <input type="text" id="inp-hpp"
                                            class="form-control form-control-premium rupiah-mask"
                                            style="border-radius:0 10px 10px 0 !important;" placeholder="0">
                                        <input type="hidden" name="product_hpp" id="inp-hpp-raw">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted text-uppercase">Ray Store
                                        (Offline)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="border-radius:10px 0 0 10px; border-right:none;">Rp</span>
                                        </div>
                                        <input type="text" id="inp-ray"
                                            class="form-control form-control-premium rupiah-mask"
                                            style="border-radius:0 10px 10px 0 !important;" placeholder="0">
                                        <input type="hidden" name="ray_store" id="inp-ray-raw">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded" style="border-radius:12px !important;">
                            <div class="text-center small text-muted font-weight-bold mb-2">PREVIEW KALKULASI</div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Estimasi HET Online:</span>
                                <span class="font-weight-bold text-primary" id="preview-het">-</span>
                            </div>
                            <div class="small text-muted text-center mt-2 italic">HPP Ray, Margin, dan HET Online akan
                                diperbarui otomatis setelah disimpan.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke p-4">
                        <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal"
                            style="border-radius:10px;">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold" id="btn-save"
                            style="border-radius:10px;">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toast notification --}}
    <div id="toast-msg" class="toast-msg">
        <i class="fas fa-check-circle" id="toast-icon"></i>
        <span id="toast-text"></span>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {

                /* ── Helpers ── */
                function fmt(val) {
                    if (!val) return '0';
                    return parseInt(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
                function raw(val) {
                    if (!val) return 0;
                    return parseInt(val.toString().replace(/[^0-9]/g, '')) || 0;
                }
                function showToast(msg, type = 'success') {
                    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
                    $('#toast-msg').removeClass('success error info').addClass(type);
                    $('#toast-icon').attr('class', 'fas ' + (icons[type] || icons.success));
                    $('#toast-text').text(msg);
                    $('#toast-msg').addClass('show');
                    setTimeout(() => $('#toast-msg').removeClass('show'), 3500);
                }
                function refreshStats() {
                    $.get("{{ route('admin.products.pricing.stats') }}", function (d) {
                        $('#stat-total').text(d.total);
                        $('#stat-approved').text(d.approved);
                        $('#stat-pending').text(d.pending);
                        $('#stat-belum').text(d.belumHitung);
                    });
                }

                /* ── DataTable ── */
                let activeFilter = 'all';

                const table = $('#pricing-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.products.pricing.all') }}",
                        data: function (d) { d.filter = activeFilter; }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'product_info', name: 'product_info', orderable: true, searchable: true },
                        { data: 'tier_col', name: 'tier_col', orderable: false, searchable: false },
                        { data: 'tax_status_col', name: 'tax_status_col', orderable: false, searchable: false },
                        { data: 'hpp_beli_col', name: 'hpp_beli_col', orderable: false, searchable: false },
                        { data: 'margin_hpp_col', name: 'margin_hpp_col', orderable: false, searchable: false },
                        { data: 'ray_store_col', name: 'ray_store_col', orderable: false, searchable: false },
                        { data: 'het_product_col', name: 'het_product_col', orderable: false, searchable: false },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ],
                    order: [[1, 'asc']],
                    pageLength: 25,
                    language: {
                        search: '<i class="fas fa-search mr-1"></i>',
                        searchPlaceholder: 'Cari produk atau SKU...',
                        processing: '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>',
                        emptyTable: '<div class="text-center py-4 text-muted"><i class="fas fa-box-open fa-2x mb-2 d-block"></i>Tidak ada data</div>',
                        zeroRecords: '<div class="text-center py-4 text-muted"><i class="fas fa-search fa-2x mb-2 d-block"></i>Data tidak ditemukan</div>',
                    }
                });

                /* ── Filter pills ── */
                $(document).on('click', '.filter-pill', function () {
                    $('.filter-pill').removeClass('active');
                    $(this).addClass('active');
                    activeFilter = $(this).data('filter');
                    table.ajax.reload(null, false);
                });

                /* ── Rupiah formatting on input ── */
                $(document).on('input', '.rupiah-mask', function () {
                    const v = raw($(this).val());
                    $(this).val(fmt(v));

                    // Update hidden inputs
                    if ($(this).attr('id') === 'inp-hpp') $('#inp-hpp-raw').val(v);
                    if ($(this).attr('id') === 'inp-ray') $('#inp-ray-raw').val(v);
                });

                /* ── Open Process Modal ── */
                $(document).on('click', '.btn-process', function () {
                    const d = $(this).data();

                    $('#inp-id').val(d.id);
                    $('#display-name').text(d.name);
                    $('#inp-tier').val(d.tier);
                    $('#inp-tax').val(d.tax);

                    $('#inp-hpp').val(fmt(d.hpp));
                    $('#inp-hpp-raw').val(d.hpp);

                    $('#inp-ray').val(fmt(d.ray));
                    $('#inp-ray-raw').val(d.ray);

                    $('#preview-het').text('Rp ' + fmt(d.ray)); // Placeholder, actual HET calculated by server

                    $('#modal-process').modal('show');
                });

                /* ── Save Process Modal ── */
                $('#form-pricing').on('submit', function (e) {
                    e.preventDefault();
                    const $btn = $('#btn-save');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');

                    $.ajax({
                        url: "{{ route('admin.products.pricing.save') }}",
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function (res) {
                            $('#modal-process').modal('hide');
                            showToast(res.message, 'success');
                            table.ajax.reload(null, false);
                            refreshStats();
                            $btn.prop('disabled', false).text('Simpan Data');
                        },
                        error: function (xhr) {
                            showToast(xhr.responseJSON?.message || 'Gagal menyimpan data', 'error');
                            $btn.prop('disabled', false).text('Simpan Data');
                        }
                    });
                });

                /* ── Recalculate ALL ── */
                $('#btn-recalc-all').on('click', function () {
                    Swal.fire({
                        title: 'Hitung Ulang Semua Harga?',
                        html: 'Sistem akan menghitung ulang <b>HPP Ray, Margin, dan HET Online</b> untuk semua varian yang sudah memiliki HPP Modal.<br><br><span class="text-muted">Ray Store yang sudah disesuaikan manual akan tetap dipertahankan.</span>',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-calculator mr-1"></i>Ya, Hitung Semua',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#0f766e',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const $btn = $(this);
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menghitung...');

                        $.post("{{ route('admin.products.pricing.recalculate_all') }}", {
                            _token: '{{ csrf_token() }}'
                        }, function (res) {
                            showToast(res.message, 'success');
                            table.ajax.reload(null, false);
                            refreshStats();
                            $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i>Hitung Semua');
                        }).fail(function (xhr) {
                            showToast('Gagal menghitung ulang semua harga', 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i>Hitung Semua');
                        });
                    });
                });

                /* ── Approve / Unapprove ── */
                $(document).on('click', '.btn-approve', function () {
                    const id = $(this).data('id');
                    const status = $(this).data('status');
                    const isApproving = status == 1;

                    Swal.fire({
                        title: isApproving ? 'Approve Harga?' : 'Batalkan Approval?',
                        html: isApproving
                            ? 'Harga HET Online akan dikunci untuk varian ini.'
                            : 'Status akan kembali ke <b>Pending</b>.',
                        icon: isApproving ? 'success' : 'warning',
                        showCancelButton: true,
                        confirmButtonText: isApproving ? '<i class="fas fa-check mr-1"></i>Approve' : '<i class="fas fa-times mr-1"></i>Batalkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: isApproving ? '#10b981' : '#ef4444',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.post("{{ route('admin.products.pricing.approve') }}", {
                            _token: '{{ csrf_token() }}',
                            id: id,
                            status: status,
                        }, function (res) {
                            showToast(res.message, 'success');
                            table.ajax.reload(null, false);
                            refreshStats();
                        }).fail(function () {
                            showToast('Gagal memproses approval', 'error');
                        });
                    });
                });

            });
        </script>
    @endpush
@endsection