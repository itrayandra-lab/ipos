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
                                <tr><th>#</th><th>No. Ref</th><th>Tanggal</th><th>Jumlah Item</th><th>Total</th><th>Aksi</th></tr>
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
            {data: 'sale_date'},
            {data: 'total_items', orderable: false},
            {data: 'total_amount'},
            {data: 'action', orderable: false, searchable: false},
        ]
    });
});
</script>
@endpush
