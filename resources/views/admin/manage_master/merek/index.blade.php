@extends('master')
@section('title', 'Manajemen Merek - ')

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
    }

    .btn-premium {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: white;
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
        color: white;
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

    /* DataTables Control Padding */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px 25px !important;
    }
    
    .badge-code {
        background: #f1f5f9;
        color: #0f766e;
        font-family: 'Monaco', 'Consolas', monospace;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 25px;
    }
    .modal-title {
        font-weight: 800;
        color: #1e293b;
    }
    .modal-body {
        padding: 25px;
    }
    .form-group label {
        font-weight: 700;
        color: #475569;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        height: auto;
    }
    .form-control:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Master Merek</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item">Master Data</div>
                <div class="breadcrumb-item active">Merek</div>
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
                            <h4>Daftar Merek Produk</h4>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-premium" data-toggle="modal" data-target="#addModal">
                                    <i class="fas fa-plus-circle mr-2"></i> Tambah Merek Baru
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="merekTable">
                                    <thead>
                                        <tr>
                                            <th width="50px">#</th>
                                            <th width="120px">Kode</th>
                                            <th>Nama Merek</th>
                                            <th>Deskripsi</th>
                                            <th width="100px">Aksi</th>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle text-teal mr-2"></i> Tambah Merek</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ url('admin/manage-master/merek') }}" method="POST" class="needs-validation" novalidate="">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Merek</label>
                        <input type="text" placeholder="Contoh: Wardah, Somethinc..." class="form-control" name="name" required="">
                        <div class="invalid-feedback">Nama merek wajib diisi</div>
                    </div>
                    <div class="form-group">
                        <label>Kode Merek</label>
                        <input type="text" placeholder="Contoh: WRD (Maks 10 Karakter)" class="form-control" name="code" required="" maxlength="10">
                        <div class="invalid-feedback">Kode merek wajib diisi (Maks 10 huruf)</div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi <small class="text-muted">(Opsional)</small></label>
                        <textarea class="form-control" placeholder="Keterangan tambahan mengenai merek ini..." name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-premium">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit text-teal mr-2"></i> Update Merek</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ url('admin/manage-master/merek/update') }}" method="POST" class="needs-validation" novalidate="">
                @csrf
                <input type="hidden" name="id" id="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Merek</label>
                        <input type="text" class="form-control" name="name" required="" id="name">
                    </div>
                    <div class="form-group">
                        <label>Kode Merek</label>
                        <input type="text" class="form-control" name="code" required="" id="code" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi <small class="text-muted">(Opsional)</small></label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-premium">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#merekTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('admin/manage-master/merek/all') }}",
                type: "GET"
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { 
                    data: 'code', 
                    name: 'code',
                    render: function(data) {
                        return '<span class="badge-code">' + data + '</span>';
                    }
                },
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data) {
                        return '<span class="font-weight-bold">' + data + '</span>';
                    }
                },
                { 
                    data: 'description', 
                    name: 'description',
                    render: function(data) {
                        return data ? data : '<span class="text-muted italic">- Tidak ada deskripsi -</span>';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari merek...",
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

        // Edit button handler
        $('#merekTable').on('click', '.edit[data-id]', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            $.ajax({
                data: { 'id': id, '_token': "{{ csrf_token() }}" },
                type: 'POST',
                url: "{{ url('admin/manage-master/merek/get') }}",
                beforeSend: function() {
                    $.LoadingOverlay("show", { image: "", fontawesome: "fa fa-cog fa-spin" });
                },
                complete: function() {
                    $.LoadingOverlay("hide");
                },
                success: function(data) {
                    $('#id').val(data.id);
                    $('#name').val(data.name);
                    $('#code').val(data.code);
                    $('#description').val(data.description);
                    $('#updateModal').modal('show');
                },
                error: function(err) {
                    iziToast.error({ title: 'Error', message: 'Gagal mengambil data', position: 'topRight' });
                }
            });
        });

        // Delete button handler
        $('#merekTable').on('click', '.hapus[data-id]', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            swal({
                title: "Hapus Merek?",
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
                        data: { 'id': id, '_token': "{{ csrf_token() }}" },
                        type: 'DELETE',
                        url: "{{ url('admin/manage-master/merek') }}",
                        beforeSend: function() {
                            $.LoadingOverlay("show");
                        },
                        complete: function() {
                            $.LoadingOverlay("hide");
                        },
                        success: function(data) {
                            iziToast.success({ title: 'Berhasil', message: data.message, position: 'topRight' });
                            $('#merekTable').DataTable().ajax.reload();
                        },
                        error: function(err) {
                            iziToast.error({ title: 'Error', message: 'Gagal menghapus data', position: 'topRight' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection