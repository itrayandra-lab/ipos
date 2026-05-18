@extends('master')
@section('title', 'Edit Pengajuan Barang')
@push('styles')
<style>
.select2-container--bootstrap4 .select2-selection--single,
.select2-container--default .select2-selection--single {
    min-height: calc(1.8125rem + 2px) !important;
    height: auto !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
    white-space: normal !important;
    word-wrap: break-word !important;
    padding-top: 5px;
    padding-bottom: 5px;
}
.select2-container--bootstrap4 .select2-results__option,
.select2-container--default .select2-results__option {
    white-space: normal !important;
    word-wrap: break-word !important;
}
.select2-container .select2-dropdown .select2-results__options {
    max-height: 200px !important; /* Limits to ~5 items depending on wrap */
}
.select2-container {
    max-width: 100% !important;
}
/* Fixed width for Produk column */
#table-items th:nth-child(2),
#table-items td:nth-child(2) {
    min-width: 450px !important;
    width: 450px !important;
}
/* Fixed width for Catatan column */
#table-items th:nth-child(4),
#table-items td:nth-child(4) {
    min-width: 250px !important;
    width: 250px !important;
}
</style>
@endpush
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('branch.stock_requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Edit Pengajuan Barang</h1>
        </div>

        <form id="form-request" action="{{ route('branch.stock_requests.update', $request->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h4>Informasi Pengajuan</h4></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Catatan Pengajuan</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan tambahan untuk pusat...">{{ $request->notes }}</textarea>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold mb-0">Daftar Barang yang Diminta</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                                        <i class="fas fa-plus mr-1"></i> Tambah Item
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="table-items">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="35">#</th>
                                                <th>Produk</th>
                                                <th width="130">Qty Diminta</th>
                                                <th>Catatan</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('branch.stock_requests.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="submit" id="btn-submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
@push('scripts')
<script>
let rowCount = 0;
const variants = @json($variants);
const existingItems = @json($request->items);

$(document).ready(function() {
    $('#btn-add-item').on('click', () => addRow());

    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(item => addRow(item));
    } else {
        addRow();
    }

    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        reindex();
    });

    $('#form-request').on('submit', function(e) {
        e.preventDefault();
        if ($('#table-items tbody tr').length === 0) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Tambahkan minimal 1 item barang!' });
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

function addRow(existing = null) {
    const idx = rowCount++;
    const num = $('#table-items tbody tr').length + 1;
    
    const productId = existing ? existing.product_id : '';
    const variantId = existing ? existing.product_variant_id : '';
    const qty = existing ? existing.qty_requested : '';
    const notes = existing ? (existing.notes || '') : '';

    let options = '<option value="">— Pilih Produk —</option>';
    variants.forEach(v => {
        const selected = (v.id == variantId) ? 'selected' : '';
        options += `<option value="${v.id}" data-product="${v.product_id}" ${selected}>${v.label}</option>`;
    });
    const html = `
        <tr>
            <td class="align-middle text-center">${num}</td>
            <td>
                <select name="items[${idx}][product_variant_id]" class="form-control form-control-sm select-variant" required>
                    ${options}
                </select>
                <input type="hidden" name="items[${idx}][product_id]" class="product-id-input" value="${productId}">
            </td>
            <td>
                <input type="number" name="items[${idx}][qty_requested]" class="form-control form-control-sm" min="1" required placeholder="0" value="${qty}">
            </td>
            <td>
                <textarea name="items[${idx}][notes]" class="form-control form-control-sm" rows="1" style="height: auto; min-height: 38px; resize: vertical; word-wrap: break-word;" placeholder="Catatan item...">${notes}</textarea>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
    $('#table-items tbody').append(html);
    // Set hidden product_id on change
    const row = $('#table-items tbody tr:last');
    row.find('.select-variant').select2({ 
        width: '100%', 
        dropdownAutoWidth: false,
        placeholder: '— Pilih Produk —',
        allowClear: false
    });
    row.find('.select-variant').on('change', function() {
        const selected = variants.find(v => v.id == $(this).val());
        $(this).closest('tr').find('.product-id-input').val(selected ? selected.product_id : '');
    });
}

function reindex() {
    $('#table-items tbody tr').each((i, tr) => $(tr).find('td:first').text(i + 1));
}
</script>
@endpush
