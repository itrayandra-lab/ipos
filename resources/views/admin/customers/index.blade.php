@extends('master')
@section('title', 'Manage Customers')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Manage Customers</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Manage Customers</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Customer</h4>
                        <div class="card-header-form">
                            <div class="d-flex align-items-center">
                                <select id="filter-status" class="form-control form-control-sm mr-2" style="width: 150px;">
                                    <option value="all">Semua</option>
                                    <option value="frequent">Paling Sering Belanja</option>
                                    <option value="newest">Terbaru</option>
                                    <option value="inactive">Tidak Aktif (>30 hari)</option>
                                </select>
                                <button class="btn btn-primary" id="btn-add">Tambah Customer</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="customer-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama</th>
                                        <th>WA/Phone</th>
                                        <th>Email</th>
                                        <th>Total Transaksi</th>
                                        <th>Total Belanja</th>
                                        <th>Last Transaksi</th>
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
                <form id="form-customer">
                    @csrf
                    <input type="hidden" name="id" id="customer-id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-title">Tambah Customer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Customer</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>WA/Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control" required placeholder="Contoh: 08123456789">
                        </div>
                        <div class="form-group">
                            <label>Email (Opsional)</label>
                            <input type="email" name="email" id="email" class="form-control">
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
            let table = $('#customer-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.customers.all') }}",
                    data: function(d) {
                        d.filter = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'phone', name: 'phone' },
                    { data: 'email', name: 'email' },
                    { data: 'total_transactions', name: 'total_transactions', searchable: false },
                    { data: 'total_spending_formatted', name: 'total_spending', searchable: false },
                    { data: 'last_transaction_formatted', name: 'last_transaction', searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            $('#filter-status').on('change', function() {
                table.draw();
            });

            $('#btn-add').on('click', function() {
                $('#modal-title').text('Tambah Customer');
                $('#form-customer')[0].reset();
                $('#customer-id').val('');
                $('#modal-form').modal('show');
            });

            $('#form-customer').on('submit', function(e) {
                e.preventDefault();
                let id = $('#customer-id').val();
                let url = id ? "{{ route('admin.customers.update') }}" : "{{ route('admin.customers.store') }}";
                
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
                    url: "{{ route('admin.customers.get') }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', id: id },
                    success: function(res) {
                        $('#modal-title').text('Edit Customer');
                        $('#customer-id').val(res.id);
                        $('#name').val(res.name);
                        $('#phone').val(res.phone);
                        $('#email').val(res.email);
                        $('#modal-form').modal('show');
                    }
                });
            });

            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                swal({
                    title: 'Apakah anda yakin?',
                    text: 'Data customer akan dihapus!',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: "{{ route('admin.customers.delete') }}",
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
