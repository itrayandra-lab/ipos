@extends('master')

@section('title', 'Return Barang ke Supplier')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Return Barang ke Supplier</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item">Return Barang</div>
            </div>
            <div class="section-header-button">
                <a href="{{ route('admin.purchasing.returns.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Return Baru
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Return Barang</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-returns">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. Return</th>
                                            <th>Supplier</th>
                                            <th>Gudang</th>
                                            <th>Tanggal Return</th>
                                            <th>Status</th>
                                            <th>Dibuat Oleh</th>
                                            <th>Action</th>
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
        table = $('#table-returns').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.purchasing.returns.getall') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'return_number', name: 'return_number' },
                { data: 'supplier_name', name: 'supplier.name' },
                { data: 'warehouse_name', name: 'warehouse.name' },
                { data: 'return_date', name: 'return_date' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        let badge = data === 'completed' ? 'success' : (data === 'cancelled' ? 'danger' : 'warning');
                        return `<span class="badge badge-${badge}">${data.toUpperCase()}</span>`;
                    }
                },
                { data: 'user_name', name: 'user.name' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus Return?',
                text: "Apakah Anda yakin ingin menghapus data return ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/purchasing/returns') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Dihapus!', response.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
