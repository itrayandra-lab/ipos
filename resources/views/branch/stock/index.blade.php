@extends('master')
@section('title', 'Stok Cabang Saya')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Stok Cabang Saya</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><i class="fas fa-warehouse mr-1"></i> {{ $warehouse->name ?? '-' }}</div>
                <div class="breadcrumb-item active">Stok</div>
            </div>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-branch-stock">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produk</th>
                                    <th>Variant</th>
                                    <th>Batch No</th>
                                    <th class="text-center">Stok</th>
                                    <th>Exp. Date</th>
                                    <th>Harga Beli</th>
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
    $('#table-branch-stock').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('branch.stock.getall') }}",
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'product_name'},
            {data: 'variant_name'},
            {data: 'batch_no'},
            {data: 'qty', className: 'text-center font-weight-bold'},
            {data: 'expiry_date'},
            {data: 'buy_price'},
        ]
    });
});
</script>
@endpush
