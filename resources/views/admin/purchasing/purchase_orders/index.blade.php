@extends('master')

@section('title', 'Purchase Orders')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Order Pembelian</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item">Order Pembelian</div>
            </div>
            <div class="section-header-button">
                <a href="{{ route('admin.purchasing.purchase_orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat PO Baru
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Order Pembelian (PO)</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-po">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. PO</th>
                                            <th>Tanggal</th>
                                            <th>Supplier</th>
                                            <th>Total</th>
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
        table = $('#table-po').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.purchasing.purchase_orders.getall') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'po_number', name: 'po_number' },
                { data: 'po_date', name: 'po_date' },
                { data: 'supplier_name', name: 'supplier.name' },
                { 
                    data: 'total', 
                    name: 'total',
                    render: function(data) {
                        return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        let badges = {
                            'draft': 'secondary',
                            'submitted': 'info',
                            'approved': 'primary',
                            'received': 'success',
                            'cancelled': 'danger'
                        };
                        return `<span class="badge badge-${badges[data]}">${data.toUpperCase()}</span>`;
                    }
                },
                { data: 'created_name', name: 'creator.name' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Delete Purchase Order
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            swal({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus Purchase Order ini?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('admin.purchasing.purchase_orders.delete') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", id: id },
                        success: function(res) {
                            if (res.status === 'success') {
                                table.ajax.reload();
                                swal('Berhasil', res.message, 'success');
                            }
                        },
                        error: function(err) {
                            swal('Error', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                        }
                    });
                }
            });
        });
    });

</script>
@endpush
