@extends('master')
@section('title', 'Data Atribut')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Atribut</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Master Data</div>
                    <div class="breadcrumb-item">Atribut</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Atribut</h2>
                <p class="section-lead">Manajemen nilai atribut (Contoh: Putih, Merah untuk grup WARNA - atau Dokter, Reseller untuk grup TIPE_AFFILIATE).</p>
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Atribut</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                                Tambah Atribut
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Grup</th>
                                    <th>Nama Atribut</th>
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
                    <h5 class="modal-title">Tambah Atribut</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Grup Atribut <span class="text-danger">*</span></label>
                            <select name="attribute_group_id" class="form-control" required>
                                <option value="">-- Pilih Grup --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Atribut <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="Contoh: Merah">
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
                    <h5 class="modal-title">Edit Atribut</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Grup Atribut <span class="text-danger">*</span></label>
                            <select name="attribute_group_id" class="form-control" id="edit_group_id" required>
                                <option value="">-- Pilih Grup --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Atribut <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
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
                    url: "{{ route('admin.manage_master.attributes.all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'group_name', name: 'group.name' },
                    { data: 'name', name: 'name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Add Form
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.manage_master.attributes.store') }}",
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
                    url: "{{ route('admin.manage_master.attributes.get') }}",
                    method: "POST",
                    data: { id: id, _token: "{{ csrf_token() }}" },
                    success: function(data) {
                        $('#edit_id').val(data.id);
                        $('#edit_group_id').val(data.attribute_group_id);
                        $('#edit_name').val(data.name);
                        $('#editModal').modal('show');
                    }
                });
            });

            // Edit Form
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.manage_master.attributes.update') }}",
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
                            url: "{{ route('admin.manage_master.attributes.delete') }}",
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
