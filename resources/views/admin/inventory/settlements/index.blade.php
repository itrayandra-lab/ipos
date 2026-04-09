@extends('master')

@section('title', 'Settlement Gudang')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Settlement Gudang</h1>
            <div class="section-header-button">
                <a href="{{ route('admin.settlements.create') }}" class="btn btn-primary">Buat Settlement</a>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select id="filter-warehouse" class="form-control selectric">
                                <option value="">Semua Gudang</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-settlements">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>No. Settlement</th>
                                    <th>Gudang</th>
                                    <th>Periode</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
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
    var table = $('#table-settlements').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.settlements.getall') }}",
            data: function(d) {
                d.warehouse_id = $('#filter-warehouse').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'settlement_no'},
            {data: 'warehouse_name'},
            {data: 'period'},
            {data: 'total_amount'},
            {data: 'status'},
            {data: 'action', orderable: false, searchable: false},
        ]
    });

    $('#filter-warehouse').on('change', function() { table.ajax.reload(); });

    $(document).on('click', '.btn-submit', function() {
        let id = $(this).data('id');
        swal({ title: 'Submit untuk Verifikasi?', icon: 'info', buttons: true })
            .then(ok => ok && $.post(`/admin/purchasing/settlements/${id}/submit`, { _token: '{{ csrf_token() }}' }, res => {
                if (res.status === 'success') { swal('Berhasil', res.message, 'success'); table.ajax.reload(); }
                else swal('Error', res.message, 'error');
            }));
    });

    $(document).on('click', '.btn-verify', function() {
        let id = $(this).data('id');
        swal({ title: 'Verifikasi Settlement?', text: 'Settlement ini akan disetujui.', icon: 'warning', buttons: true, dangerMode: false })
            .then(ok => ok && $.post(`/admin/purchasing/settlements/${id}/verify`, { _token: '{{ csrf_token() }}' }, res => {
                if (res.status === 'success') { swal('Diverifikasi!', res.message, 'success'); table.ajax.reload(); }
                else swal('Error', res.message, 'error');
            }));
    });

    $(document).on('click', '.btn-reject', function() {
        let id = $(this).data('id');
        swal({ title: 'Tolak Settlement?', content: { element: 'input', attributes: { placeholder: 'Alasan penolakan...' } }, icon: 'warning', buttons: true, dangerMode: true })
            .then(reason => {
                if (reason !== null) {
                    $.post(`/admin/purchasing/settlements/${id}/reject`, { _token: '{{ csrf_token() }}', reason: reason }, res => {
                        if (res.status === 'success') { swal('Ditolak', res.message, 'success'); table.ajax.reload(); }
                        else swal('Error', res.message, 'error');
                    });
                }
            });
    });
</script>
@endpush
