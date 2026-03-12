@extends('master')
@section('title', 'Kelola Rekening Bank')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Kelola Rekening Bank</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Pengaturan</div>
                    <div class="breadcrumb-item active">Rekening Bank</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Rekening Bank</h4>
                        <div class="card-header-form">
                            <button class="btn btn-primary" id="btn-add">Tambah Rekening</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="bank-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Bank</th>
                                        <th>No Rekening</th>
                                        <th>Nama Pemilik</th>
                                        <th>Status</th>
                                        <th width="150">Aksi</th>
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

    <!-- Modal Form -->
    <div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="form-bank">
                    @csrf
                    <input type="hidden" name="id" id="bank-id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-title">Tambah Rekening Bank</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Bank</label>
                            <input type="text" name="bank_name" id="bank_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>No Rekening</label>
                            <input type="text" name="account_number" id="account_number" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Pemilik Rekening</label>
                            <input type="text" name="account_holder" id="account_holder" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#bank-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.bank_accounts.all') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'bank_name', name: 'bank_name' },
                    { data: 'account_number', name: 'account_number' },
                    { data: 'account_holder', name: 'account_holder' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            $('#btn-add').on('click', function() {
                $('#modal-title').text('Tambah Rekening Bank');
                $('#form-bank')[0].reset();
                $('#bank-id').val('');
                $('#modal-form').modal('show');
            });

            $('#form-bank').on('submit', function(e) {
                e.preventDefault();
                let id = $('#bank-id').val();
                let url = id ? "{{ route('admin.bank_accounts.update') }}" : "{{ route('admin.bank_accounts.store') }}";
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        swal('Berhasil', res.message, 'success');
                        $('#modal-form').modal('hide');
                        table.draw();
                    },
                    error: function(err) {
                        swal('Gagal', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                    }
                });
            });

            $(document).on('click', '.edit', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('admin.bank_accounts.get') }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', id: id },
                    success: function(res) {
                        $('#modal-title').text('Edit Rekening Bank');
                        $('#bank-id').val(res.id);
                        $('#bank_name').val(res.bank_name);
                        $('#account_number').val(res.account_number);
                        $('#account_holder').val(res.account_holder);
                        $('#modal-form').modal('show');
                    }
                });
            });

            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                swal({
                    title: 'Apakah anda yakin?',
                    text: 'Rekening bank akan dihapus!',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: "{{ route('admin.bank_accounts.delete') }}",
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}', id: id },
                            success: function(res) {
                                swal('Berhasil', res.message, 'success');
                                table.draw();
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
