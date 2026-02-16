@extends('master')
@section('title', 'Data Produk')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Data Produk</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Produk</h2>
                <p class="section-lead">Berikut adalah Data Produk.</p>
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi kesalahan!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>Data Seluruh Produk</h4>
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
                        <table class="table table-striped mt-5" id="products-table">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Nama</th>
                                    <th>Hierarki</th>
                                    <th>Merk</th>
                                    <th>Jumlah Varian</th>
                                    <th>Status</th>
                                    <th>Foto</th>
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
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Tambah Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ url('admin/manage-master/products') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" placeholder="Masukkan Nama Produk" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Merk</label>
                                    <select class="form-control" name="merek_id" required>
                                        <option value="">Pilih Merk</option>
                                        @foreach ($merek as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control select-category" name="category_id" id="add-category" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($categories as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Produk</label>
                                    <select class="form-control" name="product_type_id" id="add-product-type" required>
                                        <option value="">Pilih Tipe Produk</option>
                                        @foreach($productTypes as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Min. Stock Alert</label>
                            <input type="number" placeholder="Batas stok minimum untuk alert" class="form-control" name="min_stock_alert" required min="0" value="0">
                        </div>

                        <div class="form-group">
                            <label class="d-block">Varian Produk (SKU) <button type="button" class="btn btn-sm btn-success float-right mb-2" id="btn-add-variant"><i class="fas fa-plus"></i> Tambah Varian</button></label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="table-variants">
                                    <thead>
                                        <tr>
                                            <th>Netto</th>
                                            <th>Nama Varian</th>
                                            <th>SKU Code</th>
                                            <th>Harga Jual (Rp)</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="variants[0][netto]" class="form-control form-control-sm" placeholder="100ml" required></td>
                                            <td><input type="text" name="variants[0][name]" class="form-control form-control-sm" placeholder="Original" required></td>
                                            <td><input type="text" name="variants[0][sku]" class="form-control form-control-sm" placeholder="SKU001" required></td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm rupiah-variant" placeholder="Rp 0" required>
                                                <input type="hidden" name="variants[0][price]" class="raw-price-variant">
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" required>
                                <option value="Y">Aktif</option>
                                <option value="N">Non Aktif</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gambar Produk <small>(optional, multiple)</small></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="foto" name="foto[]" multiple accept="image/*">
                                <label class="custom-file-label" for="foto">Pilih gambar...</label>
                            </div>
                            <div id="image-preview-add" class="mt-3 d-flex flex-wrap"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btn-save-product">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ url('admin/manage-master/products/update') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="deleted_photos" id="deleted_photos">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" placeholder="Masukkan Nama Produk" class="form-control" name="name" id="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Merk</label>
                                    <select class="form-control" name="merek_id" id="merek_id" required>
                                        <option value="">Pilih Merk</option>
                                        @foreach ($merek as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control select-category" id="edit-category" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($categories as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipe Produk</label>
                                    <select class="form-control" name="product_type_id" id="edit-product-type" required>
                                        <option value="">Pilih Tipe Produk</option>
                                        @foreach($productTypes as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Min. Stock Alert</label>
                            <input type="number" placeholder="Batas stok minimum untuk alert" class="form-control" name="min_stock_alert" id="min_stock_alert" required min="0">
                        </div>

                        <div class="form-group">
                            <label class="d-block">Varian Produk (SKU) <button type="button" class="btn btn-sm btn-success float-right mb-2" id="btn-add-variant-edit"><i class="fas fa-plus"></i> Tambah Varian</button></label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="table-variants-edit">
                                    <thead>
                                        <tr>
                                            <th>Netto</th>
                                            <th>Nama Varian</th>
                                            <th>SKU Code</th>
                                            <th>Harga Jual (Rp)</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="Y">Aktif</option>
                                <option value="N">Non Aktif</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gambar Produk <small>(optional, multiple)</small></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="foto_update" name="foto[]" multiple accept="image/*">
                                <label class="custom-file-label" for="foto_update">Pilih gambar...</label>
                            </div>
                            <div id="image-preview-update" class="mt-3 d-flex flex-wrap"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btn-update-product">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pricing Details Modal -->
    <div class="modal fade" id="pricingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rincian Harga - <span id="pricing-product-name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="pricing-content">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Rupiah formatting function
        function formatRupiah(angka) {
            let number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return 'Rp ' + rupiah;
        }

        function getRawNumber(rupiah) {
            return rupiah.replace(/[^0-9,-]/g, '').replace(',', '.');
        }

        $(document).ready(function() {
            // Update file input label with selected file names
            function updateFileLabel(input, label) {
                let fileName = Array.from(input.files).map(file => file.name).join(', ');
                $(label).text(fileName || 'Pilih gambar...');
            }

            $('#foto').on('change', function() {
                updateFileLabel(this, '#foto + .custom-file-label');
                let preview = $('#image-preview-add');
                preview.empty();
                Array.from(this.files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        preview.append(`
                            <div class="img-preview mr-2 mb-2 position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px;">
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });

            $('#foto_update').on('change', function() {
                updateFileLabel(this, '#foto_update + .custom-file-label');
                let preview = $('#image-preview-update');
                Array.from(this.files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        preview.append(`
                            <div class="img-preview mr-2 mb-2 position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px;">
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });

            $('.rupiah').on('input', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                if ($(this).attr('id') === 'price') {
                    $('#raw_price_update').val(rawValue);
                } else {
                    $('#raw_price').val(rawValue);
                }
            });

            $('#products-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/manage-master/products/all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'name', name: 'name' },
                    { data: 'hierarchy', name: 'hierarchy' },
                    { data: 'merek_name', name: 'merek_name' },
                    { data: 'variant_count', name: 'variant_count' },
                    { data: 'status', name: 'status' },
                    { data: 'photos_preview', name: 'photos_preview' },
                    { data: 'action', name: 'action' }
                ]
            });

            // Cascading selects removed as hierarchy is now flat/standalone

            // Variant Grid Management
            let variantIndex = 1;
            $('#btn-add-variant').on('click', function() {
                let html = `
                    <tr>
                        <td><input type="text" name="variants[${variantIndex}][netto]" class="form-control form-control-sm" placeholder="100ml" required></td>
                        <td><input type="text" name="variants[${variantIndex}][name]" class="form-control form-control-sm" placeholder="Original" required></td>
                        <td><input type="text" name="variants[${variantIndex}][sku]" class="form-control form-control-sm" placeholder="SKU${variantIndex+1}" required></td>
                        <td>
                            <input type="text" class="form-control form-control-sm rupiah-variant" placeholder="Rp 0" required>
                            <input type="hidden" name="variants[${variantIndex}][price]" class="raw-price-variant">
                        </td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-remove-variant"><i class="fas fa-times"></i></button></td>
                    </tr>
                `;
                $('#table-variants tbody').append(html);
                variantIndex++;
            });

            $(document).on('click', '.btn-remove-variant', function() {
                $(this).closest('tr').remove();
            });

            $(document).on('input', '.rupiah-variant', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                $(this).siblings('.raw-price-variant').val(rawValue);
            });

            // AJAX Form Submission for Create
            $('#btn-save-product').on('click', function() {
                let form = $('#addModal form')[0];
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return;
                }
                
                let formData = new FormData(form);
                $.ajax({
                    url: "{{ url('admin/manage-master/products') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    success: function(res) {
                        $.LoadingOverlay("hide");
                        swal(res.message, { icon: "success" }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(err) {
                        $.LoadingOverlay("hide");
                        swal(err.responseJSON.message || "Something went wrong", { icon: "error" });
                    }
                });
            });

            // Edit button handler
            let variantIndexEdit = 0;
            $('#products-table').on('click', '.edit[data-id]', function(e) {
                e.preventDefault();
                $.ajax({
                    data: {
                        'id': $(this).data('id'),
                        '_token': "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    url: "{{ url('admin/manage-master/products/get') }}",
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    complete: function() {
                        $.LoadingOverlay("hide");
                    },
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#name').val(data.name);
                        $('#merek_id').val(data.merek_id);
                        $('#min_stock_alert').val(data.min_stock_alert);
                        $('#status').val(data.status);
                        
                        // Populate Hierarchy
                        $('#edit-category').val(data.category_id);
                        $('#edit-product-type').val(data.product_type_id);

                        // Populate Variants
                        $('#table-variants-edit tbody').empty();
                        variantIndexEdit = 0;
                        data.variants.forEach((v, index) => {
                            let netVal = v.product_netto ? v.product_netto.netto_value : '';
                            let html = `
                                <tr>
                                    <td><input type="text" name="variants[${index}][netto]" value="${netVal}" class="form-control form-control-sm" required></td>
                                    <td><input type="text" name="variants[${index}][name]" value="${v.variant_name}" class="form-control form-control-sm" required></td>
                                    <td><input type="text" name="variants[${index}][sku]" value="${v.sku_code}" class="form-control form-control-sm" required></td>
                                    <td>
                                        <input type="text" value="${formatRupiah(v.price)}" class="form-control form-control-sm rupiah-variant" required>
                                        <input type="hidden" name="variants[${index}][price]" value="${v.price}" class="raw-price-variant">
                                    </td>
                                    <td>${index > 0 ? '<button type="button" class="btn btn-sm btn-danger btn-remove-variant"><i class="fas fa-times"></i></button>' : ''}</td>
                                </tr>
                            `;
                            $('#table-variants-edit tbody').append(html);
                            variantIndexEdit = index + 1;
                        });

                        $('#deleted_photos').val('');
                        let preview = $('#image-preview-update');
                        preview.empty();
                        data.photos.forEach(photo => {
                            preview.append(`
                                <div class="img-preview mr-2 mb-2 position-relative" data-id="${photo.id}">
                                    <img src="{{ asset('') }}${photo.foto}" class="img-thumbnail" style="width: 100px; height: 100px;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 0; right: 0;" onclick="removePhoto(${photo.id})">×</button>
                                </div>
                            `);
                        });
                        $('#foto_update + .custom-file-label').text('Pilih gambar...');
                        $('#updateModal').modal('show');
                    }
                });
            });

            // Add Variant in Edit Modal
            $('#btn-add-variant-edit').on('click', function() {
                let html = `
                    <tr>
                        <td><input type="text" name="variants[${variantIndexEdit}][netto]" class="form-control form-control-sm" required></td>
                        <td><input type="text" name="variants[${variantIndexEdit}][name]" class="form-control form-control-sm" required></td>
                        <td><input type="text" name="variants[${variantIndexEdit}][sku]" class="form-control form-control-sm" required></td>
                        <td>
                            <input type="text" class="form-control form-control-sm rupiah-variant" placeholder="Rp 0" required>
                            <input type="hidden" name="variants[${variantIndexEdit}][price]" class="raw-price-variant">
                        </td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-remove-variant"><i class="fas fa-times"></i></button></td>
                    </tr>
                `;
                $('#table-variants-edit tbody').append(html);
                variantIndexEdit++;
            });

            // AJAX Form Submission for Update
            $('#btn-update-product').on('click', function() {
                let form = $('#updateModal form')[0];
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return;
                }
                
                let formData = new FormData(form);
                $.ajax({
                    url: "{{ url('admin/manage-master/products/update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    success: function(res) {
                        $.LoadingOverlay("hide");
                        swal(res.message, { icon: "success" }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(err) {
                        $.LoadingOverlay("hide");
                        swal(err.responseJSON.message || "Something went wrong", { icon: "error" });
                    }
                });
            });

            // Cascading selects for edit removed as hierarchy is flat
            $('#products-table').on('click', '.hapus[data-id]', function(e) {
                e.preventDefault();
                swal({
                    title: "Hapus Produk?",
                    text: "Data Produk ini akan dihapus secara permanen!",
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
                            url: "{{ url('admin/manage-master/products') }}",
                            beforeSend: function() {
                                $.LoadingOverlay("show", {
                                    image: "",
                                    fontawesome: "fa fa-cog fa-spin"
                                });
                            },
                            complete: function() {
                                $.LoadingOverlay("hide");
                            },
                            success: function(data) {
                                swal(data.message).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(err) {
                                alert('Error: ' + err.responseText);
                                console.log(err);
                            }
                        });
                    }
                });
            });
        });

        function removePhoto(photoId) {
            let deletedPhotos = $('#deleted_photos').val();
            deletedPhotos = deletedPhotos ? deletedPhotos + ',' + photoId : photoId;
            $('#deleted_photos').val(deletedPhotos);
            $(`#image-preview-update .img-preview[data-id="${photoId}"]`).remove();
        }
    </script>
@endsection