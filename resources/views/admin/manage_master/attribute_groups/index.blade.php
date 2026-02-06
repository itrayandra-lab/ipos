@extends('master')
@section('title', 'Data Grup Atribut')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Grup Atribut</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Master Data</div>
                    <div class="breadcrumb-item">Grup Atribut</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Grup Atribut</h2>
                <p class="section-lead">Manajemen grup atribut untuk pengelompokan (Contoh: WARNA, UKURAN, TIPE_AFFILIATE).</p>
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Grup Atribut</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                                Tambah Grup
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Nama Grup</th>
                                    <th>Kode</th>
                                    <th width="150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Grup Atribut</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Grup <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="Contoh: Warna">
                        </div>
                        <div class="form-group">
                            <label>Kode Grup (Unik) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" required placeholder="Contoh: COLOR">
                            <small class="text-muted">Gunakan huruf kapital dan tanpa spasi (disarankan).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Grup Atribut</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Grup <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label>Kode Grup (Unik) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" id="edit_code" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('.table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.manage_master.attribute_groups.all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Add Form
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.manage_master.attribute_groups.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    success: function(resp) {
                        $.LoadingOverlay("hide");
                        if(resp.status) {
                            $('#addModal').modal('hide');
                            $('#addForm')[0].reset();
                            table.ajax.reload();
                            swal('Berhasil', resp.message, 'success');
                        } else {
                            swal('Gagal', resp.message, 'error');
                        }
                    },
                    error: function(err) {
                        $.LoadingOverlay("hide");
                        swal('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            });

            // Edit Button
            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ route('admin.manage_master.attribute_groups.get') }}",
                    method: "POST",
                    data: { id: id, _token: "{{ csrf_token() }}" },
                    success: function(data) {
                        $('#edit_id').val(data.id);
                        $('#edit_name').val(data.name);
                        $('#edit_code').val(data.code);
                        $('#editModal').modal('show');
                    }
                });
            });

            // Edit Form
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.manage_master.attribute_groups.update') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    success: function(resp) {
                        $.LoadingOverlay("hide");
                        if(resp.status) {
                            $('#editModal').modal('hide');
                            table.ajax.reload();
                            swal('Berhasil', resp.message, 'success');
                        } else {
                            swal('Gagal', resp.message, 'error');
                        }
                    },
                    error: function(err) {
                        $.LoadingOverlay("hide");
                        swal('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            });

            // Delete Button
            $(document).on('click', '.delete', function() {
                var id = $(this).data('id');
                swal({
                    title: "Hapus Data?",
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: "{{ route('admin.manage_master.attribute_groups.delete') }}",
                            method: "DELETE",
                            data: { id: id, _token: "{{ csrf_token() }}" },
                            success: function(resp) {
                                if(resp.status) {
                                    table.ajax.reload();
                                    swal('Berhasil', resp.message, 'success');
                                } else {
                                    swal('Gagal', resp.message, 'error');
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
