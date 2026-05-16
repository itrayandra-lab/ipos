@extends('master')
@section('title', 'Input Penjualan Harian')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('branch.sales.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Input Penjualan Harian</h1>
        </div>
        <form id="form-sale" action="{{ route('branch.sales.store') }}" method="POST">
            @csrf
            <div class="section-body">
                <div class="card">
                    <div class="card-header"><h4>Detail Penjualan</h4></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Penjualan <span class="text-danger">*</span></label>
                                    <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Keterangan penjualan...">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0">Daftar Barang Terjual</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                                <i class="fas fa-plus mr-1"></i> Tambah Item
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="table-items">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th><th>Produk</th><th>Stok Tersedia</th>
                                        <th width="120">Qty Terjual</th><th width="150">Harga Jual</th>
                                        <th width="150">Subtotal</th><th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bold">Total</td>
                                        <td class="font-weight-bold text-success" id="grand-total">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('branch.sales.index') }}" class="btn btn-secondary mr-2">Batal</a>
                        <button type="submit" id="btn-submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-save mr-1"></i> Simpan Penjualan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let rowIdx = 0;
const variants = @json($variants);
const batchesByVariant = @json($batchesByVariant);

$(document).ready(function() {
    $('#btn-add-item').on('click', addRow);
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        reindex();
        calcTotal();
    });
    $(document).on('change', '.select-variant', function() {
        const variantId = $(this).val();
        const row = $(this).closest('tr');
        const variant = variants.find(v => v.id == variantId);
        row.find('.input-sell-price').val(variant?.selling_price || 0);
        updateBatchInfo(row, variantId);
        calcSubtotal(row);
    });
    $(document).on('input', '.input-qty', function() {
        const row = $(this).closest('tr');
        const variantId = row.find('.select-variant').val();
        updateBatchInfo(row, variantId);
        calcSubtotal(row);
        calcTotal();
    });
    $(document).on('input', '.input-sell-price', function() {
        calcSubtotal($(this).closest('tr'));
        calcTotal();
    });

    $('#form-sale').on('submit', function(e) {
        e.preventDefault();
        if ($('#table-items tbody tr').length === 0) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Tambahkan minimal 1 item barang terjual!' });
            return;
        }
        const btn = $('#btn-submit');
        const ori = btn.html();
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
        $.ajax({
            url: $(this).attr('action'), method: 'POST', data: $(this).serialize(),
            success: res => {
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1800, showConfirmButton: false })
                        .then(() => location.href = res.redirect);
                } else {
                    btn.attr('disabled', false).html(ori);
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: err => {
                btn.attr('disabled', false).html(ori);
                Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Terjadi kesalahan' });
            }
        });
    });
});

function addRow() {
    const idx = rowIdx++;
    let opts = '<option value="">— Pilih Produk —</option>';
    variants.forEach(v => {
        opts += `<option value="${v.id}" data-product-id="${v.product_id}">${v.label}</option>`;
    });
    const html = `
        <tr>
            <td class="align-middle text-center">${$('#table-items tbody tr').length + 1}</td>
            <td>
                <select name="items[${idx}][product_variant_id]" class="form-control form-control-sm select-variant" required>
                    ${opts}
                </select>
                <input type="hidden" name="items[${idx}][product_id]" class="input-product-id">
            </td>
            <td class="align-middle text-center stok-info text-muted" style="font-size:12px">—</td>
            <td><input type="number" name="items[${idx}][qty_sold]" class="form-control form-control-sm input-qty" min="1" required placeholder="0"></td>
            <td><input type="number" name="items[${idx}][sell_price]" class="form-control form-control-sm input-sell-price" min="0" required placeholder="0"></td>
            <td class="align-middle font-weight-bold subtotal-cell">Rp 0</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
    $('#table-items tbody').append(html);
    const $select = $('#table-items tbody tr:last .select-variant');
    $select.select2({ width: '100%' });
    $select.on('change', function() {
        const v = variants.find(x => x.id == $(this).val());
        $(this).closest('tr').find('.input-product-id').val(v?.product_id || '');
    });
}

function updateBatchInfo(row, variantId) {
    const qty = parseInt(row.find('.input-qty').val()) || 0;
    const batches = batchesByVariant[variantId] || [];
    const totalStock = batches.reduce((sum, b) => sum + b.qty, 0);
    let html = `<span class="font-weight-bold text-${totalStock > 0 ? 'success' : 'muted'}">${totalStock.toLocaleString('id-ID')}</span>`;
    if (batches.length > 0) {
        html += '<br><small class="text-muted">';
        let remaining = qty || totalStock;
        batches.forEach((b, i) => {
            const take = Math.min(b.qty, remaining);
            if (take > 0) {
                html += (i > 0 ? '<br>' : '') + `<span class="text-info">${b.batch_no}</span> (${b.expiry_date || '-'}) <span class="font-weight-bold">${take.toLocaleString('id-ID')}</span>`;
                remaining -= take;
            }
        });
        html += '</small>';
    }
    row.find('.stok-info').html(html);
}

function calcSubtotal(row) {
    const qty   = parseInt(row.find('.input-qty').val()) || 0;
    const price = parseInt(row.find('.input-sell-price').val()) || 0;
    const sub   = qty * price;
    row.find('.subtotal-cell').text('Rp ' + sub.toLocaleString('id-ID'));
}

function calcTotal() {
    let total = 0;
    $('#table-items tbody tr').each(function() {
        const qty   = parseInt($(this).find('.input-qty').val()) || 0;
        const price = parseInt($(this).find('.input-sell-price').val()) || 0;
        total += qty * price;
    });
    $('#grand-total').text('Rp ' + total.toLocaleString('id-ID'));
}

function reindex() {
    $('#table-items tbody tr').each((i, tr) => $(tr).find('td:first').text(i + 1));
}
</script>
@endpush