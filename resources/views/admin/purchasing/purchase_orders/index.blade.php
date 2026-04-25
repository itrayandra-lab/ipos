@extends('master')
@section('title', 'Manajemen Purchase Order - ')

@push('styles')
<style>
    /* Premium Management Aesthetic */
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
    }
    .card-header {
        background-color: transparent !important;
        padding: 20px 25px !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .card-header h4 {
        color: #1e293b;
        font-weight: 800;
        font-size: 18px;
        margin-bottom: 0;
    }

    .btn-premium {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: white !important;
        border: none;
        padding: 8px 20px;
        border-radius: 10px;
        font-weight: 700;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
    }
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3);
    }

    /* Filter Box Premium */
    .filter-wrapper {
        background: #f8fafc;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
    }
    .filter-wrapper label {
        font-weight: 700;
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .form-control-premium {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        height: 42px;
        font-size: 13px;
    }
    .form-control-premium:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }

    .table thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 15px 25px !important;
    }
    .table tbody td {
        padding: 15px 25px !important;
        vertical-align: middle;
        color: #334155;
        font-weight: 500;
    }

    .badge-status {
        font-weight: 800;
        text-transform: uppercase;
        font-size: 10px;
        padding: 5px 12px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }
    .bg-soft-draft { background: #f1f5f9; color: #64748b; }
    .bg-soft-submitted { background: #e0f2fe; color: #0284c7; }
    .bg-soft-approved { background: #dcfce7; color: #16a34a; }
    .bg-soft-received { background: #ecfdf5; color: #10b981; border: 1px solid #10b981; }
    .bg-soft-cancelled { background: #fee2e2; color: #dc2626; }

    .po-number {
        color: #0d9488;
        font-weight: 800;
        font-family: 'Monaco', 'Consolas', monospace;
    }

    /* DataTables Control Padding */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px 25px !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Manajemen Purchase Order</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item">Purchasing</div>
                <div class="breadcrumb-item active">Purchase Orders</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
                            <i class="fas fa-check-circle mr-2"></i> {{ session()->get('message') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
                            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session()->get('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Daftar Seluruh PO</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.purchasing.purchase_orders.create') }}" class="btn btn-premium">
                                    <i class="fas fa-plus-circle mr-2"></i> Buat PO Baru
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filter Box Integrated -->
                            <div class="filter-wrapper shadow-sm">
                                <form id="filter-form">
                                    <div class="row align-items-end">
                                        <div class="col-md-3">
                                            <div class="form-group mb-md-0">
                                                <label>Status Pesanan</label>
                                                <select class="form-control form-control-premium" id="status" name="status">
                                                    <option value="">Semua Status</option>
                                                    <option value="draft">Draft</option>
                                                    <option value="submitted">Submitted</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="received">Received</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-md-0">
                                                <label>Mulai Tanggal</label>
                                                <input type="date" class="form-control form-control-premium" id="start_date" name="start_date">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-md-0">
                                                <label>Sampai Tanggal</label>
                                                <input type="date" class="form-control form-control-premium" id="end_date" name="end_date">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <button type="submit" class="btn btn-primary btn-block shadow-sm mr-2" style="height: 42px; border-radius: 10px; font-weight: 700;">
                                                    <i class="fas fa-search mr-1"></i> Cari
                                                </button>
                                                <button type="button" class="btn btn-light" style="height: 42px; width: 42px; border-radius: 10px;" onclick="resetFilter()" title="Reset Filter">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover w-100" id="table-po">
                                    <thead>
                                        <tr>
                                            <th width="50px">#</th>
                                            <th>No. PO</th>
                                            <th>Tanggal</th>
                                            <th>Supplier</th>
                                            <th class="text-right">Total Nominal</th>
                                            <th class="text-center">Status</th>
                                            <th>PIC</th>
                                            <th width="80px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
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

@push('scripts')
<script>
    let table;
    $(document).ready(function() {
        table = $('#table-po').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.purchasing.purchase_orders.getall') }}",
                data: function(d) {
                    d.status = $('#status').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { 
                    data: 'po_number', 
                    name: 'po_number',
                    render: function(data) {
                        return '<span class="po-number">' + data + '</span>';
                    }
                },
                { data: 'po_date', name: 'po_date' },
                { 
                    data: 'supplier_name', 
                    name: 'supplier.name',
                    render: function(data) {
                        return '<span class="font-weight-bold text-dark">' + data + '</span>';
                    }
                },
                { 
                    data: 'total', 
                    name: 'total',
                    className: 'text-right',
                    render: function(data) {
                        return '<span class="text-dark font-weight-bold">Rp ' + parseInt(data).toLocaleString('id-ID') + '</span>';
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    className: 'text-center',
                    render: function(data) {
                        let classes = {
                            'draft': 'bg-soft-draft',
                            'submitted': 'bg-soft-submitted',
                            'approved': 'bg-soft-approved',
                            'received': 'bg-soft-received',
                            'cancelled': 'bg-soft-cancelled'
                        };
                        return `<span class="badge badge-status ${classes[data]}">${data.toUpperCase()}</span>`;
                    }
                },
                { data: 'created_name', name: 'creator.name' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari No. PO atau Supplier...",
                lengthMenu: "_MENU_ baris",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        window.resetFilter = function() {
            $('#status').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            table.draw();
        };

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            swal({
                title: "Hapus PO ini?",
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: "warning",
                buttons: {
                    cancel: "Batal",
                    confirm: { text: "Ya, Hapus!", className: "btn-danger" }
                },
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('admin.purchasing.purchase_orders.delete') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", id: id },
                        beforeSend: function() { $.LoadingOverlay("show"); },
                        complete: function() { $.LoadingOverlay("hide"); },
                        success: function(res) {
                            if (res.status === 'success') {
                                table.ajax.reload();
                                iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                            }
                        },
                        error: function(err) {
                            iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Terjadi kesalahan', position: 'topRight' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
