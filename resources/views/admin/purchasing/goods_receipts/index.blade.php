@extends('master')

@section('title', 'Surat Penerimaan Barang')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Penerimaan Barang</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item">Penerimaan Barang</div>
            </div>
            <div class="section-header-button">
                <a href="{{ route('admin.purchasing.goods_receipts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Terima Barang Baru
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Surat Penerimaan Barang (SJ Internal)</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-gr">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. SJ Intern</th>
                                            <th>No. Surat Jalan Supplier</th>
                                            <th>No. PO</th>
                                            <th>Supplier</th>
                                            <th>Tanggal Terima</th>
                                            <th>Diterima Oleh</th>
                                            <th>Status</th>
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
        table = $('#table-gr').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.purchasing.goods_receipts.getall') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'sj_number', name: 'sj_number' },
                { data: 'delivery_note_number', name: 'delivery_note_number' },
                { data: 'po_number', name: 'po_number' },
                { data: 'supplier_name', name: 'supplier.name' },
                { data: 'received_date', name: 'received_date' },
                { data: 'received_by_name', name: 'receiver.name' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        let badge = data === 'confirmed' ? 'success' : 'secondary';
                        return `<span class="badge badge-${badge}">${data.toUpperCase()}</span>`;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            swal({
                title: 'Hapus Penerimaan Barang?',
                text: 'Stok barang terkait akan dikembalikan. Data tidak bisa dikembalikan!',
                icon: 'warning',
                buttons: {
                    cancel: 'Batal',
                    confirm: { text: 'Ya, Hapus', className: 'btn-danger' }
                },
                dangerMode: true,
            }).then((confirm) => {
                if (confirm) {
                    $.ajax({
                        url: "{{ route('admin.purchasing.goods_receipts.destroy', '') }}/" + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                swal('Berhasil', res.message, 'success');
                                table.ajax.reload();
                            } else {
                                swal('Error', res.message, 'error');
                            }
                        },
                        error: function(err) {
                            swal('Error', err.responseJSON?.message || 'Gagal menghapus data', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
