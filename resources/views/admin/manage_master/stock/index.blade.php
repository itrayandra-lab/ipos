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

<!-- Modal Detail (Audit Card) -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Laporan Audit Stok: <span id="det-title-product"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th width="120">Produk</th><td>: <span id="det-info-name"></span></td></tr>
                            <tr><th>Lokasi Gudang</th><td>: <span id="det-info-warehouse" class="badge badge-info"></span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-warning btn-sm" id="btn-edit-netto">
                            <i class="fas fa-edit"></i> Edit Netto & Harga Jual
                        </button>
                    </div>
                </div>

                <ul class="nav nav-tabs" id="auditTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="batch-tab" data-toggle="tab" href="#tab-batch" role="tab">Rincian Batch</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="incoming-tab" data-toggle="tab" href="#tab-incoming" role="tab">Riwayat Masuk (Supplier)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="outgoing-tab" data-toggle="tab" href="#tab-outgoing" role="tab">Riwayat Keluar (Penjualan)</a>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="auditTabContent">
                    <!-- Tab Batches -->
                    <div class="tab-pane fade show active" id="tab-batch" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="table-det-batches">
                                <thead>
                                    <tr class="bg-light">
                                        <th>No Batch</th>
                                        <th>Exp Date</th>
                                        <th class="text-right">Qty Awal</th>
                                        <th class="text-right text-primary">Sisa Stok</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Tab Incoming -->
                    <div class="tab-pane fade" id="tab-incoming" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="table-det-incoming">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Tipe</th>
                                        <th>No. Ref / SJ</th>
                                        <th>Asal / Supplier</th>
                                        <th>Tgl Terima</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Tab Outgoing -->
                    <div class="tab-pane fade" id="tab-outgoing" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="table-det-outgoing">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Tipe</th>
                                        <th>No. Referensi</th>
                                        <th>Tujuan / Customer</th>
                                        <th class="text-right">Qty Keluar</th>
                                        <th>Tgl Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup Laporan</button>
            </div>
        </div>
    </div>
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
<!-- Modal Edit Netto & Harga Jual -->
<div class="modal fade" id="modal-edit-netto" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Netto & Harga Jual</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-edit-netto">
                @csrf
                <input type="hidden" name="variant_id" id="netto-variant-id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Varian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="variant_name" id="netto-variant-name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Netto Value <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="netto_value" id="netto-value" placeholder="Contoh: 100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Satuan</label>
                                <input type="text" class="form-control" name="satuan" id="netto-satuan" placeholder="Contoh: ml, gr, pcs">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="number" class="form-control" name="price" id="netto-price" required min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>SKU Code</label>
                        <input type="text" class="form-control" id="netto-sku" disabled>
                        <small class="text-muted">SKU tidak dapat diubah dari sini</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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

        // Detail Audit Button
        $(document).on('click', '.btn-detail', function() {
            let data = $(this).data();
            // Simpan variant_id aktif untuk tombol Edit Netto
            activeVariantId = data.variant_id || null;

            $.post('{{ url("admin/manage-master/stock/detail") }}', {
                _token: '{{ csrf_token() }}',
                product_id: data.product_id,
                variant_id: data.variant_id,
                warehouse_id: data.warehouse_id
            }, function(res) {
                if (res.success) {
                    $('#det-info-name').text(res.product.name);
                    $('#det-info-warehouse').text(res.product.warehouse);

                    // Tampilkan/sembunyikan tombol edit netto
                    if (activeVariantId) {
                        $('#btn-edit-netto').show();
                    } else {
                        $('#btn-edit-netto').hide();
                    }

                    // Render Batches
                    $('#table-det-batches tbody').empty();
                    res.batches.forEach(b => {
                        $('#table-det-batches tbody').append(`
                            <tr>
                                <td><b>${b.batch_no}</b></td>
                                <td>${b.expiry_date ? new Date(b.expiry_date).toLocaleDateString('id-ID') : '-'}</td>
                                <td class="text-right">${b.qty}</td>
                                <td class="text-right text-primary font-weight-bold">${b.current_qty}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info btn-edit-batch" data-id="${b.id}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger btn-delete-batch" data-id="${b.id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });

                    // Render Incoming
                    $('#table-det-incoming tbody').empty();
                    if(res.incoming.length) {
                        res.incoming.forEach(i => {
                            let badge = i.type === 'Supplier' ? 'badge-primary' : 'badge-dark';
                            $('#table-det-incoming tbody').append(`
                                <tr>
                                    <td><span class="badge ${badge}">${i.type}</span></td>
                                    <td class="font-weight-bold">${i.ref_no}</td>
                                    <td>${i.source}</td>
                                    <td>${new Date(i.date).toLocaleDateString('id-ID')}</td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#table-det-incoming tbody').append('<tr><td colspan="4" class="text-center text-muted">Tidak ada data incoming</td></tr>');
                    }

                    // Render Outgoing
                    $('#table-det-outgoing tbody').empty();
                    if(res.outgoing.length) {
                        res.outgoing.forEach(o => {
                            let badge = o.type === 'Penjualan' ? 'badge-success' : 'badge-info';
                            $('#table-det-outgoing tbody').append(`
                                <tr>
                                    <td><span class="badge ${badge}">${o.type}</span></td>
                                    <td class="font-weight-bold">${o.ref_no}</td>
                                    <td>${o.destination}</td>
                                    <td class="text-right">${o.qty}</td>
                                    <td>${new Date(o.date).toLocaleString('id-ID')}</td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#table-det-outgoing tbody').append('<tr><td colspan="5" class="text-center text-muted">Tidak ada data outgoing</td></tr>');
                    }

                    $('#modal-detail').modal('show');
                }
            });
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

        // Edit Batch
        $(document).on('click', '.btn-edit-batch', function() {
            let id = $(this).data('id');
            $.post('{{ url("admin/manage-master/stock/get") }}', { _token: '{{ csrf_token() }}', id: id }, function(res) {
                if(res.success) {
                    $('#edit-id').val(res.data.id);
                    $('#edit-batch-no').val(res.data.batch_no);
                    $('#edit-expiry').val(res.data.expiry_date ? res.data.expiry_date.split('T')[0] : '');
                    $('#edit-qty').val(res.data.qty);
                    $('#modal-edit').modal('show');
                }
            });
        });

        $('#form-edit').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ url("admin/manage-master/stock/update") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#modal-edit').modal('hide');
                    iziToast.success({ title: 'Berhasil', message: res.message });
                    table.ajax.reload();
                }
            });
        });

        // Delete Batch
        $(document).on('click', '.btn-delete-batch', function() {
            let id = $(this).data('id');
            swal({ title: 'Hapus Batch?', text: 'Data tidak bisa dikembalikan', icon: 'warning', buttons: true, dangerMode: true })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: '{{ url("admin/manage-master/stock") }}',
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}', id: id },
                        success: function(res) {
                            table.ajax.reload();
                            $('#modal-detail').modal('hide');
                            iziToast.success({ title: 'Berhasil', message: res.message });
                        }
                    });
                }
            });
        });

        // Edit Netto & Harga Jual
        $('#btn-edit-netto').on('click', function() {
            if (!activeVariantId) {
                iziToast.warning({ message: 'Tidak ada varian yang dipilih', position: 'topRight' });
                return;
            }
            $.post('{{ url("admin/manage-master/stock/get-netto") }}', {
                _token: '{{ csrf_token() }}',
                variant_id: activeVariantId
            }, function(res) {
                if (res.success) {
                    let d = res.data;
                    $('#netto-variant-id').val(d.variant_id);
                    $('#netto-variant-name').val(d.variant_name);
                    $('#netto-value').val(d.netto_value);
                    $('#netto-satuan').val(d.satuan);
                    $('#netto-price').val(d.price);
                    $('#netto-sku').val(d.sku_code);
                    $('#modal-edit-netto').modal('show');
                }
            });
        });

        $('#form-edit-netto').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ url("admin/manage-master/stock/update-netto") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#modal-edit-netto').modal('hide');
                    table.ajax.reload();
                    iziToast.success({ title: 'Berhasil', message: res.message });
                },
                error: function(err) {
                    iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Gagal menyimpan' });
                }
            });
        });
    });
</script>
@endpush
