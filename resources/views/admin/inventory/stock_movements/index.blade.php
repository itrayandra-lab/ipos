@extends('master')

@section('title', 'Stock Movements')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Stock Movements</h1>
            <div class="section-header-button">
                <a href="{{ route('admin.stock_movements.create') }}" class="btn btn-primary">Tambah Movement</a>
            </div>
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
            swal({
                title: 'Kirim Barang?',
                text: 'Stok akan dikurangi dari gudang asal.',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willShip) => {
                if (willShip) {
                    let url = "{{ route('admin.stock_movements.ship', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.status === 'success') {
                                swal('Berhasil', res.message, 'success');
                                table.ajax.reload();
                            } else {
                                swal('Error', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-receive', function() {
            let id = $(this).data('id');
            swal({
                title: 'Terima Barang?',
                text: 'Stok akan ditambahkan ke gudang tujuan.',
                icon: 'info',
                buttons: true,
            }).then((willReceive) => {
                if (willReceive) {
                    let url = "{{ route('admin.stock_movements.receive', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.status === 'success') {
                                swal('Berhasil', res.message, 'success');
                                table.ajax.reload();
                            } else {
                                swal('Error', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
