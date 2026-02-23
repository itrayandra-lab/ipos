@extends('master')
@section('title', 'Data Sub Kategori')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Sub Kategori</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Data Sub Kategori</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Sub Kategori</h2>
                <p class="section-lead">Berikut adalah Data Sub Kategori yang terhubung dengan Kategori Utama.</p>
                <div class="card">
                    <div class="card-header">
                        <h4>Data Seluruh Sub Kategori</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                                Tambah Sub Kategori
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="table-sub-category">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Kategori Utama</th>
                                    <th>Nama Sub Kategori</th>
                                    <th>Deskripsi</th>
                                    <th width="10px">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sub Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                </div>
                <form action="{{ url('admin/manage-master/sub-categories') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Kategori Utama</label>
                            <select class="form-control select2" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Sub Kategori</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sub Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                </div>
                <form action="{{ url('admin/manage-master/sub-categories/update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Kategori Utama</label>
                            <select class="form-control select2" name="category_id" id="edit-category-id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nama Sub Kategori</label>
                            <input type="text" class="form-control" name="name" id="edit-name" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="description" id="edit-description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#table-sub-category').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('admin/manage-master/sub-categories/all') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'action', name: 'action' }
                ]
            });

            $(document).on('click', '.edit', function() {
                let id = $(this).data('id');
                $.post("{{ url('admin/manage-master/sub-categories/get') }}", { id: id, _token: "{{ csrf_token() }}" }, function(data) {
                    $('#edit-id').val(data.id);
                    $('#edit-category-id').val(data.category_id).trigger('change');
                    $('#edit-name').val(data.name);
                    $('#edit-description').val(data.description);
                    $('#updateModal').modal('show');
                });
            });

            $(document).on('click', '.hapus', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                swal({
                    title: "Hapus Sub Kategori?",
                    text: "Menghapus sub kategori juga akan berdampak pada tipe produk yang terkait.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: "{{ url('admin/manage-master/sub-categories') }}",
                            type: 'DELETE',
                            data: { id: id, _token: "{{ csrf_token() }}" },
                            success: function(res) {
                                swal(res.message, { icon: "success" }).then(() => location.reload());
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
