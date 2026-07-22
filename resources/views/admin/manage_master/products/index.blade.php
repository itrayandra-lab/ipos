@extends('master')
@section('title', 'Data Produk')
@section('content')
    <div class="main-content">
        <style>
            /* Premium Aesthetic Enhancements */
            :root {
                --primary-gradient: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            }

            .section-header {
                background: #fff;
                padding: 20px 25px !important;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.05);
                margin-bottom: 25px !important;
                border-left: 5px solid #0d9488;
            }

            .section-header h1 {
                font-weight: 800 !important;
                color: #1e293b !important;
                letter-spacing: -0.5px;
            }

            .card {
                border-radius: 15px !important;
                border: none !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
            }

            .card-header {
                border-bottom: 1px solid #f1f5f9 !important;
                padding: 20px 25px !important;
            }

            .card-header h4 {
                color: #0d9488 !important;
                font-weight: 700 !important;
            }

            #products-table {
                font-size: 13px !important;
                border: none !important;
            }

            #products-table thead th {
                background-color: #f8fafc !important;
                color: #64748b !important;
                text-transform: none !important; /* Title Case */
                font-weight: 600 !important;
                padding: 15px 12px !important;
                border-top: none !important;
            }

            #products-table tbody td {
                padding: 15px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            /* Custom Badges */
            .badge-soft-success {
                background-color: #dcfce7;
                color: #15803d;
                padding: 6px 12px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 11px;
                display: inline-flex;
                align-items: center;
            }
            .badge-soft-success::before {
                content: "";
                width: 6px;
                height: 6px;
                background: #15803d;
                border-radius: 50%;
                margin-right: 6px;
            }

            .badge-soft-secondary {
                background-color: #f1f5f9;
                color: #475569;
                padding: 6px 12px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 11px;
            }

            /* Table Photo Styling */
            .img-thumbnail-custom {
                width: 48px;
                height: 48px;
                object-fit: cover;
                border-radius: 10px;
                border: 2px solid #f1f5f9;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                transition: transform 0.2s;
            }
            .img-thumbnail-custom:hover {
                transform: scale(1.1);
            }

            .hierarchy-text {
                color: #64748b;
                font-size: 11px;
            }
            .hierarchy-main {
                color: #1e293b;
                font-weight: 600;
                display: block;
            }

            /* Action Button Refinement */
            .btn-action-custom {
                border-radius: 8px !important;
                font-weight: 600 !important;
                padding: 5px 15px !important;
                background-color: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                color: #475569 !important;
                box-shadow: none !important;
            }
            .btn-action-custom:hover {
                background-color: #f1f5f9 !important;
                color: #1e293b !important;
            }

            .btn-tambah-custom {
                background: var(--primary-gradient) !important;
                border: none !important;
                border-radius: 8px !important;
                padding: 10px 20px !important;
                font-weight: 700 !important;
                box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3) !important;
                transition: all 0.3s;
            }
            .btn-tambah-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(13, 148, 136, 0.4) !important;
            }

            /* DataTable search and entries refinement */
            .dataTables_wrapper .dataTables_filter input {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
                padding: 8px 12px !important;
            }
            .dataTables_wrapper .dataTables_length select {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
            }
        </style>
        <section class="section">
            <div class="section-header">
                <h1>Data Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Produk</div>
                </div>
            </div>

            <div class="section-body">
                <!-- Redundant titles removed to improve focus and vertical space -->
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi kesalahan!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Seluruh Produk</h4>
                        @if(auth()->user()->canEdit('access_products') && !auth()->user()->isSales())
                        <div class="card-header-form">
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-tambah-custom">
                                <i class="fas fa-plus mr-2"></i> Tambah Produk Baru
                            </a>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5" id="products-table">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Merk</th>
                                    <th>Produk</th>
                                    <th>Kategori / Tipe</th>
                                    <th>SKUs</th>
                                    <th>Status</th>
                                    <th class="text-center">Foto</th>
                                    <th width="10px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- No modals needed here as Create and Edit are standalone pages -->

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#products-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/manage-master/products/all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'merek_name', name: 'merek_name' },
                    { 
                        data: 'name', 
                        name: 'name',
                        render: function(data, type, row) {
                            return `<span class="hierarchy-main">${data}</span>`;
                        }
                    },
                    { 
                        data: 'hierarchy', 
                        name: 'hierarchy',
                        render: function(data, type, row) {
                            return `<span class="hierarchy-text">${data}</span>`;
                        }
                    },
                    { data: 'variant_count', name: 'variant_count' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data, type, row) {
                            if (data === 'Aktif') {
                                return `<span class="badge-soft-success">Aktif</span>`;
                            }
                            return `<span class="badge-soft-secondary">${data}</span>`;
                        }
                    },
                    { 
                        data: 'photos_preview', 
                        name: 'photos_preview',
                        className: 'text-center',
                        render: function(data, type, row) {
                            const srcMatch = data.match(/src="([^"]+)"/);
                            const src = srcMatch ? srcMatch[1] : '';
                            return `<img src="${src}" class="img-thumbnail-custom">`;
                        }
                    },
                    { data: 'action', name: 'action' }
                ]
            });

            // Delete Logic
            $('#products-table').on('click', '.hapus[data-id]', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                Swal.fire({
                    title: "Hapus Produk?",
                    text: "Data Produk ini akan dihapus secara permanen!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            data: {
                                'id': id,
                                '_token': "{{ csrf_token() }}"
                            },
                            type: 'DELETE',
                            url: "{{ url('admin/manage-master/products') }}",
                            beforeSend: function() {
                                $.LoadingOverlay("show");
                            },
                            complete: function() {
                                $.LoadingOverlay("hide");
                            },
                            success: function(data) {
                                Swal.fire({
                                    title: "Berhasil",
                                    text: data.message,
                                    icon: "success"
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(err) {
                                Swal.fire({
                                    title: "Gagal",
                                    text: err.responseJSON?.message || "Gagal menghapus produk",
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
@endsection