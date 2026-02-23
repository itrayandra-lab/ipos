@extends('master')

@section('title', 'Manajemen Stok')

@push('styles')
<style>
    /* Limit Select2 dropdown height to approx 5 items */
    .select2-results__options {
        max-height: 200px !important;
        overflow-y: auto !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Manajemen Stok</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Stok</div>
            </div>
            <div class="section-header-button">
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add">
                    <i class="fas fa-plus"></i> Tambah Batch Stok
                </button>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Stok Produk</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-stock">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>Netto</th>
                                            <th>No Batch</th>
                                            <th>Stok Awal</th>
                                            <th>Sisa Stok</th>
                                            <th>Tgl Kadaluarsa</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Add -->
<div class="modal fade" id="modal-add" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Batch Stok</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-add">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Produk <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="product_id" id="product-add" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Varian</label>
                        <select class="form-control select2" name="product_variant_id" id="variant-add">
                            <option value="">Pilih Varian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Batch <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="batch_no" placeholder="Contoh: BATCH001" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Kadaluarsa</label>
                        <input type="date" class="form-control" name="expiry_date">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Stok <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="qty" placeholder="0" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Batch Stok</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-edit">
                @csrf
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Produk</label>
                        <input type="text" class="form-control" id="edit-product" readonly>
                    </div>
                    <div class="form-group">
                        <label>Varian</label>
                        <input type="text" class="form-control" id="edit-variant" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nomor Batch <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="batch_no" id="edit-batch-no" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Kadaluarsa</label>
                        <input type="date" class="form-control" name="expiry_date" id="edit-expiry">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Stok <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="qty" id="edit-qty" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table;

    $(document).ready(function() {
        // Initialize DataTable
        table = $('#table-stock').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/manage-master/stock/all") }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'product_name', name: 'product.name' },
                { data: 'netto', name: 'netto', orderable: false },
                { data: 'batch_no', name: 'batch_no' },
                { data: 'qty', name: 'qty' },
                { data: 'current_stock', name: 'current_stock', orderable: false },

                { 
                    data: 'expiry_date', 
                    name: 'expiry_date',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString('id-ID') : '-';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Initialize Select2
        $('#product-add, #variant-add').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#modal-add')
        });



        // Load variants when product selected
        $('#product-add').on('change', function() {
            let productId = $(this).val();
            $('#variant-add').html('<option value="">Pilih Varian</option>');
            
            if (productId) {
                $.get('{{ url("admin/manage-master/stock/variants") }}/' + productId, function(res) {
                    if (res.success && res.data.length > 0) {
                        res.data.forEach(function(variant) {
                            let satuan = variant.satuan ? variant.satuan : '';
                            let displayText = `${variant.netto_value} ${satuan}`;
                            $('#variant-add').append(`<option value="${variant.id}">${displayText}</option>`);
                        });
                    }
                });
            }
        });

        // Add form submit
        $('#form-add').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ url("admin/manage-master/stock") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#modal-add').modal('hide');
                        $('#form-add')[0].reset();
                        $('.select2').val('').trigger('change');
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                    }
                },
                error: function(err) {
                    iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Terjadi kesalahan', position: 'topRight' });
                }
            });
        });

        // Edit button
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $.post('{{ url("admin/manage-master/stock/get") }}', { _token: '{{ csrf_token() }}', id: id }, function(res) {
                if (res.success) {
                    let data = res.data;
                    $('#edit-id').val(data.id);
                    $('#edit-product').val(data.product ? data.product.name : '-');
                    $('#edit-variant').val(data.variant ? (data.variant.netto ? data.variant.netto.netto_value : '') + ' - ' + data.variant.sku_code : '-');
                    $('#edit-batch-no').val(data.batch_no);
                    $('#edit-expiry').val(data.expiry_date);
                    $('#edit-qty').val(data.qty);
                    $('#modal-edit').modal('show');
                }
            });
        });

        // Edit form submit
        $('#form-edit').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ url("admin/manage-master/stock/update") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#modal-edit').modal('hide');
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                    }
                },
                error: function(err) {
                    iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Terjadi kesalahan', position: 'topRight' });
                }
            });
        });

        // Delete button
        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            swal({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin menghapus batch ini?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: '{{ url("admin/manage-master/stock") }}',
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}', id: id },
                        success: function(res) {
                            if (res.success) {
                                table.ajax.reload();
                                iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                            }
                        },
                        error: function(err) {
                            iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Terjadi kesalahan', position: 'topRight' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
