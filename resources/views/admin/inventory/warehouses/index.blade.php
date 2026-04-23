@extends('master')

@section('title', 'Manajemen Gudang')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Manajemen Gudang</h1>
            <div class="section-header-button">
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-warehouse">Tambah Gudang</button>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-warehouses">
                            <thead>
                                <tr>
                                    <th width="30">#</th>
                                    <th>Nama Gudang</th>
                                    <th>Alamat</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th width="100">Action</th>
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

<!-- Modal Warehouse -->
<div class="modal fade" id="modal-warehouse" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="form-warehouse">
            @csrf
            <input type="hidden" name="id" id="warehouse-id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Gudang</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Gudang <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="warehouse-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address" id="warehouse-address" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tipe <span class="text-danger">*</span></label>
                        <select name="type" id="warehouse-type" class="form-control" required>
                            <option value="main">Gudang Utama</option>
                            <option value="branch">Store / Cabang</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" id="warehouse-status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#table-warehouses').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.settings.warehouses.getall') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'address', name: 'address'},
                {data: 'type', name: 'type', render: function(data) {
                    return data === 'main' ? 'Utama' : 'Cabang';
                }},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $('#form-warehouse').on('submit', function(e) {
            e.preventDefault();
            let id = $('#warehouse-id').val();
            let url = id ? "{{ route('admin.settings.warehouses.update') }}" : "{{ route('admin.settings.warehouses.store') }}";
            let btn = $(this).find('button[type="submit"]');
            
            btn.addClass('btn-progress').attr('disabled', true);
            
            $.ajax({
                url: url,
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    if (res.status === 'success') {
                        $('#modal-warehouse').modal('hide');
                        table.ajax.reload();
                        swal('Berhasil', res.message, 'success');
                    } else {
                        swal('Gagal', res.message, 'error');
                    }
                },
                error: function(err) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    swal('Error', err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan pada server', 'error');
                }
            });
        });

        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let btn = $(this);
            btn.addClass('btn-progress').attr('disabled', true);

            $.post("{{ route('admin.settings.warehouses.get') }}", { _token: "{{ csrf_token() }}", id: id }, function(res) {
                btn.removeClass('btn-progress').attr('disabled', false);
                if (res.status === 'success') {
                    $('#warehouse-id').val(res.data.id);
                    $('#warehouse-name').val(res.data.name);
                    $('#warehouse-address').val(res.data.address);
                    $('#warehouse-type').val(res.data.type);
                    $('#warehouse-status').val(res.data.status);
                    $('#modal-warehouse').modal('show');
                }
            }).fail(function() {
                btn.removeClass('btn-progress').attr('disabled', false);
                swal('Error', 'Gagal mengambil data gudang', 'error');
            });
        });

        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            swal({
                title: 'Hapus Gudang?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('admin.settings.warehouses.delete') }}",
                        method: 'DELETE',
                        data: { _token: "{{ csrf_token() }}", id: id },
                        success: function(res) {
                            if (res.status === 'success') {
                                table.ajax.reload();
                                swal('Berhasil', res.message, 'success');
                            } else {
                                swal('Gagal', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        $('#modal-warehouse').on('hidden.bs.modal', function() {
            $('#warehouse-id').val('');
            $('#form-warehouse')[0].reset();
        });
    });
</script>
@endpush
