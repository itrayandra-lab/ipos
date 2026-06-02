@extends('master')
@section('title', 'Manajemen Stok')

@push('styles')
<style>
:root {
    --teal-600: #0d9488;
    --slate-50: #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --green-50: #f0fdf4;
    --green-600: #16a34a;
    --amber-50: #fffbeb;
    --amber-500: #f59e0b;
    --red-50: #fef2f2;
    --red-500: #ef4444;
}

/* KPI Bar */
.kpi-bar {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.kpi-item {
    flex: 1;
    min-width: 140px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid var(--slate-200);
    padding: 14px 18px;
}
.kpi-item .kpi-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: var(--slate-400);
}
.kpi-item .kpi-value {
    font-size: 22px;
    font-weight: 800;
    color: var(--slate-800);
    margin-top: 2px;
}
.kpi-item.kpi-warning .kpi-value { color: var(--amber-500); }
.kpi-item.kpi-danger .kpi-value { color: var(--red-500); }

/* Inventory card */
.inventory-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--slate-200);
}
.inventory-card .card-header {
    background: transparent;
    border-bottom: 1px solid var(--slate-100);
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.inventory-card .card-header h4 {
    font-size: 14px;
    font-weight: 700;
    color: var(--slate-700);
    margin: 0;
}
.inventory-card .card-body {
    padding: 0;
}

/* Filter bar */
.filter-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    padding: 12px 20px;
    border-bottom: 1px solid var(--slate-100);
    background: var(--slate-50);
}
.filter-bar .form-control-sm {
    border-radius: 6px;
    border: 1px solid var(--slate-200);
    font-size: 12px;
    height: 32px;
    padding: 4px 10px;
}
.filter-bar .form-control-sm:focus {
    border-color: var(--teal-600);
    box-shadow: 0 0 0 2px rgba(13,148,136,.1);
}

/* Stock table */
#table-stock {
    margin: 0;
    font-size: 13px;
}
#table-stock thead th {
    background: var(--slate-50);
    color: var(--slate-500);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .3px;
    border-bottom: 1px solid var(--slate-200);
    padding: 10px 12px;
    white-space: nowrap;
}
#table-stock tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    border-bottom: 1px solid var(--slate-100);
}
#table-stock tbody tr:hover {
    background: #f0fdfa;
    cursor: pointer;
}
#table-stock tbody tr:last-child td {
    border-bottom: none;
}

.prod-name {
    font-weight: 600;
    color: var(--slate-800);
}
.prod-meta {
    font-size: 11px;
    color: var(--slate-400);
    margin-top: 1px;
}

.stock-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    font-size: 14px;
}
.stock-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.stock-dot.aman { background: var(--green-600); }
.stock-dot.menipis { background: var(--amber-500); }
.stock-dot.kritis { background: var(--red-500); }
.stock-dot.habis { background: var(--slate-300); }

.stock-bar {
    width: 80px;
    height: 5px;
    background: var(--slate-100);
    border-radius: 4px;
    display: inline-block;
    vertical-align: middle;
    margin-left: 8px;
    overflow: hidden;
}
.stock-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width .3s;
}
.stock-bar-fill.aman { background: var(--green-600); }
.stock-bar-fill.menipis { background: var(--amber-500); }
.stock-bar-fill.kritis { background: var(--red-500); }

.expiry-badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
    white-space: nowrap;
}
.expiry-safe {
    background: var(--green-50);
    color: var(--green-600);
}
.expiry-warning {
    background: var(--amber-50);
    color: var(--amber-500);
}
.expiry-danger {
    background: var(--red-50);
    color: var(--red-500);
}

.btn-detail-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    color: var(--slate-400);
    transition: all .15s;
}
.btn-detail-icon:hover {
    background: var(--slate-100);
    color: var(--slate-600);
}

.dataTables_wrapper .dataTables_filter {
    display: none;
}
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <h1>Manajemen Stok</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Stok</div>
            </div>
            <div class="section-header-button d-flex" style="gap:8px;">
                <a href="/admin/manage-master/stock/expired" class="btn btn-sm btn-outline-warning font-weight-bold" style="border-radius:8px;padding:6px 14px;">
                    <i class="fas fa-clock mr-1"></i> Kadaluarsa
                </a>
                @if(!auth()->user()->isFinance())
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add" style="border-radius:8px;padding:6px 14px;">
                    <i class="fas fa-plus"></i> Tambah Batch
                </button>
                @endif
            </div>
        </div>

        <div class="section-body">
            {{-- KPI Summary --}}
            <div class="kpi-bar">
                <div class="kpi-item">
                    <div class="kpi-label">Total SKU</div>
                    <div class="kpi-value">{{ number_format($totalSku, 0) }}</div>
                </div>
                <div class="kpi-item">
                    <div class="kpi-label">Total Unit</div>
                    <div class="kpi-value">{{ number_format($totalUnits, 0) }}</div>
                </div>
                <div class="kpi-item kpi-warning">
                    <div class="kpi-label">Stock Menipis</div>
                    <div class="kpi-value">{{ number_format($lowStockCount, 0) }}</div>
                </div>
                <div class="kpi-item kpi-danger">
                    <div class="kpi-label">Hampir Kadaluarsa</div>
                    <div class="kpi-value">{{ number_format($nearExpiredCount, 0) }}</div>
                </div>
                <div class="kpi-item">
                    <div class="kpi-label">Gudang</div>
                    <div class="kpi-value" style="font-size:16px;">{{ $warehouses->count() }}</div>
                </div>
            </div>

            {{-- Inventory Table --}}
            <div class="inventory-card">
                <div class="card-header">
                    <h4><i class="fas fa-warehouse mr-2" style="color:var(--teal-600);"></i>Inventory Monitoring</h4>
                </div>

                {{-- Filter Bar --}}
                <div class="filter-bar">
                    <input type="text" id="search-box" class="form-control form-control-sm" placeholder="Cari produk..." style="flex:1;min-width:160px;">
                    @if(!auth()->user()->isSales() || $warehouses->count() > 1)
                    <select id="warehouse-filter" class="form-control form-control-sm" style="width:160px;">
                        @if(!auth()->user()->isSales())
                        <option value="">Semua Gudang</option>
                        @endif
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select id="status-filter" class="form-control form-control-sm" style="width:130px;">
                        <option value="">Semua Status</option>
                        <option value="aman">Aman</option>
                        <option value="menipis">Menipis</option>
                        <option value="kritis">Kritis</option>
                        <option value="habis">Habis</option>
                    </select>
                    <select id="expiry-filter" class="form-control form-control-sm" style="width:130px;">
                        <option value="">Expiry</option>
                        <option value="expired">Sudah Expired</option>
                        <option value="near">Hampir Expired</option>
                        <option value="safe">Masih Lama</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table" id="table-stock">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Produk</th>
                                <th style="width:100px;">Batch</th>
                                <th style="width:90px;">Netto</th>
                                <th style="width:120px;">Gudang</th>
                                <th style="width:140px;">Stok</th>
                                <th style="width:100px;" class="text-right">Nilai</th>
                                <th style="width:90px;">Expired</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

@include('admin.manage_master.stock.modals')
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let activeWarehouseId = $('#warehouse-filter').length ? $('#warehouse-filter').val() : '';

    const table = $('#table-stock').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ url("admin/manage-master/stock/all") }}',
            data: function(d) {
                d.warehouse_id = activeWarehouseId;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product_name', name: 'product_name' },
            { data: 'batch_count', name: 'batch_count', className: 'text-center', searchable: false },
            { data: 'netto', name: 'netto', orderable: false, searchable: false },
            { data: 'warehouse_name', name: 'warehouse_name' },
            { data: 'stock_display', name: 'total_current_stock', searchable: false, orderable: false },
            { data: 'value_display', name: 'stock_value', className: 'text-right', orderable: false, searchable: false },
            { data: 'expiry_display', name: 'expiry_info', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [],
        dom: 'rt<"d-flex justify-content-between align-items-center px-3 py-2"ip>',
        language: {
            search: '', searchPlaceholder: 'Cari produk...',
            processing: '<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...',
            zeroRecords: '<div class="text-muted py-4"><i class="fas fa-box-open mr-1"></i>Tidak ada data stok</div>',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            paginate: { previous: '‹', next: '›' }
        },
        drawCallback: function() { $('.dataTables_paginate').addClass('pagination-sm'); }
    });

    // Render stock display, value, expiry
    table.on('xhr.dt', function(e, settings, json) {
        if (!json || !json.data) return;
        $.each(json.data, function(i, row) {
            const stock = row.total_current_stock;
            const status = row.stock_status;
            const statusLabel = { aman: 'Aman', menipis: 'Menipis', kritis: 'Kritis', habis: 'Habis' };
            const maxVal = Math.max(stock, 20);
            const barPct = Math.min(100, (stock / maxVal) * 100);

            row.stock_display = `
                <div class="stock-badge">
                    <span class="stock-dot ${status}"></span>
                    ${stock}
                    <span class="stock-bar"><span class="stock-bar-fill ${status}" style="width:${barPct}%"></span></span>
                </div>
                <div style="font-size:11px;color:var(--slate-400);margin-top:1px;">${statusLabel[status] || status}</div>`;

            row.value_display = stock > 0 ? 'Rp ' + Number(row.stock_value).toLocaleString('id-ID') : '-';

            if (row.expiry_info) {
                const exp = new Date(row.expiry_info);
                const now = new Date();
                const diffDays = Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
                let cls = 'expiry-safe', label = exp.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                if (diffDays < 0) { cls = 'expiry-danger'; label = 'Expired'; }
                else if (diffDays <= 30) { cls = 'expiry-warning'; label += ' (' + diffDays + ' hari)'; }
                row.expiry_display = `<span class="expiry-badge ${cls}">${label}</span>`;
            } else {
                row.expiry_display = '<span class="text-muted" style="font-size:11px;">-</span>';
            }
        });
    });

    // Warehouse filter
    $('#warehouse-filter').on('change', function() {
        activeWarehouseId = $(this).val();
        table.ajax.reload();
    });

    // Status filter (client-side)
    let statusFilterFn = null;
    $('#status-filter').on('change', function() {
        const val = $(this).val();
        if (statusFilterFn) { $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(statusFilterFn), 1); }
        if (val) {
            statusFilterFn = function(settings, data, dataIndex) {
                const rowData = table.row(dataIndex).data();
                return rowData && rowData.stock_status === val;
            };
            $.fn.dataTable.ext.search.push(statusFilterFn);
        } else { statusFilterFn = null; }
        table.draw();
    });

    // Client-side search
    $('#search-box').on('keyup', function() { table.search($(this).val()).draw(); });

    // Row click
    $(document).on('click', '#table-stock tbody tr', function(e) {
        if ($(e.target).closest('a, button, .btn-detail-icon').length) return;
        const data = table.row(this).data();
        if (data && data.action) {
            const url = $(data.action).attr('href');
            if (url) window.location.href = url;
        }
    });

    // Form add
    $('#form-add').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("admin/manage-master/stock") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modal-add').modal('hide');
                table.ajax.reload();
                iziToast.success({ title: 'Berhasil', message: res.message });
            }
        });
    });

    // Product variant loading
    $('#product-add').on('change', function() {
        let id = $(this).val();
        $('#variant-add').html('<option value="">Pilih Varian</option>');
        if (id) {
            $.get('{{ url("admin/manage-master/stock/variants") }}/' + id, function(res) {
                res.data.forEach(function(v) {
                    $('#variant-add').append('<option value="' + v.id + '">' + v.netto_value + ' ' + v.satuan + '</option>');
                });
            });
        }
    });

    $('.select2').each(function() {
        $(this).select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $(this).closest('.modal') });
    });
});
</script>
@endpush
