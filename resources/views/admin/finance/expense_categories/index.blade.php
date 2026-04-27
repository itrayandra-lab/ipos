@extends('master')
@section('title', 'Kategori Pengeluaran')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Kategori Pengeluaran</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item">Expense Categories</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Kategori</h4>
                    <div class="card-header-form">
                        <button class="btn btn-primary" onclick="addCategory()">
                            <i class="fas fa-plus mr-1"></i> Tambah Kategori
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="category-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Kategori</th>
                                    <th>Keterangan</th>
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

<!-- Modal Category -->
<div class="modal fade" id="modalCategory" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formCategory">
                @csrf
                <input type="hidden" id="category_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" class="form-control" name="name" id="cat_name" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea class="form-control" name="description" id="cat_desc" rows="3"></textarea>
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

<script>
    $(document).ready(function() {
        var table = $('#category-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.finance.expense_categories.all') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        window.addCategory = function() {
            $('#category_id').val('');
            $('#formCategory')[0].reset();
            $('#modalTitle').text('Tambah Kategori');
            $('#modalCategory').modal('show');
        };

        window.editCategory = function(id) {
            $.get("{{ url('admin/finance/expense-categories') }}/" + id, function(data) {
                $('#category_id').val(data.id);
                $('#cat_name').val(data.name);
                $('#cat_desc').val(data.description);
                $('#modalTitle').text('Edit Kategori');
                $('#modalCategory').modal('show');
            });
        };

        $('#formCategory').on('submit', function(e) {
            e.preventDefault();
            var id = $('#category_id').val();
            var url = id ? "{{ url('admin/finance/expense-categories') }}/" + id : "{{ route('admin.finance.expense_categories.store') }}";
            
            $.ajax({
                url: url,
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    $('#modalCategory').modal('hide');
                    table.ajax.reload();
                    iziToast.success({ title: 'Berhasil', message: 'Data berhasil disimpan', position: 'topRight' });
                }
            });
        });

        window.deleteCategory = function(id) {
            if (confirm('Yakin ingin menghapus kategori ini? Semua data pengeluaran dalam kategori ini juga akan terhapus.')) {
                $.ajax({
                    url: "{{ url('admin/finance/expense-categories') }}/" + id,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: 'Data berhasil dihapus', position: 'topRight' });
                    }
                });
            }
        };
    });
</script>
@endsection
