@extends('master')

@section('title', 'Surat Jalan Supplier')

@push('styles')
<style>
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
    .section-header h1 { font-weight: 800 !important; color: #1e293b !important; margin-bottom: 0; }

    .card { border-radius: 20px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.03); }
    .card-header { background-color: transparent !important; padding: 20px 25px !important; border-bottom: 1px solid #f1f5f9 !important; }
    .card-header h4 { color: #1e293b; font-weight: 800; font-size: 18px; margin-bottom: 0; }

    .table thead th {
        background-color: #f8fafc; color: #64748b; font-weight: 700;
        text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;
        border-top: none; padding: 8px 12px !important;
    }
    .table tbody td { padding: 8px 12px !important; vertical-align: middle; color: #334155; font-weight: 500; }

    .badge-status {
        font-weight: 800; text-transform: uppercase; font-size: 10px;
        padding: 5px 12px; border-radius: 20px; letter-spacing: 0.5px;
    }

    .po-number { color: #0d9488; font-weight: 800; font-family: 'Monaco', 'Consolas', monospace; }

    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate {
        padding: 8px 12px !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Surat Jalan Supplier</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item">Surat Jalan Supplier</div>
            </div>
            <div class="section-header-button">
                <a href="{{ route('admin.purchasing.supplier_delivery_notes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Surat Jalan
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Surat Jalan Supplier</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover w-100" id="table-sdn">
                                    <thead>
                                        <tr>
                                            <th width="30px">No</th>
                                            <th>Dokumen</th>
                                            <th>Surat Jalan</th>
                                            <th>Supplier</th>
                                            <th>Produk</th>
                                            <th width="80px">Aksi</th>
                                        </tr>
                                    </thead>
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
        table = $('#table-sdn').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.purchasing.supplier_delivery_notes.getall') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'dokumen', name: 'sj_number' },
                { data: 'surat_jalan', name: 'delivery_note_number' },
                { data: 'supplier_name', name: 'supplier_name' },
                { data: 'produk', name: 'produk', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari...",
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

        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Surat Jalan?',
                text: 'Data tidak bisa dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.purchasing.supplier_delivery_notes.destroy', '') }}/" + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });
                                table.ajax.reload();
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                            }
                        },
                        error: function(err) {
                            Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Gagal menghapus data' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
