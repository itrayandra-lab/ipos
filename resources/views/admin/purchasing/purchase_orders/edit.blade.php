@extends('master')

@section('title', 'Edit Purchase Order')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Fix Select2 text wrapping */
        .select2-container .select2-selection--single {
            height: auto !important;
            min-height: 42px !important;
            border-color: #e4e6fc !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            white-space: normal !important;
            word-wrap: break-word !important;
            word-break: break-all !important;
            text-overflow: inherit !important;
            overflow: visible !important;
            line-height: 1.4 !important;
            padding: 8px 8px 8px 8px !important;
            display: block !important;
            width: 100% !important;
            color: #444 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            position: absolute !important;
            right: 5px !important;
            display: flex !important;
            align-items: center !important;
        }

        #table-items {
            table-layout: fixed !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }

        #table-items th,
        #table-items td {
            overflow: visible !important;
            vertical-align: middle !important;
            word-wrap: break-word !important;
            height: auto !important;
        }

        #table-items th:nth-child(1), #table-items td:nth-child(1) { width: 45px !important; }
        #table-items th:nth-child(2), #table-items td:nth-child(2) { width: 250px !important; }
        #table-items th:nth-child(3), #table-items td:nth-child(3) { width: 150px !important; }
        #table-items th:nth-child(4), #table-items td:nth-child(4) { width: 150px !important; }
        #table-items th:nth-child(5), #table-items td:nth-child(5) { width: 140px !important; }
        #table-items th:nth-child(6), #table-items td:nth-child(6) { width: auto !important; }
        #table-items th:nth-child(7), #table-items td:nth-child(7) { width: 45px !important; }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('admin.purchasing.purchase_orders.index') }}" class="btn btn-icon"><i
                            class="fas fa-arrow-left"></i></a>
                </div>
                <h1>Edit Purchase Order {{ $po->po_number }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="#">Pembelian</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.purchase_orders.index') }}">Order
                            Pembelian</a></div>
                    <div class="breadcrumb-item">Edit Purchase Order</div>
                </div>
            </div>

            <form id="form-po" action="{{ route('admin.purchasing.purchase_orders.update', $po->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="section-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>No. Purchase Order</label>
                                                <input type="text" value="{{ $po->po_number }}" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal PO <span class="text-danger">*</span></label>
                                                <input type="date" name="po_date" value="{{ $po->po_date->format('Y-m-d') }}"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Gudang Tujuan <span class="text-danger">*</span></label>
                                                <select name="warehouse_id" id="warehouse_id" class="form-control select2" required>
                                                    @foreach($warehouses as $wh)
                                                        <option value="{{ $wh->id }}" {{ $po->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Supplier <span class="text-danger">*</span></label>
                                                <select name="supplier_id" id="supplier_id" class="form-control select2"
                                                    required>
                                                    <option value="">Pilih Supplier</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}" {{ $po->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Estimasi Pengiriman</label>
                                                <input type="date" name="expected_delivery_date" value="{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : '' }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Keterangan / Catatan</label>
                                        <textarea name="notes" class="form-control" style="height: 42px;">{{ $po->notes }}</textarea>
                                    </div>

                                    <hr>

                                    <div class="section-title">Item Produk</div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-md" id="table-items">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th>Produk</th>
                                                    <th>Qty</th>
                                                    <th>Harga (Rp)</th>
                                                    <th>Jumlah</th>
                                                    <th>Keterangan</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Row template will be inserted here -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="7">
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            id="btn-add-row">
                                                            <i class="fas fa-plus"></i> Tambah Baris
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row justify-content-end mt-4">
                                        <div class="col-md-5">
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-4 col-form-label">Subtotal</label>
                                                <div class="col-sm-8">
                                                    <input type="text" id="display-subtotal" class="form-control text-right"
                                                        readonly value="0">
                                                    <input type="hidden" name="subtotal" id="input-subtotal" value="{{ $po->subtotal }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-4 col-form-label">Diskon</label>
                                                <div class="col-sm-3">
                                                    <select name="discount_type" id="discount_type" class="form-control">
                                                        <option value="percentage" {{ $po->discount_type == 'percentage' ? 'selected' : '' }}>%</option>
                                                        <option value="fixed" {{ $po->discount_type == 'fixed' ? 'selected' : '' }}>Rp</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-5">
                                                    <input type="number" name="discount_value" id="discount_value"
                                                        class="form-control text-right" value="{{ $po->discount_value }}">
                                                    <input type="hidden" name="discount_amount" id="input-discount-amount"
                                                        value="{{ $po->discount_amount }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-4 col-form-label">Aktifkan PPN</label>
                                                <div class="col-sm-8 d-flex align-items-center">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="tax_enable" {{ $po->tax_percentage > 0 ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="tax_enable"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-2" id="tax_percentage_container" style="{{ $po->tax_percentage > 0 ? '' : 'display:none;' }}">
                                                <label class="col-sm-4 col-form-label">Pajak (PPN %)</label>
                                                <div class="col-sm-3">
                                                    <input type="number" name="tax_percentage" id="tax_percentage"
                                                        class="form-control text-right" value="{{ $po->tax_percentage > 0 ? $po->tax_percentage : 11 }}">
                                                </div>
                                                <div class="col-sm-5">
                                                    <input type="text" id="display-tax-amount"
                                                        class="form-control text-right" readonly value="0">
                                                    <input type="hidden" name="tax_amount" id="input-tax-amount" value="{{ $po->tax_amount }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-0">
                                                <label class="col-sm-4 col-form-label font-weight-bold">Grand Total</label>
                                                <div class="col-sm-8">
                                                    <input type="text" id="display-total"
                                                        class="form-control text-right font-weight-bold"
                                                        style="font-size: 1.1rem !important;" readonly value="0">
                                                    <input type="hidden" name="total" id="input-total" value="{{ $po->total }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-whitesmoke text-right">
                                    <a href="{{ route('admin.purchasing.purchase_orders.index') }}"
                                        class="btn btn-secondary mr-2">Batal</a>
                                    <button type="button" id="btn-save-po" onclick="submitPO()"
                                        class="btn btn-primary btn-lg px-5">Perbarui Purchase Order</button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let rowCount = 0;

        function formatNumberId(val) {
            if (val === undefined || val === null || val === '') return '';
            let number = parseFloat(val);
            if (isNaN(number)) return '';
            return number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
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
                    description: '{{ $item->description }}',
                    satuan: '{{ $item->satuan }}'
                });
            @endforeach

            $('#btn-add-row').on('click', function () {
                addRow();
            });

            $(document).on('click', '.btn-remove-row', function () {
                if ($('#table-items tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotal();
                } else {
                    swal('Peringatan', 'Minimal harus ada 1 item', 'warning');
                }
            });

            // Calculation events
            $(document).on('input', '.qty-input, .price-input', function () {
                let row = $(this).closest('tr');
                calculateRow(row);
            });

            $(document).on('blur', '.qty-input, .price-input, #discount_value', function () {
                let val = parseNumberId($(this).val());
                $(this).val(formatNumberId(val));
            });

            $('#discount_type, #discount_value, #tax_percentage').on('input change', function () {
                calculateTotal();
            });

            $('#tax_enable').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#tax_percentage_container').show();
                } else {
                    $('#tax_percentage_container').hide();
                }
                calculateTotal();
            });

            calculateTotal();
        });

        function submitPO() {
            if (!$('#supplier_id').val()) {
                swal('Error', 'Harap pilih supplier', 'error');
                return;
            }

            let form = $('#form-po');
            let btn = $('#btn-save-po');

            btn.addClass('btn-progress').attr('disabled', true);
            $.LoadingOverlay("show");

            $.ajax({
                url: form.attr('action'),
                method: "POST",
                data: (function () {
                    let serialized = form.serializeArray();
                    serialized.forEach(item => {
                        if (['subtotal', 'discount_amount', 'tax_amount', 'total', 'price', 'qty'].some(key => item.name.includes(key))) {
                            item.value = parseNumberId(item.value);
                        }
                        if (item.name === 'tax_percentage' && !$('#tax_enable').is(':checked')) {
                            item.value = 0;
                        }
                    });
                    return serialized;
                })(),
                success: function (res) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    $.LoadingOverlay("hide");
                    if (res.status === 'success') {
                        swal('Berhasil', res.message, 'success').then(() => {
                            window.location.href = res.redirect;
                        });
                    }
                },
                error: function (err) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    $.LoadingOverlay("hide");
                    swal('Error', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        }

        function addRow(data = null) {
            rowCount++;
            let qty = data ? data.qty : 1;
            let price = data ? data.price : 0;
            let desc = data ? data.description : '';
            let pId = data ? data.product_id : '';
            let pName = data ? data.product_name : '';
            let satuan = data ? data.satuan : '';

            let html = `
                <tr>
                    <td class="text-center">${$('#table-items tbody tr').length + 1}</td>
                    <td style="width: 250px !important; max-width: 250px !important;">
                        <select name="items[${rowCount}][product_name]" class="form-control product-select" data-placeholder="Pilih Produk..." style="width: 100% !important;">
                            ${pName ? `<option value="${pName}" selected>${pName}</option>` : '<option value=""></option>'}
                        </select>
                        <input type="hidden" name="items[${rowCount}][product_id]" class="product-id-hidden" value="${pId}">
                    </td>
                    <td style="width: 150px !important;">
                        <input type="text" name="items[${rowCount}][qty]" class="form-control qty-input text-right" value="${formatNumberId(qty)}" required>
                    </td>
                    <td style="width: 150px !important;">
                        <input type="text" name="items[${rowCount}][price]" class="form-control price-input text-right" value="${formatNumberId(price)}" required>
                    </td>
                    <td class="text-right" style="width: 140px !important;">
                        <div class="row-total-container font-weight-bold" style="padding-top: 10px;">
                            Rp <span class="row-total-display">0</span>
                        </div>
                        <input type="hidden" class="row-total" value="0">
                    </td>
                    <td>
                        <input type="text" name="items[${rowCount}][description]" class="form-control description-input" placeholder="Keterangan..." value="${desc}">
                        <input type="hidden" name="items[${rowCount}][satuan]" value="${satuan}">
                    </td>
                    <td class="text-center" style="width: 45px !important;">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#table-items tbody').append(html);
            initProductSelect2($('#table-items tbody tr:last .product-select'));
            
            if (data) {
                calculateRow($('#table-items tbody tr:last'));
            }
        }

        function initProductSelect2(element) {
            element.select2({
                ajax: {
                    url: "{{ route('admin.purchasing.purchase_orders.get_products') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                },
                placeholder: 'Pilih Produk...',
                minimumInputLength: 0,
                width: '100%'
            }).on('select2:select', function (e) {
                let data = e.params.data;
                let row = $(this).closest('tr');

                if (data.product_id) {
                    row.find('.product-id-hidden').val(data.product_id);
                    row.find('.price-input').val(formatNumberId(data.price || 0));
                } else {
                    row.find('.product-id-hidden').val('');
                }
                calculateRow(row);
            });
        }

        function calculateRow(row) {
            let qty = parseNumberId(row.find('.qty-input').val());
            let price = parseNumberId(row.find('.price-input').val());
            let total = qty * price;
            row.find('.row-total').val(total);
            calculateTotal();
        }

        function calculateTotal() {
            let subtotal = 0;
            $('#table-items tbody tr').each(function () {
                let row = $(this);
                let qty = parseNumberId(row.find('.qty-input').val());
                let price = parseNumberId(row.find('.price-input').val());
                let rowTotal = qty * price;
                subtotal += rowTotal;
                row.find('.row-total-display').text(formatNumberId(rowTotal));
            });

            $('#input-subtotal').val(subtotal);
            $('#display-subtotal').val(formatNumberId(subtotal));

            let discountType = $('#discount_type').val();
            let discountValue = parseNumberId($('#discount_value').val());
            let discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = subtotal * (discountValue / 100);
            } else {
                discountAmount = discountValue;
            }

            $('#input-discount-amount').val(discountAmount);

            let taxPercentage = 0;
            if ($('#tax_enable').is(':checked')) {
                taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
            }
            
            let taxAmount = (subtotal - discountAmount) * (taxPercentage / 100);

            $('#input-tax-amount').val(taxAmount);
            $('#display-tax-amount').val(formatNumberId(taxAmount));

            let total = subtotal - discountAmount + taxAmount;
            $('#input-total').val(total);
            $('#display-total').val(formatNumberId(total));

            $('#table-items tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }
    </script>
@endpush
