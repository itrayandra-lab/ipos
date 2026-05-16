@extends('master')
@section('title', 'Pengajuan Dana')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pengajuan Dana</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item active">Pengajuan Dana</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('message'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ session('message') }}
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Daftar Pengajuan Dana</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.finance.fund_requests.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Buat Pengajuan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="fund-request-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Pengaju</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(function() {
        $('#fund-request-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("admin.finance.fund_requests.all") }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'request_code', name: 'request_code' },
                { data: 'title', name: 'title' },
                { data: 'category', name: 'category', orderable: false, searchable: false },
                { data: 'requester', name: 'requester' },
                { data: 'amount', name: 'amount' },
                { data: 'status', name: 'status', orderable: false },
                { data: 'created_at', name: 'created_at', render: function(data) {
                    return moment(data).format('DD MMM YYYY');
                }},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });

    function deleteRequest(id) {
        swal({
            title: 'Hapus Pengajuan?',
            text: 'Data pengajuan ini akan dihapus permanen!',
            icon: 'warning',
            buttons: ['Batal', 'Hapus'],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '{{ url("admin/finance/fund-requests") }}/' + id + '/delete',
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            iziToast.success({ title: 'Berhasil', message: response.message, position: 'topRight' });
                            $('#fund-request-table').DataTable().ajax.reload();
                        } else {
                            iziToast.error({ title: 'Gagal', message: response.message, position: 'topRight' });
                        }
                    },
                    error: function() {
                        iziToast.error({ title: 'Error', message: 'Terjadi kesalahan pada server', position: 'topRight' });
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
