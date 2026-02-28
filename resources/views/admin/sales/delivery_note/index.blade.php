@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Surat Jalan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Surat Jalan</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Surat Jalan</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.sales.delivery_notes.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Surat Jalan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="dn-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>No Surat Jalan</th>
                                    <th>Customer</th>
                                    <th>Tanggal</th>
                                    <th style="width: 20%">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dn-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.sales.delivery_notes.all') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'delivery_note_no', name: 'delivery_note_no' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });

    function deleteDeliveryNote(id) {
        if (confirm('Apakah Anda yakin ingin menghapus Surat Jalan ini? Stok barang akan dikembalikan.')) {
            $.ajax({
                url: "{{ url('admin/sales/delivery-notes') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $('#dn-table').DataTable().ajax.reload();
                        iziToast.success({
                            title: 'Berhasil',
                            message: 'Surat Jalan berhasil dihapus',
                            position: 'topRight'
                        });
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                }
            });
        }
    }
</script>
@endpush
