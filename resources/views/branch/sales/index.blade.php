@extends('master')
@section('title', 'Input Penjualan Harian')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Input Penjualan Harian</h1>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Penjualan</h4>
                    <div class="card-header-action">
                        <a href="{{ route('branch.sales.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Input Penjualan</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-sales">
                            <thead>
                                <tr><th>#</th><th>No. Ref</th><th>Cabang</th><th>Tanggal</th><th>Jumlah Item</th><th>Total</th><th>Aksi</th></tr>
                            </thead>
                            <tbody></tbody>
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
    $('#table-sales').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('branch.sales.getall') }}",
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'reference_number'},
            {data: 'branch_name'},
            {data: 'sale_date'},
            {data: 'total_items', orderable: false},
            {data: 'total_amount'},
            {data: 'action', orderable: false, searchable: false},
        ]
    });

    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Penjualan?',
            text: 'Stok akan dikembalikan dan data tidak bisa dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('branch.sales.destroy', '') }}/' + id,
                    type: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Terhapus!', res.message, 'success');
                            $('#table-sales').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
