@extends('master')
@section('title', 'Return Barang ke Pusat')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Return Barang ke Pusat</h1>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Return</h4>
                    <div class="card-header-action">
                        <a href="{{ route('branch.returns.create') }}" class="btn btn-warning"><i class="fas fa-plus mr-1"></i> Buat Return</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-returns">
                            <thead>
                                <tr><th>#</th><th>No. Ref</th><th>Tanggal</th><th>Jumlah Item</th><th>Status</th><th>Aksi</th></tr>
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
    $('#table-returns').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('branch.returns.getall') }}",
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
