@extends('master')

@section('title', 'Buat Purchase Order')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        line-height: 42px !important;
        border-color: #e4e6fc !important;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.purchase_orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Buat Purchase Order Baru</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.purchase_orders.index') }}">Order Pembelian</a></div>
                <div class="breadcrumb-item">Buat Baru</div>
            </div>
        </div>

        <form id="form-po" action="{{ route('admin.purchasing.purchase_orders.store') }}" method="POST">
            @csrf
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No. Purchase Order</label>
                                            <input type="text" value="{{ $po_number }}" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal PO <span class="text-danger">*</span></label>
                                            <input type="date" name="po_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Supplier <span class="text-danger">*</span></label>
                                            <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                                <option value="">Pilih Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Estimasi Pengiriman</label>
                                            <input type="date" name="expected_delivery_date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Keterangan / Catatan</label>
                                    <textarea name="notes" class="form-control" style="height: 42px;"></textarea>
                                </div>

                                <hr>

                                <div class="section-title">Item Produk</div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-md" id="table-items">
                                        <thead>
                                            <tr>
                                                <th width="30">#</th>
                                                <th>Produk</th>
                                                <th>Deskripsi</th>
                                                <th width="80">Satuan</th>
                                                <th width="100">Qty</th>
                                                <th width="150">Harga Satuan</th>
                                                <th width="150">Jumlah</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Row template will be inserted here -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="8">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-row">
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
                                                <input type="text" id="display-subtotal" class="form-control text-right" readonly value="0">
                                                <input type="hidden" name="subtotal" id="input-subtotal" value="0">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label">Diskon</label>
                                            <div class="col-sm-3">
                                                <select name="discount_type" id="discount_type" class="form-control">
                                                    <option value="percentage">%</option>
                                                    <option value="fixed">Rp</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="number" name="discount_value" id="discount_value" class="form-control text-right" value="0">
                                                <input type="hidden" name="discount_amount" id="input-discount-amount" value="0">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label">Pajak (PPN %)</label>
                                            <div class="col-sm-3">
                                                <input type="number" name="tax_percentage" id="tax_percentage" class="form-control text-right" value="11">
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="text" id="display-tax-amount" class="form-control text-right" readonly value="0">
                                                <input type="hidden" name="tax_amount" id="input-tax-amount" value="0">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-4 col-form-label font-weight-bold">Grand Total</label>
                                            <div class="col-sm-8">
                                                <input type="text" id="display-total" class="form-control text-right font-weight-bold" style="font-size: 1.1rem !important;" readonly value="0">
                                                <input type="hidden" name="total" id="input-total" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-whitesmoke text-right">
                                <a href="{{ route('admin.purchasing.purchase_orders.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="button" id="btn-save-po" onclick="submitPO()" class="btn btn-primary btn-lg px-5">Simpan & Ajukan PO</button>
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

    $(document).ready(function() {
        $('.select2').select2();

        // Add first row
        addRow();

        $('#btn-add-row').on('click', function() {
            addRow();
        });

        $(document).on('click', '.btn-remove-row', function() {
            if ($('#table-items tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateTotal();
            } else {
                swal('Peringatan', 'Minimal harus ada 1 item', 'warning');
            }
        });

        // Initialize select2 for products in existing rows
        initProductSelect2($('.product-select'));

        // Calculation events
        $(document).on('input', '.qty-input, .price-input', function() {
            let row = $(this).closest('tr');
            calculateRow(row);
        });

        // Format on blur
        $(document).on('blur', '.qty-input, .price-input, #discount_value', function() {
            let val = parseNumberId($(this).val());
            $(this).val(formatNumberId(val));
        });

        $('#discount_type, #discount_value, #tax_percentage').on('input change', function() {
            calculateTotal();
        });

        $('#form-po').on('submit', function(e) {
            e.preventDefault();
            submitPO();
        });
    });

    function submitPO() {
        console.log('submitPO() function called');
        
        // Simple validation
        if (!$('#supplier_id').val()) {
            swal('Error', 'Harap pilih supplier', 'error');
            return;
        }

        let form = $('#form-po');
        let btn = $('#btn-save-po');

        console.log('Validation passed, sending AJAX...');
        btn.addClass('btn-progress').attr('disabled', true);
        $.LoadingOverlay("show");

        $.ajax({
            url: form.attr('action'),
            method: "POST",
            data: (function() {
                let serialized = form.serializeArray();
                serialized.forEach(item => {
                    // Pre-process quantities and prices back to standard numeric format
                    if (['subtotal', 'discount_amount', 'tax_amount', 'total', 'price', 'qty'].some(key => item.name.includes(key))) {
                        item.value = parseNumberId(item.value);
                    }
                });
                return serialized;
            })(),
            success: function(res) {
                btn.removeClass('btn-progress').attr('disabled', false);
                $.LoadingOverlay("hide");
                if (res.status === 'success') {
                    swal('Berhasil', res.message, 'success').then(() => {
                        window.location.href = res.redirect;
                    });
                }
            },
            error: function(err) {
                btn.removeClass('btn-progress').attr('disabled', false);
                $.LoadingOverlay("hide");
                console.error('AJAX Error:', err);
                swal('Error', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        });
    }

    function addRow() {
        rowCount++;
        let html = `
            <tr>
                <td class="text-center">${$('#table-items tbody tr').length + 1}</td>
                <td>
                    <select name="items[${rowCount}][product_name]" class="form-control product-select" data-placeholder="Cari atau Ketik Produk...">
                        <option value=""></option>
                    </select>
                    <input type="hidden" name="items[${rowCount}][product_id]" class="product-id-hidden">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][description]" class="form-control description-input" placeholder="Varian/Netto...">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][satuan]" class="form-control satuan-input text-center" placeholder="pcs/kg...">
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][qty]" class="form-control qty-input text-center" value="1" required>
                </td>
                <td>
                    <input type="text" name="items[${rowCount}][price]" class="form-control price-input text-right" value="0" required>
                </td>
                <td>
                    <input type="text" class="form-control row-total text-right" readonly value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#table-items tbody').append(html);
        initProductSelect2($('#table-items tbody tr:last .product-select'));
    }

    function initProductSelect2(element) {
        element.select2({
            tags: true,
            ajax: {
                url: "{{ route('admin.purchasing.purchase_orders.get_products') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { search: params.term };
                },
                processResults: function(data) {
                    return { results: data };
                },
                cache: true
            },
            placeholder: 'Cari atau Ketik Produk...',
            minimumInputLength: 0,
            width: '100%',
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true // add additional parameters
                }
            }
        }).on('select2:select', function(e) {
            let data = e.params.data;
            let row = $(this).closest('tr');
            
            if (data.product_id) {
                row.find('.product-id-hidden').val(data.product_id);
                row.find('.description-input').val(''); // Empty by default as requested
                row.find('.price-input').val(data.price || 0);
            } else {
                // New tag
                row.find('.product-id-hidden').val('');
            }
            calculateRow(row);
        });
    }

    function calculateRow(row) {
        let qty = parseNumberId(row.find('.qty-input').val());
        let price = parseNumberId(row.find('.price-input').val());
        let total = qty * price;
        row.find('.row-total').val(formatNumberId(total));
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        $('#table-items tbody tr').each(function() {
            let row = $(this);
            let qty = parseNumberId(row.find('.qty-input').val());
            let price = parseNumberId(row.find('.price-input').val());
            let rowTotal = qty * price;
            subtotal += rowTotal;
            row.find('.row-total').val(formatNumberId(rowTotal));
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
        // Not visually display discount amount in this version yet, but we have fields for it

        let taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
        let taxAmount = (subtotal - discountAmount) * (taxPercentage / 100);

        $('#input-tax-amount').val(taxAmount);
        $('#display-tax-amount').val(formatNumberId(taxAmount));

        let total = subtotal - discountAmount + taxAmount;
        $('#input-total').val(total);
        $('#display-total').val(formatNumberId(total));
        
        // Update table index
        $('#table-items tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
</script>
@endpush
