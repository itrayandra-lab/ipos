@extends('master')

@section('title', 'Stock Movements')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Stock Movements</h1>
            @if(!auth()->user()->isFinance())
            <div class="section-header-button">
                <a href="{{ route('admin.stock_movements.create') }}" class="btn btn-primary">Tambah Movement</a>
            </div>
            @endif
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Inventory</a></div>
                <div class="breadcrumb-item">Stock Movements</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-stock-movements">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>No. Movement</th>
                                    <th>Asal</th>
                                    <th>Tujuan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
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
        var table = $('#table-stock-movements').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.stock_movements.getall') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'reference_number', name: 'reference_number'},
                {data: 'from', name: 'from', orderable: false},
                {data: 'to', name: 'to', orderable: false},
                {data: 'created_at', name: 'created_at', render: function(data) {
                    return new Date(data).toLocaleDateString('id-ID');
                }},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $(document).on('click', '.btn-ship', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Kirim Barang?',
                text: 'Stok akan dikurangi dari gudang asal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('admin.stock_movements.ship', ':id') }}".replace(':id', id);
                    Swal.fire({ title: 'Memproses...', text: 'Mohon tunggu sebentar.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.status === 'success') {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false })
                                    .then(() => table.ajax.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                            }
                        },
                        error: function(err) {
                            Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Terjadi kesalahan server.' });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-receive', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Penerimaan?',
                text: 'Stok akan ditambahkan ke gudang tujuan.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Terima!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('admin.stock_movements.receive', ':id') }}".replace(':id', id);
                    Swal.fire({ title: 'Memproses...', text: 'Mohon tunggu sebentar.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.status === 'success') {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false })
                                    .then(() => table.ajax.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                            }
                        },
                        error: function(err) {
                            Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Terjadi kesalahan server.' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
