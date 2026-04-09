@extends('master')
@section('title', 'Data Product Tier')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Product Tier</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Data Product Tier</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Product Tier</h2>
                <p class="section-lead">Berikut adalah Data Product Tier (Kategori Harga).</p>
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>Data Seluruh Product Tier</h4>
                        <div class="card-header-form">
                            <div class="dropdown d-inline dropleft">
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" aria-haspopup="true"
                                    data-toggle="dropdown" aria-expanded="false">
                                    Tambah
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" data-toggle="modal" data-target="#addModal"
                                            href="#">Input Manual</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Nama Tier</th>
                                    <th>Multiplier (X)</th>
                                    <th width="10px">Action</th>
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
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Product Tier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ url('admin/manage-master/product-tiers') }}" method="POST" class="needs-validation" novalidate="">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Tier</label>
                            <input type="text" placeholder="Contoh: Hero" class="form-control" name="name" required="">
                        </div>
                        <div class="form-group">
                            <label>Multiplier</label>
                            <input type="number" step="0.01" placeholder="Contoh: 2.5" class="form-control" name="multiplier" required="">
                            <small class="text-muted">Harga Jual = Harga Modal x Multiplier</small>
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
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Product Tier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ url('admin/manage-master/product-tiers/update') }}" method="POST" class="needs-validation" novalidate="">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Tier</label>
                            <input type="text" placeholder="Contoh: Hero" class="form-control" name="name" required="" id="name">
                        </div>
                        <div class="form-group">
                            <label>Multiplier</label>
                            <input type="number" step="0.01" placeholder="Contoh: 2.5" class="form-control" name="multiplier" required="" id="multiplier">
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
            $('.table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/manage-master/product-tiers/all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'name', name: 'name' },
                    { data: 'multiplier_display', name: 'multiplier_display' },
                    { data: 'action', name: 'action' }
                ]
            });

            $('.table').on('click', '.edit[data-id]', function(e) {
                e.preventDefault();
                $.ajax({
                    data: {
                        'id': $(this).data('id'),
                        '_token': "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    url: "{{ url('admin/manage-master/product-tiers/get') }}",
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    complete: function() {
                        $.LoadingOverlay("hide");
                    },
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#name').val(data.name);
                        $('#multiplier').val(data.multiplier);
                        $('#updateModal').modal('show');
                    }
                });
            });

            $('.table').on('click', '.hapus[data-id]', function(e) {
                e.preventDefault();
                swal({
                    title: "Hapus Tier?",
                    text: "Data Tier ini akan dihapus secara permanen!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            data: {
                                'id': $(this).data('id'),
                                '_token': "{{ csrf_token() }}"
                            },
                            type: 'DELETE',
                            url: "{{ url('admin/manage-master/product-tiers') }}",
                            beforeSend: function() {
                                $.LoadingOverlay("show");
                            },
                            complete: function() {
                                $.LoadingOverlay("hide");
                            },
                            success: function(data) {
                                swal(data.message).then(() => {
                                    location.reload();
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
