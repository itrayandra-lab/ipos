@extends('master')
@section('title', 'Stock Opname - Baru')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Stock Opname Baru</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/stock') }}">Stok</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/stock-opname') }}">Stock Opname</a></div>
                <div class="breadcrumb-item active">Baru</div>
            </div>
            <div class="section-header-button">
                <a href="{{ url('admin/manage-master/stock-opname') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Pilih Gudang</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gudang <span class="text-danger">*</span></label>
                                        <select class="form-control" id="warehouse_id" name="warehouse_id">
                                            <option value="">-- Pilih Gudang --</option>
                                            @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->type }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Catatan</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Catatan opname (opsional)"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button class="btn btn-primary" id="btn-load-products" disabled>
                                        <i class="fas fa-sync mr-1"></i> Muat Produk
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="card-products" style="display:none;">
                        <div class="card-header">
                            <h4>Input Stok Fisik</h4>
                            <div class="card-header-action">
                                <button class="btn btn-success" id="btn-save-opname">
                                    <i class="fas fa-save mr-1"></i> Simpan Draft Opname
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>
                                Masukkan jumlah stok fisik. Data akan disimpan sebagai <strong>Draft</strong> dan harus disetujui oleh Supervisor/Manager sebelum stok disesuaikan.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-products" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Produk</th>
                                            <th>Variant</th>
                                            <th>Netto</th>
                                            <th class="text-right">Stok Sistem</th>
                                            <th class="text-right">Stok Fisik</th>
                                            <th class="text-right">Selisih</th>
                                            <th>Indikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="products-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ringkasan Opname</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted">Total Item dengan Selisih</small>
                        <p class="font-weight-bold" id="summary-total-items">0</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Total Kelebihan (+)</small>
                        <p class="font-weight-bold text-danger" id="summary-total-surplus">0</p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Total Kekurangan (-)</small>
                        <p class="font-weight-bold text-success" id="summary-total-deficit">0</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Sistem</th>
                                <th class="text-right">Fisik</th>
                                <th class="text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody id="summary-tbody"></tbody>
                    </table>
                </div>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Data akan disimpan sebagai <strong>Draft</strong>. Perubahan stok hanya akan terjadi setelah disetujui oleh Supervisor/Manager.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-confirm-save">
                    <i class="fas fa-save mr-1"></i> Simpan Draft
                </button>
            </div>
        </div>
    </div>
</div>

<div id="loading-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:9999; text-align:center;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);">
        <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
        <div class="mt-2 text-muted">Memproses...</div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #table-products td, #table-products th { vertical-align: middle; }
    .physical-input { width: 100px; text-align: right; }
    .diff-positive { color: #dc3545; font-weight: 700; }
    .diff-negative { color: #28a745; font-weight: 700; }
    .diff-zero { color: #6c757d; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let productsData = [];
    let dt = null;

    $('#warehouse_id').on('change', function() {
        $('#btn-load-products').prop('disabled', !$(this).val());
    });

    $('#btn-load-products').on('click', function() {
        const warehouseId = $('#warehouse_id').val();
        if (!warehouseId) return;

        $('#btn-load-products').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');

        $.ajax({
            url: '{{ url("admin/manage-master/stock-opname/get-products") }}',
            method: 'POST',
            data: { warehouse_id: warehouseId, _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    productsData = res.data;
                    renderTable(res.data);
                    $('#card-products').show();
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                alert('Gagal memuat produk: ' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function() {
                $('#btn-load-products').prop('disabled', false).html('<i class="fas fa-sync mr-1"></i> Muat Produk');
            }
        });
    });

    function renderTable(data) {
        if (dt) { dt.destroy(); $('#products-tbody').empty(); }

        let html = '';
        $.each(data, function(i, item) {
            const systemQty = parseInt(item.system_qty) || 0;
            const variantName = item.variant_name || (item.product_variant_id ? 'Varian' : '-');

            html += '<tr>';
            html += '<td>' + (i + 1) + '</td>';
            html += '<td><strong>' + item.display_name + '</strong></td>';
            html += '<td>' + variantName + '</td>';
            html += '<td>' + (item.netto_label || '-') + '</td>';
            html += '<td class="text-right system-qty">' + systemQty + '</td>';
            html += '<td class="text-right"><input type="number" class="form-control physical-input" data-product-id="' + item.product_id + '" data-variant-id="' + (item.product_variant_id || '') + '" data-system-qty="' + systemQty + '" value="' + systemQty + '" min="0" style="display:inline-block;"></td>';
            html += '<td class="text-right diff-cell diff-zero">0</td>';
            html += '<td class="indication-cell">-</td>';
            html += '</tr>';
        });

        $('#products-tbody').html(html);

        dt = $('#table-products').DataTable({
            pageLength: 50,
            order: [[1, 'asc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            columnDefs: [{ targets: 5, orderable: false }]
        });

        $('.physical-input').on('input', function() {
            const $input = $(this);
            const systemQty = parseInt($input.data('system-qty'));
            const physicalQty = parseInt($input.val()) || 0;
            const diff = physicalQty - systemQty;
            const $tr = $input.closest('tr');
            const $diffCell = $tr.find('.diff-cell');
            const $indicationCell = $tr.find('.indication-cell');

            $diffCell.text(diff >= 0 ? '+' + diff : diff);
            $diffCell.removeClass('diff-positive diff-negative diff-zero');
            if (diff > 0) {
                $diffCell.addClass('diff-positive');
                $indicationCell.html('<span class="text-danger">Kelebihan</span>');
            } else if (diff < 0) {
                $diffCell.addClass('diff-negative');
                $indicationCell.html('<span class="text-success">Kekurangan</span>');
            } else {
                $diffCell.addClass('diff-zero');
                $indicationCell.html('-');
            }
        });
    }

    $('#btn-save-opname').on('click', function() {
        const items = [];
        let hasDiff = false;
        let totalSurplus = 0, totalDeficit = 0, totalItems = 0;

        $('.physical-input').each(function() {
            const $input = $(this);
            const systemQty = parseInt($input.data('system-qty'));
            const physicalQty = parseInt($input.val()) || 0;
            if (systemQty !== physicalQty) {
                hasDiff = true;
                const productName = $input.closest('tr').find('td:nth-child(2)').text().trim();
                items.push({
                    product_id: $input.data('product-id'),
                    product_variant_id: $input.data('variant-id') || null,
                    system_qty: systemQty,
                    physical_qty: physicalQty
                });
                const diff = physicalQty - systemQty;
                totalItems++;
                if (diff > 0) totalSurplus += diff;
                if (diff < 0) totalDeficit += Math.abs(diff);
            }
        });

        if (!hasDiff) {
            if (!confirm('Tidak ada selisih stok. Tetap simpan opname?')) return;
        }

        $('#summary-total-items').text(totalItems);
        $('#summary-total-surplus').text(totalSurplus);
        $('#summary-total-deficit').text(totalDeficit);

        let summaryHtml = '';
        $('.physical-input').each(function() {
            const $input = $(this);
            const systemQty = parseInt($input.data('system-qty'));
            const physicalQty = parseInt($input.val()) || 0;
            if (systemQty !== physicalQty) {
                const productName = $input.closest('tr').find('td:nth-child(2)').text().trim();
                const diff = physicalQty - systemQty;
                summaryHtml += '<tr>';
                summaryHtml += '<td>' + productName + '</td>';
                summaryHtml += '<td class="text-right">' + systemQty + '</td>';
                summaryHtml += '<td class="text-right">' + physicalQty + '</td>';
                summaryHtml += '<td class="text-right ' + (diff > 0 ? 'text-danger' : 'text-success') + ' font-weight-bold">' + (diff > 0 ? '+' : '') + diff + '</td>';
                summaryHtml += '</tr>';
            }
        });
        $('#summary-tbody').html(summaryHtml || '<tr><td colspan="4" class="text-center text-muted">Tidak ada selisih</td></tr>');

        $('#summaryModal').modal('show');
    });

    $('#btn-confirm-save').on('click', function() {
        const warehouseId = $('#warehouse_id').val();
        const notes = $('#notes').val();
        const items = [];

        $('.physical-input').each(function() {
            const $input = $(this);
            const systemQty = parseInt($input.data('system-qty'));
            const physicalQty = parseInt($input.val()) || 0;
            if (systemQty !== physicalQty) {
                items.push({
                    product_id: $input.data('product-id'),
                    product_variant_id: $input.data('variant-id') || null,
                    system_qty: systemQty,
                    physical_qty: physicalQty
                });
            }
        });

        $('#summaryModal').modal('hide');
        $('#loading-overlay').show();

        $.ajax({
            url: '{{ url("admin/manage-master/stock-opname/store") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', warehouse_id: warehouseId, notes: notes, items: items },
            success: function(res) {
                if (res.success) {
                    window.location.href = '{{ url("admin/manage-master/stock-opname") }}';
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                alert('Gagal menyimpan: ' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function() { $('#loading-overlay').hide(); }
        });
    });
});
</script>
@endpush
