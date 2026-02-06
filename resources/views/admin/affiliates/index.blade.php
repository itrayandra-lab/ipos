@extends('master')
@section('title', 'Data Affiliate User')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Affiliate User</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Data Affiliate User</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Affiliate User</h2>
                <p class="section-lead">Berikut adalah Data Affiliate User (Dokter, Reseller, Influencer, dll).</p>
                <div class="card">
                    <div class="card-header">
                        <h4>Data Seluruh Affiliate</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                                Tambah Affiliate
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Fee Default</th>
                                    <th>Status</th>
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Affiliate User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama User <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Tipe User <span class="text-danger">*</span></label>
                            <select name="type_id" class="form-control" required>
                                <option value="">-- Pilih Tipe --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Metode Fee <span class="text-danger">*</span></label>
                                    <select name="fee_method" class="form-control" id="fee_method_add" required>
                                        <option value="percent">Persentase (%)</option>
                                        <option value="nominal">Nominal (Rp)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label id="fee_label_add">Nilai Fee (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="fee_value" required min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active" class="form-control">
                                <option value="1">Aktif</option>
                                <option value="0">Non Aktif</option>
                            </select>
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Affiliate User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama User <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label>Tipe User <span class="text-danger">*</span></label>
                            <select name="type_id" class="form-control" id="edit_type_id" required>
                                <option value="">-- Pilih Tipe --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Metode Fee <span class="text-danger">*</span></label>
                                    <select name="fee_method" class="form-control" id="fee_method_edit" required>
                                        <option value="percent">Persentase (%)</option>
                                        <option value="nominal">Nominal (Rp)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label id="fee_label_edit">Nilai Fee (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="fee_value" id="edit_fee_value" required min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active" class="form-control" id="edit_is_active">
                                <option value="1">Aktif</option>
                                <option value="0">Non Aktif</option>
                            </select>
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
                    url: "{{ route('admin.affiliates.all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'name', name: 'name' },
                    { data: 'type_name', name: 'type.name' },
                    { data: 'fee_display', name: 'fee_value' },
                    { data: 'status', name: 'is_active' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Dynamic Label Update
            $('#fee_method_add, #fee_method_edit').change(function() {
                var isPercent = $(this).val() == 'percent';
                var label = isPercent ? 'Nilai Fee (%)' : 'Nilai Fee (Rp)';
                var target = $(this).attr('id') == 'fee_method_add' ? '#fee_label_add' : '#fee_label_edit';
                $(target).html(label + ' <span class="text-danger">*</span>');
            });

            // Add Form
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.affiliates.store') }}",
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
                    url: "{{ route('admin.affiliates.get') }}",
                    method: "POST",
                    data: { id: id, _token: "{{ csrf_token() }}" },
                    success: function(data) {
                        $('#edit_id').val(data.id);
                        $('#edit_name').val(data.name);
                        $('#edit_type_id').val(data.type_id);
                        $('#fee_method_edit').val(data.fee_method).trigger('change');
                        $('#edit_fee_value').val(data.fee_value);
                        $('#edit_is_active').val(data.is_active);
                        $('#editModal').modal('show');
                    }
                });
            });

            // Edit Form
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.affiliates.update') }}",
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
                            url: "{{ route('admin.affiliates.delete') }}",
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
