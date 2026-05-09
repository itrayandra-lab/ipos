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
                            <div class="card-header-action">
                                <div class="btn-group" id="warehouse-tabs" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm active" data-warehouse="">Semua Gudang</button>
                                    @foreach($warehouses as $wh)
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-warehouse="{{ $wh->id }}">{{ $wh->name }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-stock">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>Gudang</th>
                                            <th>Netto</th>
                                            <th class="text-center">Jml Batch</th>
                                            <th class="text-right">Total Sisa Stok</th>
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


<!-- Modal Add (Restored) -->
<div class="modal fade" id="modal-add" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Batch Stok</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-add">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Gudang <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="warehouse_id" required>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $wh->type == 'main' ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Produk <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="product_id" id="product-add" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->merek ? $product->merek->name . ' ' : '' }}{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Varian (Netto)</label>
                        <select class="form-control select2" name="product_variant_id" id="variant-add">
                            <option value="">Pilih Varian</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Batch <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="batch_no" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tgl Kadaluarsa</label>
                                <input type="date" class="form-control" name="expiry_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Qty Masuk <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="qty" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit (Restored) -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Batch</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-edit">
                @csrf
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>No Batch</label>
                        <input type="text" class="form-control" name="batch_no" id="edit-batch-no" required>
                    </div>
                    <div class="form-group">
                        <label>Tgl Kadaluarsa</label>
                        <input type="date" class="form-control" name="expiry_date" id="edit-expiry">
                    </div>
                    <div class="form-group">
                        <label>Qty Awal</label>
                        <input type="number" class="form-control" name="qty" id="edit-qty" required>
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
    let activeWarehouseId = '';
    let activeVariantId = null;

    $(document).ready(function() {
        table = $('#table-stock').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ url("admin/manage-master/stock/all") }}',
                data: function(d) { d.warehouse_id = activeWarehouseId; }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'product_name', name: 'product_name' },
                { data: 'warehouse_name', name: 'warehouse_name' },
                { data: 'netto', name: 'netto', orderable: false, searchable: false },
                { data: 'batch_count', name: 'batch_count', className: 'text-center', searchable: false },
                { data: 'total_current_stock', name: 'total_current_stock', className: 'text-right font-weight-bold', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#warehouse-tabs button').on('click', function() {
            $('#warehouse-tabs button').removeClass('active');
            $(this).addClass('active');
            activeWarehouseId = $(this).data('warehouse');
            table.ajax.reload();
        });

        $('.select2').each(function() {
            $(this).select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $(this).closest('.modal') });
        });

        $('#product-add').on('change', function() {
            let id = $(this).val();
            $('#variant-add').html('<option value="">Pilih Varian</option>');
            if(id) {
                $.get('{{ url("admin/manage-master/stock/variants") }}/' + id, function(res) {
                    res.data.forEach(v => {
                        $('#variant-add').append(`<option value="${v.id}">${v.netto_value} ${v.satuan}</option>`);
                    });
                });
            }
        });


        // Add
        $('#form-add').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ url("admin/manage-master/stock") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#modal-add').modal('hide');
                    table.ajax.reload();
                    iziToast.success({ title: 'Berhasil', message: res.message });
                }
            });
        });




    });
</script>
@endpush
