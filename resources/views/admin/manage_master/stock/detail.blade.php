@extends('master')

@section('title', 'Detail Stok')

@push('styles')
<style>
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
            <div class="section-header-back">
                <a href="{{ url('admin/manage-master/stock') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Laporan Audit Stok</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/stock') }}">Stok</a></div>
                <div class="breadcrumb-item active">Audit</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="text-white" style="margin: 0;">Detail Stok: <span id="det-title-product">Memuat...</span></h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4 mt-2">
                        <div class="col-md-8">
                            <table class="table table-sm table-borderless" style="font-size: 14px;">
                                <tr><th width="120">Produk</th><td>: <span id="det-info-name">...</span></td></tr>
                                <tr><th>Lokasi Gudang</th><td>: <span id="det-info-warehouse" class="badge badge-info">...</span></td></tr>
                                <tr><th>Netto</th><td>: <span id="det-info-netto" class="font-weight-bold text-primary">...</span></td></tr>
                            </table>
                        </div>
                        @if(!auth()->user()->isFinance())
                        <div class="col-md-4 text-right">
                            <button class="btn btn-warning btn-sm mt-1" id="btn-edit-netto" style="display:none;">
                                <i class="fas fa-exchange-alt"></i> Ganti Varian
                            </button>
                            <button class="btn btn-success btn-sm mt-1" id="btn-add-netto" style="display:none;">
                                <i class="fas fa-link"></i> Hubungkan Varian
                            </button>
                        </div>
                        @endif
                    </div>

                    <ul class="nav nav-pills" id="auditTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="batch-tab" data-toggle="tab" href="#tab-batch" role="tab">
                                <i class="fas fa-boxes"></i> Rincian Batch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="incoming-tab" data-toggle="tab" href="#tab-incoming" role="tab">
                                <i class="fas fa-arrow-down text-success"></i> Riwayat Masuk (Supplier)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="outgoing-tab" data-toggle="tab" href="#tab-outgoing" role="tab">
                                <i class="fas fa-arrow-up text-danger"></i> Riwayat Keluar (Penjualan/Return)
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content pt-4" id="auditTabContent">
                        <!-- Tab Batches -->
                        <div class="tab-pane fade show active" id="tab-batch" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-det-batches">
                                    <thead>
                                        <tr>
                                            <th>No Batch</th>
                                            <th>Exp Date</th>
                                            <th class="text-right">Qty Awal</th>
                                            <th class="text-right text-primary">Sisa Stok</th>
                                            @if(!auth()->user()->isFinance())
                                            <th class="text-center">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Tab Incoming -->
                        <div class="tab-pane fade" id="tab-incoming" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover" id="table-det-incoming">
                                    <thead>
                                        <tr>
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
                                <table class="table table-hover" id="table-det-outgoing">
                                    <thead>
                                        <tr>
                                            <th>No Batch</th>
                                            <th>Tipe</th>
                                            <th>No. Referensi</th>
                                            <th>Tujuan (Customer/Supplier)</th>
                                            <th class="text-right">Qty Keluar</th>
                                            <th>Tgl Transaksi</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
<!-- Modal Tambah Netto -->
<div class="modal fade" id="modal-add-netto" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hubungkan ke Varian</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-add-netto">
                @csrf
                <input type="hidden" name="product_id" id="add-netto-product-id">
                <input type="hidden" name="warehouse_id" id="add-netto-warehouse-id">
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        Pilih varian yang sesuai. Semua batch produk ini yang belum memiliki varian akan dihubungkan ke varian yang dipilih.
                    </div>
                    <div class="form-group">
                        <label>Pilih Varian <span class="text-danger">*</span></label>
                        <select class="form-control" name="variant_id" id="add-netto-variant-select" required>
                            <option value="">-- Pilih Varian --</option>
                        </select>
                        <small class="text-muted">Data varian diambil dari produk ini (Netto + Satuan)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Hubungkan</button>
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
                <h5 class="modal-title">Ganti Varian</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-edit-netto">
                @csrf
                <input type="hidden" name="variant_id" id="netto-variant-id">
                <input type="hidden" name="product_id" id="netto-product-id">
                <input type="hidden" name="warehouse_id" id="netto-warehouse-id">
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small">
                        Pilih varian yang sesuai. Semua batch produk ini di gudang ini akan dipindahkan ke varian yang dipilih.
                    </div>
                    <div class="form-group">
                        <label>Pilih Varian <span class="text-danger">*</span></label>
                        <select class="form-control" name="new_variant_id" id="netto-variant-select" required>
                            <option value="">-- Pilih Varian --</option>
                        </select>
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
    let activeVariantId = '{{ $variantId }}' || null;
    let productId = '{{ $productId }}';
    let warehouseId = '{{ $warehouseId }}';

    function loadAuditData() {
        $.post('{{ url("admin/manage-master/stock/detail") }}', {
            _token: '{{ csrf_token() }}',
            product_id: productId,
            variant_id: activeVariantId,
            warehouse_id: warehouseId
        }, function(res) {
            if (res.success) {
                $('#det-title-product').text(res.product.name);
                $('#det-info-name').text(res.product.name);
                $('#det-info-warehouse').text(res.product.warehouse);
                $('#det-info-netto').text(res.product.netto || '-');

                if (activeVariantId) {
                    $('#btn-edit-netto').show();
                    $('#btn-add-netto').hide();
                    $('#netto-product-id').val(productId);
                    $('#netto-warehouse-id').val(warehouseId);
                } else {
                    $('#btn-edit-netto').hide();
                    $('#btn-add-netto').show();
                    $('#add-netto-product-id').val(productId);
                    $('#add-netto-warehouse-id').val(warehouseId);
                }

                // Render Batches
                $('#table-det-batches tbody').empty();
                if(res.batches.length) {
                    res.batches.forEach(b => {
                        let actionBtn = '';
                        @if(!auth()->user()->isFinance())
                        actionBtn = `
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info btn-edit-batch" data-id="${b.id}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger btn-delete-batch" data-id="${b.id}"><i class="fas fa-trash"></i></button>
                                </td>
                        `;
                        @endif

                        $('#table-det-batches tbody').append(`
                            <tr>
                                <td><b>${b.batch_no}</b></td>
                                <td>${b.expiry_date ? new Date(b.expiry_date).toLocaleDateString('id-ID') : '-'}</td>
                                <td class="text-right">${b.qty}</td>
                                <td class="text-right text-primary font-weight-bold">${b.current_qty}</td>
                                ${actionBtn}
                            </tr>
                        `);
                    });
                } else {
                    $('#table-det-batches tbody').append('<tr><td colspan="5" class="text-center text-muted">Tidak ada data batch</td></tr>');
                }

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
                        let badge = 'badge-info';
                        if (o.type === 'Penjualan') badge = 'badge-success';
                        if (o.type === 'Return Supplier') badge = 'badge-danger';
                        
                        let printBtn = o.print_url ? `<a href="${o.print_url}" target="_blank" class="btn btn-sm btn-light" title="Cetak Surat Jalan"><i class="fas fa-print"></i></a>` : '-';
                        
                        $('#table-det-outgoing tbody').append(`
                            <tr>
                                <td class="font-weight-bold text-dark">${o.batch_no || '-'}</td>
                                <td><span class="badge ${badge}">${o.type}</span></td>
                                <td class="font-weight-bold">${o.ref_no}</td>
                                <td>${o.destination}</td>
                                <td class="text-right">${o.qty}</td>
                                <td>${o.date}</td>
                                <td class="text-center">${printBtn}</td>
                            </tr>
                        `);
                    });
                } else {
                    $('#table-det-outgoing tbody').append('<tr><td colspan="6" class="text-center text-muted">Tidak ada data outgoing</td></tr>');
                }
            }
        }).fail(function(xhr) {
            let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat data.';
            $('#det-title-product').text('Error');
            $('#table-det-batches tbody').html(`<tr><td colspan="5" class="text-center text-danger">${msg}</td></tr>`);
            iziToast.error({ title: 'Gagal', message: msg });
        });
    }

    $(document).ready(function() {
        // Init Select2
        $('.select2').each(function() {
            $(this).select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $(this).closest('.modal') });
        });

        // Load data on start
        loadAuditData();

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
                    loadAuditData();
                }
            });
        });

        // Delete Batch
        $(document).on('click', '.btn-delete-batch', function() {
            let id = $(this).data('id');
            let $row = $(this).closest('tr');
            Swal.fire({ 
                title: 'Hapus Batch?', 
                text: 'Data tidak bisa dikembalikan', 
                icon: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("admin/manage-master/stock/delete") }}',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', id: id },
                        success: function(res) {
                            // Hapus baris langsung dari DOM tanpa tunggu reload
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                // Jika tabel kosong, tampilkan pesan
                                if ($('#table-det-batches tbody tr').length === 0) {
                                    $('#table-det-batches tbody').append('<tr><td colspan="5" class="text-center text-muted">Tidak ada data batch</td></tr>');
                                }
                            });
                            iziToast.success({ title: 'Berhasil', message: res.message });
                            // Reload data di background untuk sinkronisasi sisa stok
                            setTimeout(loadAuditData, 400);
                        },
                        error: function(err) {
                            let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Gagal menghapus batch.';
                            iziToast.error({ title: 'Gagal', message: msg, position: 'topRight' });
                        }
                    });
                }
            });
        });

        // Tambah Netto (untuk batch yang belum punya variant)
        $('#btn-add-netto').on('click', function() {
            let select = $('#add-netto-variant-select');
            select.html('<option value="">-- Memuat varian... --</option>');

            $.get('{{ url("admin/manage-master/stock/variants") }}/' + productId, function(res) {
                select.html('<option value="">-- Pilih Varian --</option>');
                if (res.data && res.data.length > 0) {
                    res.data.forEach(v => {
                        let label = v.netto_value + (v.satuan ? ' ' + v.satuan : '');
                        select.append(`<option value="${v.id}">${label}</option>`);
                    });
                } else {
                    select.html('<option value="">Tidak ada varian tersedia</option>');
                }
                $('#modal-add-netto').modal('show');
            });
        });

        $('#form-add-netto').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ url("admin/manage-master/stock/add-netto") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#modal-add-netto').modal('hide');
                    iziToast.success({ title: 'Berhasil', message: res.message });
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(err) {
                    iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Gagal menyimpan' });
                }
            });
        });

        // Edit Netto & Harga Jual
        $('#btn-edit-netto').on('click', function() {
            if (!activeVariantId) {
                iziToast.warning({ message: 'Tidak ada varian yang dipilih', position: 'topRight' });
                return;
            }
            let select = $('#netto-variant-select');
            select.html('<option value="">-- Memuat varian... --</option>');

            $.get('{{ url("admin/manage-master/stock/variants") }}/' + productId, function(res) {
                select.html('<option value="">-- Pilih Varian --</option>');
                if (res.data && res.data.length > 0) {
                    res.data.forEach(v => {
                        let label = v.netto_value + (v.satuan ? ' ' + v.satuan : '');
                        let selected = (v.id == activeVariantId) ? 'selected' : '';
                        select.append(`<option value="${v.id}" ${selected}>${label}</option>`);
                    });
                }
                $('#netto-variant-id').val(activeVariantId);
                $('#modal-edit-netto').modal('show');
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
                    iziToast.success({ title: 'Berhasil', message: res.message });
                    setTimeout(() => window.location.href = res.redirect || window.location.href, 1000);
                },
                error: function(err) {
                    iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Gagal menyimpan' });
                }
            });
        });
    });
</script>
@endpush
