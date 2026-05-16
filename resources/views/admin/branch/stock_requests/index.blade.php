@extends('master')
@section('title', 'Pengajuan Barang Cabang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pengajuan Barang dari Cabang</h1>
        </div>
        <div class="section-body">
            <div class="row mb-3">
                <div class="col-auto">
                    <label class="mr-2 mt-2">Filter Status:</label>
                </div>
                @foreach(['','pending','approved','rejected','shipped','received'] as $st)
                <div class="col-auto">
                    <button class="btn btn-sm {{ $st == '' ? 'btn-secondary' : 'btn-outline-secondary' }} btn-filter" data-status="{{ $st }}">
                        {{ $st == '' ? 'Semua' : ucfirst($st) }}
                    </button>
                </div>
                @endforeach
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-requests">
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
let table;
$(document).ready(function() {
    table = $('#table-requests').DataTable({
        processing: true, serverSide: true,
        ajax: { url: "{{ route('admin.branch.stock_requests.getall') }}", data: d => { d.status = activeStatus; } },
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

    let activeStatus = '';
    $('.btn-filter').on('click', function() {
        activeStatus = $(this).data('status');
        $('.btn-filter').removeClass('btn-secondary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');
        table.ajax.reload();
    });
});
</script>
@endpush
