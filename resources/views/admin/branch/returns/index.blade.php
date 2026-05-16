@extends('master')
@section('title', 'Return Barang dari Cabang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Return Barang dari Cabang</h1>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-returns">
                            <thead>
                                <tr><th>#</th><th>No. Ref</th><th>Gudang Cabang</th><th>Diajukan oleh</th><th>Tgl</th><th>Item</th><th>Status</th><th>Aksi</th></tr>
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
        ajax: "{{ route('admin.branch.returns.getall') }}",
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'reference_number'},
            {data: 'warehouse_name', orderable: false},
            {data: 'requester_name', orderable: false},
            {data: 'created_at', render: d => new Date(d).toLocaleDateString('id-ID')},
            {data: 'total_items', orderable: false},
            {data: 'status', orderable: false},
            {data: 'action', orderable: false, searchable: false},
        ]
    });
});
</script>
@endpush
