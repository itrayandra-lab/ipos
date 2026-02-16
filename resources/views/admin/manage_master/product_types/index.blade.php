@extends('master')
@section('title', 'Data Tipe Produk')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Tipe Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Data Tipe Produk</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Tipe Produk</h2>
                <p class="section-lead">Berikut adalah Data Tipe Produk (Level 3 - Terakhir).</p>
                <div class="card">
                    <div class="card-header">
                        <h4>Data Seluruh Tipe Produk</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                                Tambah Tipe Produk
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="table-product-type">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Nama Tipe Produk</th>
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
                    <h5 class="modal-title">Tambah Tipe Produk</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                </div>
                <form action="{{ url('admin/manage-master/product-types') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Tipe Produk</label>
                            <input type="text" class="form-control" name="name" required>
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
                    <h5 class="modal-title">Edit Tipe Produk</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                </div>
                <form action="{{ url('admin/manage-master/product-types/update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Tipe Produk</label>
                            <input type="text" class="form-control" name="name" id="edit-name" required>
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
            $('#table-product-type').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('admin/manage-master/product-types/all') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'name', name: 'name' },
                    { data: 'action', name: 'action' }
                ]
            });

            $(document).on('click', '.edit', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/manage-master/product-types/get') }}", { id: id }, function(data) {
                    $('#edit-id').val(data.id);
                    $('#edit-name').val(data.name);
                    $('#updateModal').modal('show');
                });
            });

            $(document).on('click', '.hapus', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                swal({
                    title: "Hapus Tipe Produk?",
                    text: "Produk yang menggunakan tipe ini akan kehilangan referensi tipe produknya.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: "{{ url('admin/manage-master/product-types') }}",
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
