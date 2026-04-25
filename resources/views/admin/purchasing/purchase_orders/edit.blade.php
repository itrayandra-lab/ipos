@extends('master')
@section('title', 'Edit Purchase Order - ')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Premium Purchasing Aesthetic */
    .section-header {
        background: #fff;
        padding: 20px 25px !important;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px !important;
        border-left: 5px solid #0d9488;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .section-header h1 {
        font-weight: 800 !important;
        color: #1e293b !important;
        margin-bottom: 0;
    }

    .card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
    }
    .card-header {
        background-color: transparent !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .card-header h4 {
        color: #1e293b;
        font-weight: 800;
        font-size: 16px;
        margin-bottom: 0;
    }

    /* Form Styling */
    .form-group label {
        font-weight: 700;
        color: #475569;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .form-control-premium {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        height: auto;
    }
    .form-control-premium:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }

    /* Table Styling */
    .table-premium thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 12px 15px !important;
    }
    .table-premium tbody td {
        padding: 12px 15px !important;
        vertical-align: middle;
    }

    .btn-premium {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: white !important;
        border: none;
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
    }
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3);
    }

    .total-box {
        background: #f8fafc;
        border-radius: 20px;
        padding: 25px;
        border: 2px solid #0d9488;
    }
    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .total-label {
        color: #64748b;
        font-weight: 600;
    }
    .total-value {
        font-weight: 800;
        color: #1e293b;
    }
    .grand-total {
        font-size: 24px;
        color: #0d9488;
        border-top: 2px dashed #e2e8f0;
        padding-top: 15px;
        margin-top: 15px;
    }

    /* Fix Select2 text wrapping */
    .select2-container--default .select2-selection--single {
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        height: 45px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-weight: 500 !important;
        padding-left: 15px !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.purchasing.purchase_orders.index') }}" class="btn btn-icon mr-3"><i class="fas fa-arrow-left"></i></a>
                <h1>Edit Purchase Order</h1>
            </div>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.purchase_orders.index') }}">PO</a></div>
                <div class="breadcrumb-item active">Edit</div>
            </div>
        </div>

        <form id="form-po" action="{{ route('admin.purchasing.purchase_orders.update', $po->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="section-body">
                <div class="row">
                    <!-- PO Header Info -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Dasar PO</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nomor PO</label>
                                            <input type="text" value="{{ $po->po_number }}" class="form-control form-control-premium" readonly style="background: #f1f5f9; font-family: monospace; font-weight: 800;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal Order <span class="text-danger">*</span></label>
                                            <input type="date" name="po_date" value="{{ $po->po_date->format('Y-m-d') }}" class="form-control form-control-premium" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Supplier <span class="text-danger">*</span></label>
                                            <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ $po->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Gudang Penerimaan <span class="text-danger">*</span></label>
                                            <select name="warehouse_id" id="warehouse_id" class="form-control select2" required>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}" {{ $po->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Estimasi Pengiriman</label>
                                            <input type="date" name="expected_delivery_date" value="{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : '' }}" class="form-control form-control-premium">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Catatan Internal PO</label>
                                            <textarea name="notes" class="form-control form-control-premium" rows="1">{{ $po->notes }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PO Items Table -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Daftar Barang yang Dipesan</h4>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-row">
                                    <i class="fas fa-plus mr-1"></i> Tambah Baris Produk
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-premium mb-0" id="table-items">
                                        <thead>
                                            <tr>
                                                <th width="50px" class="text-center">#</th>
                                                <th width="300px">Produk / SKU</th>
                                                <th width="120px" class="text-center">Qty</th>
                                                <th width="180px">Harga Satuan (Beli)</th>
                                                <th width="180px" class="text-right">Total</th>
                                                <th>Keterangan</th>
                                                <th width="50px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be loaded via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="col-md-6 offset-md-6">
                        <div class="total-box shadow-sm mb-5">
                            <div class="total-row h6">
                                <span class="total-label">Subtotal</span>
                                <span class="total-value" id="display-subtotal-text">Rp 0</span>
                                <input type="hidden" name="subtotal" id="input-subtotal" value="{{ $po->subtotal }}">
                            </div>
                            
                            <div class="row mb-3 mt-3">
                                <div class="col-4">
                                    <label class="small font-weight-bold text-muted">Diskon</label>
                                    <select name="discount_type" id="discount_type" class="form-control form-control-sm">
                                        <option value="percentage" {{ $po->discount_type == 'percentage' ? 'selected' : '' }}>% (Persen)</option>
                                        <option value="fixed" {{ $po->discount_type == 'fixed' ? 'selected' : '' }}>Rp (Nominal)</option>
                                    </select>
                                </div>
                                <div class="col-8">
                                    <label class="small font-weight-bold text-muted">&nbsp;</label>
                                    <input type="text" name="discount_value" id="discount_value" class="form-control text-right font-weight-bold" value="{{ number_format($po->discount_value, 0, ',', '.') }}">
                                    <input type="hidden" name="discount_amount" id="input-discount-amount" value="{{ $po->discount_amount }}">
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-8">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="tax_enable" {{ $po->tax_percentage > 0 ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold text-muted" for="tax_enable">Aktifkan PPN (11%)</label>
                                    </div>
                                </div>
                                <div class="col-4 text-right" id="tax_percentage_container" style="{{ $po->tax_percentage > 0 ? '' : 'display:none;' }}">
                                    <input type="hidden" name="tax_percentage" id="tax_percentage" value="{{ $po->tax_percentage ?: 11 }}">
                                    <span class="total-value" id="display-tax-amount-text">Rp 0</span>
                                    <input type="hidden" name="tax_amount" id="input-tax-amount" value="{{ $po->tax_amount }}">
                                </div>
                            </div>

                            <div class="total-row grand-total">
                                <span class="font-weight-800">Grand Total PO</span>
                                <span id="display-total-text">Rp 0</span>
                                <input type="hidden" name="total" id="input-total" value="{{ $po->total }}">
                            </div>

                            <button type="button" id="btn-save-po" onclick="submitPO()" class="btn btn-premium btn-block mt-4">
                                <i class="fas fa-save mr-2"></i> SIMPAN PERUBAHAN PO
                            </button>
                        </div>
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
        let number = parseFloat(val);
        if (isNaN(number)) return '';
        return number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function parseNumberId(val) {
        if (val === undefined || val === null || val === '') return 0;
        let clean = val.toString().replace(/\./g, '').replace(/,/g, '.');
        return parseFloat(clean) || 0;
    }

    $(document).ready(function () {
        $('.select2').select2();

        // Load existing items
        @foreach($po->items as $item)
            addRow({
                product_id: '{{ $item->product_id }}',
                product_name: '{{ $item->product_name }}',
                qty: '{{ $item->quantity }}',
                price: '{{ $item->unit_price }}',
                description: '{{ $item->description }}'
            });
        @endforeach

        if ($('#table-items tbody tr').length === 0) addRow();

        $('#btn-add-row').on('click', addRow);

        $(document).on('click', '.btn-remove-row', function () {
            if ($('#table-items tbody tr').length > 1) {
                $(this).closest('tr').fadeOut(300, function() {
                    $(this).remove();
                    calculateTotal();
                });
            } else {
                swal('Gagal', 'Minimal harus ada 1 item produk', 'error');
            }
        });

        $(document).on('input', '.qty-input, .price-input', calculateTotal);
        $(document).on('blur', '.qty-input, .price-input, #discount_value', function () {
            $(this).val(formatNumberId(parseNumberId($(this).val())));
        });

        $('#discount_type, #discount_value, #tax_enable').on('change input', calculateTotal);

        $('#tax_enable').on('change', function() {
            if ($(this).is(':checked')) $('#tax_percentage_container').fadeIn();
            else $('#tax_percentage_container').fadeOut();
        });

        calculateTotal();
    });

    function addRow(data = null) {
        rowCount++;
        let qty = data ? data.qty : 1;
        let price = data ? data.price : 0;
        let desc = data ? data.description : '';
        let pId = data ? data.product_id : '';
        let pName = data ? data.product_name : '';

        let html = `
            <tr style="display:none;">
                <td class="text-center font-weight-bold text-muted">${$('#table-items tbody tr').length + 1}</td>
                <td>
                    <select name="items[${rowCount}][product_name]" class="form-control product-select" required>
                        ${pName ? `<option value="${pName}" selected>${pName}</option>` : ''}
                    </select>
                    <input type="hidden" name="items[${rowCount}][product_id]" class="product-id-hidden" value="${pId}">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][qty]" class="form-control text-right qty-input font-weight-bold" value="${formatNumberId(qty)}" required>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                        <input type="text" name="items[${rowCount}][price]" class="form-control text-right price-input font-weight-bold" value="${formatNumberId(price)}" required>
                    </div>
                </td>
                <td class="text-right font-weight-bold text-dark">
                    <span class="row-total-display">Rp 0</span>
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][description]" class="form-control form-control-sm" value="${desc}" placeholder="Spec / Ukuran / Batas Exp...">
                    <input type="hidden" name="items[${rowCount}][satuan]" value="">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm text-danger btn-remove-row"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
        let $row = $(html);
        $('#table-items tbody').append($row);
        $row.fadeIn(300);
        initProductSelect2($row.find('.product-select'));
        calculateTotal();
    }

    function initProductSelect2(element) {
        element.select2({
            ajax: {
                url: "{{ route('admin.purchasing.purchase_orders.get_products') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ search: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            },
            placeholder: 'Ketik nama produk / SKU...',
            minimumInputLength: 2,
            width: '100%'
        }).on('select2:select', function (e) {
            let data = e.params.data;
            let row = $(this).closest('tr');
            row.find('.product-id-hidden').val(data.product_id || '');
            row.find('.price-input').val(formatNumberId(data.price || 0));
            calculateTotal();
        });
    }

    function calculateTotal() {
        let subtotal = 0;
        $('#table-items tbody tr').each(function () {
            let row = $(this);
            let qty = parseNumberId(row.find('.qty-input').val());
            let price = parseNumberId(row.find('.price-input').val());
            let rowTotal = qty * price;
            subtotal += rowTotal;
            row.find('.row-total-display').text('Rp ' + formatNumberId(rowTotal));
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

        $('#table-items tbody tr').each(function (i) { $(this).find('td:first').text(i + 1); });
    }

    function submitPO() {
        let form = $('#form-po');
        let btn = $('#btn-save-po');

        swal({
            title: "Simpan Perubahan PO?",
            icon: "info",
            buttons: ["Batal", "Ya, Simpan"],
        }).then((confirm) => {
            if (confirm) {
                btn.addClass('btn-progress').attr('disabled', true);
                $.LoadingOverlay("show");

                let data = form.serializeArray();
                data.forEach(item => {
                    if (['subtotal', 'discount_amount', 'tax_amount', 'total', 'price', 'qty', 'discount_value'].some(key => item.name.includes(key))) {
                        item.value = parseNumberId(item.value);
                    }
                });

                $.ajax({
                    url: form.attr('action'),
                    method: "POST",
                    data: data,
                    success: function (res) {
                        $.LoadingOverlay("hide");
                        if (res.status === 'success') {
                            swal('Berhasil', res.message, 'success').then(() => {
                                window.location.href = res.redirect;
                            });
                        }
                    },
                    error: function (err) {
                        $.LoadingOverlay("hide");
                        btn.removeClass('btn-progress').attr('disabled', false);
                        swal('Gagal', err.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
