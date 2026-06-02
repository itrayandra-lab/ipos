@extends('master')
@section('title', 'Edit Purchase Order - ')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
:root {
    --po-teal: #0d9488;
    --po-teal-dark: #0f766e;
    --slate-50: #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --green-50: #f0fdf4;
    --green-600: #16a34a;
    --red-50: #fef2f2;
    --red-500: #ef4444;
    --amber-50: #fffbeb;
    --amber-500: #f59e0b;
}

.po-edit-header {
    background: #fff;
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.po-edit-header h1 {
    font-size: 18px;
    font-weight: 800;
    color: var(--slate-800);
    margin: 0;
}
.po-edit-header .badge-semantic {
    font-size: 11px;
    padding: 4px 12px;
}

.info-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--slate-200);
    padding: 20px 24px;
    margin-bottom: 20px;
}
.info-card-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--slate-400);
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--slate-100);
}
.info-field label {
    font-size: 12px;
    font-weight: 600;
    color: var(--slate-500);
    margin-bottom: 4px;
    display: block;
}
.info-field .form-control {
    border-radius: 8px;
    border: 1px solid var(--slate-200);
    padding: 8px 12px;
    font-size: 14px;
    height: auto;
    transition: border-color .15s, box-shadow .15s;
}
.info-field .form-control:focus {
    border-color: var(--po-teal);
    box-shadow: 0 0 0 3px rgba(13,148,136,.1);
}
.info-field .form-control[readonly] {
    background: var(--slate-50);
    font-family: monospace;
    font-weight: 700;
}

.audit-bar {
    display: flex;
    gap: 20px;
    padding: 10px 0 0;
    margin-top: 12px;
    border-top: 1px solid var(--slate-100);
    font-size: 12px;
    color: var(--slate-400);
}
.audit-bar span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.items-section {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--slate-200);
    padding: 20px 24px;
    margin-bottom: 20px;
}
.items-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--slate-100);
    margin-bottom: 16px;
}
.items-section-header h3 {
    font-size: 15px;
    font-weight: 700;
    color: var(--slate-700);
    margin: 0;
}

.item-card {
    border: 1px solid var(--slate-200);
    border-radius: 10px;
    padding: 0;
    margin-bottom: 10px;
    background: #fff;
    transition: border-color .2s, box-shadow .2s;
}
.item-card:hover {
    border-color: var(--po-teal);
    box-shadow: 0 2px 8px rgba(13,148,136,.06);
}
.item-card.is-modified {
    border-color: var(--amber-500);
    background: var(--amber-50);
}

.item-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 14px 16px 0;
    gap: 8px;
}
.item-card-select {
    flex: 1;
    min-width: 0;
}
.item-card-select .select2-container { width: 100% !important; }
.item-card-select .select2-container--default .select2-selection--single {
    border-radius: 8px !important;
    border: 1px solid var(--slate-200) !important;
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
}
.item-card-select .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--slate-800) !important;
    font-weight: 600 !important;
    padding-left: 12px !important;
    font-size: 14px;
}
.item-card-select .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
.item-card-remove {
    flex-shrink: 0;
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    background: var(--slate-50);
    color: var(--slate-400);
    cursor: pointer;
    transition: all .15s;
    border: none;
}
.item-card-remove:hover {
    background: var(--red-50);
    color: var(--red-500);
}

.item-card-body {
    padding: 10px 16px 14px;
}
.item-fields-row {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}
.item-field { flex: 1; }
.item-field label {
    font-size: 11px;
    font-weight: 600;
    color: var(--slate-400);
    text-transform: uppercase;
    letter-spacing: .3px;
    display: block;
    margin-bottom: 2px;
}
.item-field .form-control {
    border-radius: 6px;
    border: 1px solid var(--slate-200);
    padding: 6px 10px;
    font-size: 14px;
    font-weight: 700;
    height: auto;
    text-align: right;
    transition: border-color .15s;
}
.item-field .form-control:focus {
    border-color: var(--po-teal);
    box-shadow: 0 0 0 2px rgba(13,148,136,.1);
}
.item-field .input-group-text {
    background: var(--slate-100);
    border: 1px solid var(--slate-200);
    font-size: 12px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 6px 0 0 6px;
}
.item-field .input-group .form-control {
    border-radius: 0 6px 6px 0;
}
.item-total {
    font-size: 16px;
    font-weight: 800;
    color: var(--slate-800);
    text-align: right;
    padding: 6px 0;
}
.item-notes {
    margin-top: 8px;
}
.item-notes .form-control {
    border-radius: 6px;
    border: 1px solid var(--slate-200);
    padding: 6px 10px;
    font-size: 12px;
    height: auto;
    background: var(--slate-50);
}
.item-notes .form-control:focus {
    border-color: var(--po-teal);
    box-shadow: 0 0 0 2px rgba(13,148,136,.1);
}

.summary-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--slate-200);
    padding: 20px;
}
.summary-card.is-sticky { position: sticky; top: 24px; }
.summary-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--slate-400);
    padding-bottom: 14px;
    border-bottom: 1px solid var(--slate-100);
    margin-bottom: 14px;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.summary-label { font-size: 13px; color: var(--slate-500); }
.summary-value { font-size: 14px; font-weight: 700; color: var(--slate-800); }
.summary-hr { border: none; border-top: 1px dashed var(--slate-200); margin: 12px 0; }
.summary-grand .summary-value { font-size: 22px; font-weight: 800; color: var(--po-teal); }

.summary-discount { margin: 8px 0; }
.summary-discount select {
    border-radius: 6px; border: 1px solid var(--slate-200);
    font-size: 12px; padding: 4px 8px; height: auto;
}
.summary-discount input {
    border-radius: 6px; border: 1px solid var(--slate-200);
    font-size: 13px; font-weight: 700; padding: 4px 8px; text-align: right; height: auto;
}
.summary-discount input:focus, .summary-discount select:focus {
    border-color: var(--po-teal);
    box-shadow: 0 0 0 2px rgba(13,148,136,.1);
}

.summary-tax .custom-switch { padding-left: 2.25rem; }
.summary-tax .custom-control-label { font-size: 13px; font-weight: 600; color: var(--slate-500); }
.summary-tax .custom-control-label::before { border-color: var(--slate-300); }

.btn-po-primary {
    background: var(--po-teal);
    color: #fff; border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    transition: all .15s;
    width: 100%;
}
.btn-po-primary:hover { background: var(--po-teal-dark); color: #fff; }
.btn-po-outline {
    background: #fff; color: var(--slate-600);
    border: 1px solid var(--slate-200);
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: all .15s;
    width: 100%;
}
.btn-po-outline:hover { border-color: var(--po-teal); color: var(--po-teal); }

.status-badge-sm {
    font-size: 11px; padding: 2px 10px;
    border-radius: 100px; font-weight: 600;
}
.change-badge {
    display: inline-block;
    background: var(--amber-50);
    color: var(--amber-500);
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    margin-left: 6px;
}
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        @php
            $statusClasses = [
                'draft' => 'badge-neutral', 'submitted' => 'badge-info',
                'approved' => 'badge-success', 'received' => 'badge-success',
                'cancelled' => 'badge-danger'
            ];
            $statusLabels = [
                'draft' => 'Draft', 'submitted' => 'Dikirim',
                'approved' => 'Disetujui', 'received' => 'Selesai',
                'cancelled' => 'Dibatalkan'
            ];
        @endphp

        {{-- ===== HEADER ===== --}}
        <div class="po-edit-header">
            <div class="d-flex align-items-center" style="gap:12px;">
                <a href="{{ route('admin.purchasing.purchase_orders.show', $po->id) }}" class="btn btn-sm" style="border:1px solid var(--slate-200);border-radius:8px;padding:6px 10px;color:var(--slate-500);">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1>Edit PO — {{ $po->po_number }}</h1>
            </div>
            <span class="badge-semantic {{ $statusClasses[$po->status] ?? 'badge-neutral' }}">{{ $statusLabels[$po->status] ?? $po->status }}</span>
        </div>

        <form id="form-po" action="{{ route('admin.purchasing.purchase_orders.update', $po->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- ===== LEFT COLUMN ===== --}}
                <div class="col-lg-8">

                    {{-- INFO PO --}}
                    <div class="info-card">
                        <div class="info-card-title">Informasi PO</div>
                        <div class="row">
                            <div class="col-md-4 info-field">
                                <label>Nomor PO</label>
                                <input type="text" value="{{ $po->po_number }}" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 info-field">
                                <label>Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $po->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 info-field">
                                <label>Gudang <span class="text-danger">*</span></label>
                                <select name="warehouse_id" id="warehouse_id" class="form-control select2" required>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ $po->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4 info-field">
                                <label>Tanggal PO</label>
                                <input type="date" name="po_date" value="{{ $po->po_date->format('Y-m-d') }}" class="form-control" required>
                            </div>
                            <div class="col-md-4 info-field">
                                <label>Estimasi Datang</label>
                                <input type="date" name="expected_delivery_date" value="{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : '' }}" class="form-control">
                            </div>
                            <div class="col-md-4 info-field">
                                <label>Catatan Internal</label>
                                <input type="text" name="notes" class="form-control" value="{{ $po->notes }}" placeholder="Catatan PO">
                            </div>
                        </div>
                        <div class="audit-bar">
                            <span><i class="far fa-user mr-1"></i>Dibuat oleh: {{ $po->creator->name ?? '-' }}</span>
                            <span><i class="far fa-calendar-alt mr-1"></i>Dibuat: {{ $po->created_at->format('d M Y H:i') }}</span>
                            <span><i class="far fa-clock mr-1"></i>Diperbarui: {{ $po->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- ITEMS --}}
                    <div class="items-section">
                        <div class="items-section-header">
                            <h3><i class="fas fa-box mr-2" style="color:var(--po-teal);"></i>Daftar Barang</h3>
                            <button type="button" class="btn btn-sm btn-po-outline" id="btn-add-item" style="width:auto;padding:6px 14px;">
                                <i class="fas fa-plus mr-1"></i> Tambah
                            </button>
                        </div>
                        <div id="items-container">
                            {{-- Items rendered by JS --}}
                        </div>
                    </div>

                </div>

                {{-- ===== RIGHT COLUMN — SUMMARY ===== --}}
                <div class="col-lg-4">
                    <div class="summary-card is-sticky">
                        <div class="summary-title">Ringkasan PO</div>

                        <div class="summary-row">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value" id="display-subtotal-text">Rp 0</span>
                            <input type="hidden" name="subtotal" id="input-subtotal" value="{{ $po->subtotal }}">
                        </div>

                        <div class="summary-discount">
                            <div class="d-flex" style="gap:8px;">
                                <select name="discount_type" id="discount_type" style="flex:0 0 90px;">
                                    <option value="percentage" {{ $po->discount_type == 'percentage' ? 'selected' : '' }}>%</option>
                                    <option value="fixed" {{ $po->discount_type == 'fixed' ? 'selected' : '' }}>Rp</option>
                                </select>
                                <input type="text" name="discount_value" id="discount_value" value="{{ number_format($po->discount_value, 0, ',', '.') }}" placeholder="Diskon" style="flex:1;">
                                <input type="hidden" name="discount_amount" id="input-discount-amount" value="{{ $po->discount_amount }}">
                            </div>
                        </div>

                        <hr class="summary-hr">

                        <div class="summary-row summary-tax">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="tax_enable" {{ $po->tax_percentage > 0 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="tax_enable">PPN 11%</label>
                            </div>
                            <span class="summary-value" id="display-tax-amount-text" style="font-size:13px;">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</span>
                            <input type="hidden" name="tax_percentage" id="tax_percentage" value="{{ $po->tax_percentage ?: 0 }}">
                            <input type="hidden" name="tax_amount" id="input-tax-amount" value="{{ $po->tax_amount }}">
                        </div>

                        <hr class="summary-hr">

                        <div class="summary-row summary-grand">
                            <span class="summary-label" style="font-weight:700;font-size:14px;">Grand Total</span>
                            <span class="summary-value" id="display-total-text">Rp {{ number_format($po->total, 0, ',', '.') }}</span>
                            <input type="hidden" name="total" id="input-total" value="{{ $po->total }}">
                        </div>

                        <button type="button" id="btn-save-po" onclick="submitPO()" class="btn-po-primary mt-3">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let rowCount = 0;

function formatNumberId(val) {
    if (val === undefined || val === null || val === '') return '';
    let number = parseFloat(val); if (isNaN(number)) return '';
    return number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}
function parseNumberId(val) {
    if (val === undefined || val === null || val === '') return 0;
    let clean = val.toString().replace(/\./g, '').replace(/,/g, '.');
    return parseFloat(clean) || 0;
}

function itemCardHTML(idx, data) {
    data = data || {};
    let qty = data.qty || 1;
    let price = data.price || 0;
    let desc = data.description || '';
    let pId = data.product_id || '';
    let pName = data.product_name || '';
    let selectedOption = pName ? `<option value="${pName.replace(/"/g, '&quot;')}" selected>${pName}</option>` : '';
    return `
        <div class="item-card" data-index="${idx}">
            <div class="item-card-header">
                <div class="item-card-select">
                    <select class="product-select">
                        ${selectedOption}
                    </select>
                    <input type="hidden" name="items[${idx}][product_id]" class="product-id-hidden" value="${pId}">
                    <input type="hidden" name="items[${idx}][product_name]" class="product-name-hidden" value="${pName}">
                </div>
                <button type="button" class="item-card-remove btn-remove-item" title="Hapus item">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="item-card-body">
                <div class="item-fields-row">
                    <div class="item-field" style="flex:0 0 100px;">
                        <label>Qty</label>
                        <input type="text" name="items[${idx}][qty]" class="form-control qty-input" value="${formatNumberId(qty)}" required>
                    </div>
                    <div class="item-field" style="flex:1;">
                        <label>Harga</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                            <input type="text" name="items[${idx}][price]" class="form-control price-input" value="${formatNumberId(price)}" required>
                        </div>
                    </div>
                    <div class="item-field" style="flex:0 0 140px;">
                        <label>Subtotal</label>
                        <div class="item-total row-total-display">Rp ${formatNumberId(qty * price)}</div>
                    </div>
                </div>
                <div class="item-notes">
                    <input type="text" name="items[${idx}][description]" class="form-control" value="${desc}" placeholder="Catatan item (ukuran, batch, exp)">
                    <input type="hidden" name="items[${idx}][satuan]" value="">
                </div>
            </div>
        </div>
    `;
}

function addItem(data) {
    rowCount++;
    let $card = $(itemCardHTML(rowCount, data));
    $('#items-container').append($card);
    $card.hide().fadeIn(200);
    initProductSelect2($card.find('.product-select'), data);
}

function initProductSelect2($el, data) {
    let selectedId = data && data.text ? data.text : '';
    $el.select2({
        ajax: {
            url: "{{ route('admin.purchasing.purchase_orders.get_products') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({ search: params.term }),
            processResults: data => ({ results: data }),
            cache: true
        },
        placeholder: 'Cari produk / SKU...',
        minimumInputLength: 2,
        width: '100%',
        dropdownAutoWidth: true
    }).on('select2:select', function(e) {
        let d = e.params.data;
        let $card = $(this).closest('.item-card');
        $card.find('.product-id-hidden').val(d.product_id || '');
        $card.find('.product-name-hidden').val(d.text || '');
        $card.find('.price-input').val(formatNumberId(d.price || 0));
                    calculateTotal();
    });
}

function calculateTotal() {
    let subtotal = 0;
    $('.item-card').each(function() {
        let $card = $(this);
        let qty = parseNumberId($card.find('.qty-input').val());
        let price = parseNumberId($card.find('.price-input').val());
        let rowTotal = qty * price;
        subtotal += rowTotal;
        $card.find('.row-total-display').text('Rp ' + formatNumberId(rowTotal));
    });

    $('#input-subtotal').val(subtotal);
    $('#display-subtotal-text').text('Rp ' + formatNumberId(subtotal));

    let discType = $('#discount_type').val();
    let discVal = parseNumberId($('#discount_value').val());
    let discAmount = (discType === 'percentage') ? subtotal * (discVal / 100) : discVal;
    $('#input-discount-amount').val(discAmount);

    let taxAmount = 0;
    if ($('#tax_enable').is(':checked')) {
        taxAmount = (subtotal - discAmount) * 0.11;
    }
    $('#input-tax-amount').val(taxAmount);
    $('#display-tax-amount-text').text('Rp ' + formatNumberId(taxAmount));

    let total = subtotal - discAmount + taxAmount;
    $('#input-total').val(total);
    $('#display-total-text').text('Rp ' + formatNumberId(total));
}

function submitPO() {
    let allFilled = true;
    $('.product-name-hidden').each(function() {
        if (!$(this).val().trim()) { allFilled = false; return false; }
    });
    if (!allFilled) {
        iziToast.warning({ title: 'Peringatan', message: 'Lengkapi produk untuk semua item', position: 'topRight' });
        return;
    }

    let form = $('#form-po');
    let btn = $('#btn-save-po');

    Swal.fire({
        title: "Simpan Perubahan?",
        text: "Pastikan data PO sudah benar.",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Ya, Simpan!",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (!result.isConfirmed) return;
        btn.addClass('btn-progress').attr('disabled', true);
        $.LoadingOverlay("show");

        let data = form.serializeArray();
        data.forEach(item => {
            if (['subtotal','discount_amount','tax_amount','total','price','qty','discount_value'].some(k => item.name.includes(k))) {
                item.value = parseNumberId(item.value);
            }
        });

        $.ajax({
            url: form.attr('action'),
            method: "POST",
            data: data,
            success: function(res) {
                $.LoadingOverlay("hide");
                if (res.status === 'success') {
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success' })
                        .then(() => { window.location.href = res.redirect; });
                } else {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function(err) {
                $.LoadingOverlay("hide");
                btn.removeClass('btn-progress').attr('disabled', false);
                let msg = err.responseJSON?.message || err.responseJSON?.errors ? JSON.stringify(err.responseJSON.errors) : 'Terjadi kesalahan sistem';
                Swal.fire('Gagal', msg, 'error');
            }
        });
    });
}

$(document).ready(function() {
    $('.select2').select2();

    // Load existing items
    const existingItems = @json($po->items);
    if (existingItems.length > 0) {
        existingItems.forEach(function(item) {
            addItem({
                product_id: item.product_id,
                product_name: item.product_name,
                qty: item.quantity,
                price: item.unit_price,
                description: item.description || ''
            });
        });
    } else {
        addItem();
    }

    $(document).on('click', '#btn-add-item', function() { addItem(); });

    $(document).on('click', '.btn-remove-item', function() {
        if ($('.item-card').length > 1) {
            let $card = $(this).closest('.item-card');
            $card.fadeOut(200, function() { $card.remove(); calculateTotal(); });
        } else {
            Swal.fire('Gagal', 'Minimal 1 item produk', 'error');
        }
    });

    $(document).on('input', '.qty-input, .price-input', calculateTotal);

    $(document).on('blur', '.qty-input, .price-input, #discount_value', function() {
        let val = parseNumberId($(this).val());
        $(this).val(formatNumberId(val));
    });

    $('#discount_type, #discount_value, #tax_enable').on('change input', calculateTotal);

    $('#tax_enable').on('change', function() {
        $('#tax_percentage').val($(this).is(':checked') ? 11 : 0);
    });

    calculateTotal();
});
</script>
@endpush
