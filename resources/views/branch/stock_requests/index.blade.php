@extends('master')
@section('title', 'Pengajuan Barang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pengajuan Barang ke Pusat</h1>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Pengajuan</h4>
                    <div class="card-header-action">
                        <a href="{{ route('branch.stock_requests.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Buat Pengajuan</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-requests">
                            <thead>
                                <tr>
                                    <th>#</th><th>No. Referensi</th><th>Tanggal</th><th>Jumlah Item</th><th>Status</th><th>Aksi</th>
                                </tr>
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
    $('#table-requests').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('branch.stock_requests.getall') }}",
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'reference_number'},
            {data: 'created_at', render: d => new Date(d).toLocaleDateString('id-ID')},
            {data: 'total_items', orderable: false},
            {data: 'status', orderable: false},
            {data: 'action', orderable: false, searchable: false},
        ]
    });
});
</script>
@endpush
