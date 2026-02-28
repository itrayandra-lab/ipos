@extends('master')

@push('styles')
<style>
    /* Fix Select2 text wrapping */
    .select2-container .select2-selection--single {
        height: auto !important;
        min-height: 38px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        white-space: normal !important;
        word-wrap: break-word !important;
        line-height: 1.4 !important;
        padding: 8px 12px !important;
    }
    #items-table th, #items-table td {
        vertical-align: middle !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Buat Invoice Manual</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.invoices.index') }}">Invoice</a></div>
                <div class="breadcrumb-item active">Buat Baru</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.sales.invoices.store') }}" method="POST" id="invoice-form">
                @csrf
                
                {{-- Card 1: Info Invoice --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Info Invoice</h4>
                    </div>
                    <div class="card-body">
                        {{-- Row 1: No Invoice | Tanggal | Tanggal Jatuh Tempo --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. Invoice</label>
                                    <input type="text" name="invoice_number" class="form-control" value="{{ $nextInvoiceNumber }}" placeholder="Otomatis jika kosong">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Jatuh Tempo</label>
                                    <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Row 2: Nama Customer | No. Telepon | Tipe Kelas/Produk --}}
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Customer</label>
                                    <div id="customer-select-wrapper" style="display:none;">
                                        <select name="customer_id" class="form-control select2" id="customer-select" style="width: 100%;">
                                            <option value="">-- Pilih Customer --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}">
                                                    {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="customer-text-wrapper">
                                        <input type="text" name="customer_name" id="customer-name" class="form-control" placeholder="Nama manual">
                                    </div>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" id="is_registered_customer">
                                        <label class="form-check-label" for="is_registered_customer">Customer Terdaftar</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. Telepon</label>
                                    <input type="text" name="customer_phone" id="customer-phone" class="form-control" placeholder="08xx">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipe Transaksi</label>
                                    <select name="transaction_type" id="transaction_type" class="form-control" required>
                                        <option value="produk">Produk</option>
                                        <option value="kelas">Kelas</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Row 3: Metode Pembayaran | Status Pembayaran | Catatan --}}
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Metode Pembayaran</label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="transfer">Transfer</option>
                                        <option value="cash">Cash</option>
                                        <option value="qris">QRIS</option>
                                        <option value="debit">Debit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status Pembayaran</label>
                                    <select name="payment_status" id="payment_status" class="form-control" required>
                                        <option value="unpaid">Belum Bayar</option>
                                        <option value="paid">Lunas</option>
                                        <option value="credit">DP (Credit)</option>
                                        <option value="draft">Draft</option>
                                        <option value="canceled">Batal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="1"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Section DP (Visible if status is credit) --}}
                        <div class="row mt-2" id="dp-section" style="display:none; background: #f9f9f9; padding: 15px; border-radius: 8px;">
                            <div class="col-md-6 border-right">
                                <div class="form-group mb-0">
                                    <label>Input Down Payment (DP)</label>
                                    <div class="input-group">
                                        <input type="number" name="down_payment_amount" id="down_payment_amount" class="form-control" placeholder="Nominal DP">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="calc-dp-50">Set DP 50%</button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Gunakan tombol 50% untuk booking cepat.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label>Input DP Persentase (%)</label>
                                    <input type="number" id="dp_percent" class="form-control" placeholder="Contoh: 30">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Item Invoice --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Item Invoice</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 40%">Produk & Varian</th>
                                        <th style="width:15%">Qty</th>
                                        <th style="width:20%">Harga Satuan (Rp)</th>
                                        <th style="width:20%">Total</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="item-rows">
                                    {{-- Rows added via JS --}}
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>
                </div>

                {{-- Summary Section --}}
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="label-subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 align-items-center">
                                    <span>Pajak (PPN 11%)</span>
                                    <select name="tax_type" id="tax_type" class="form-control form-control-sm" style="width: 100px;">
                                        <option value="none">Non</option>
                                        <option value="ppn">PPN</option>
                                    </select>
                                </div>
                                <div id="tax-amount-row" class="d-flex justify-content-between mb-2" style="display:none !important;">
                                    <span>Nominal Pajak</span>
                                    <span id="label-tax">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 align-items-center border-bottom pb-2">
                                    <span>Diskon</span>
                                    <div>
                                        <span id="label-discount" class="text-danger mr-2">Rp 0</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#discountModal">
                                            <i class="fas fa-tag"></i> Set Diskon
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <h5>Grand Total</h5>
                                    <h5 class="text-primary">Rp <span id="label-grand-total">0</span></h5>
                                </div>
                                <div id="dp-summary-row" class="d-flex justify-content-between mt-2 text-info" style="display:none !important;">
                                    <span>Bayar DP</span>
                                    <span id="label-dp-paid">Rp 0</span>
                                </div>
                                <div id="sisa-summary-row" class="d-flex justify-content-between mt-1 text-warning font-weight-bold" style="display:none !important;">
                                    <span>Sisa Pelunasan</span>
                                    <span id="label-sisa-balance">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5 text-right">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                        <i class="fas fa-save"></i> Simpan Invoice
                    </button>
                </div>

                {{-- Hidden fields for calculation results --}}
                <input type="hidden" name="discount" id="val-discount" value="0">
                <input type="hidden" name="discount_type" id="val-discount-type" value="fixed">
                <input type="hidden" name="tax_amount" id="val-tax-amount" value="0">
            </form>
        </div>
    </section>
</div>

{{-- Discount Modal --}}
<div class="modal fade" id="discountModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Diskon</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Tipe Diskon</label>
                    <select id="modal-discount-type" class="form-control">
                        <option value="fixed">Nominal (Rp)</option>
                        <option value="percent">Persentase (%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nilai Diskon</label>
                    <input type="number" id="modal-discount-val" class="form-control" placeholder="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block" id="apply-discount">Terapkan</button>
            </div>
        </div>
    </div>
</div>

<script>
const batchData = @json($batchList);
</script>
@endsection

@push('scripts')
<script>
    let rowIndex = 0;

    function formatNumber(n) {
        return parseInt(n || 0).toLocaleString('id-ID');
    }

    function buildBatchOptions(selectedId) {
        let html = '<option value="">-- Pilih Produk --</option>';
        batchData.forEach(function(b) {
            const sel = b.id == selectedId ? 'selected' : '';
            html += `<option value="${b.id}" data-price="${b.price}" data-stock="${b.stock}" ${sel}>${b.text}</option>`;
        });
        return html;
    }

    function recalc() {
        let subtotal = 0;
        $('#item-rows tr').each(function() {
            const qty   = parseFloat($(this).find('.qty-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            const sub   = qty * price;
            $(this).find('.subtotal-text').text(formatNumber(sub));
            subtotal += sub;
        });

        $('#label-subtotal').text('Rp ' + formatNumber(subtotal));

        // Tax
        let tax = 0;
        if ($('#tax_type').val() === 'ppn') {
            tax = subtotal * 0.11;
            $('#tax-amount-row').attr('style', 'display: flex !important;');
        } else {
            $('#tax-amount-row').attr('style', 'display: none !important;');
        }
        $('#label-tax').text('Rp ' + formatNumber(tax));
        $('#val-tax-amount').val(tax);

        // Discount
        let discVal = parseFloat($('#modal-discount-val').val()) || 0;
        let discType = $('#modal-discount-type').val();
        let discFinal = (discType === 'percent') ? (subtotal * (discVal / 100)) : discVal;
        
        $('#label-discount').text('Rp ' + formatNumber(discFinal));
        $('#val-discount').val(discFinal);
        $('#val-discount-type').val(discType);

        // Grand Total
        let grand = subtotal + tax - discFinal;
        if (grand < 0) grand = 0;
        $('#label-grand-total').text(formatNumber(grand));

        // DP Logic
        if ($('#payment_status').val() === 'credit') {
            $('#dp-section').slideDown();
            $('#dp-summary-row').attr('style', 'display: flex !important;');
            $('#sisa-summary-row').attr('style', 'display: flex !important;');
            let dp = parseFloat($('#down_payment_amount').val()) || 0;
            let sisa = grand - dp;
            if (sisa < 0) sisa = 0;

            $('#label-dp-paid').text('Rp ' + formatNumber(dp));
            $('#label-sisa-balance').text('Rp ' + formatNumber(sisa));
        } else {
            $('#dp-section').slideUp();
            $('#dp-summary-row').attr('style', 'display: none !important;');
            $('#sisa-summary-row').attr('style', 'display: none !important;');
        }
    }

    function addRow() {
        const idx = rowIndex++;
        const row = `
        <tr>
            <td>
                <select name="items[${idx}][product_batch_id]" class="form-control select2-items batch-select" required>
                    ${buildBatchOptions()}
                </select>
            </td>
            <td>
                <input type="number" name="items[${idx}][qty]" class="form-control qty-input" value="1" min="1" required>
            </td>
            <td>
                <input type="number" name="items[${idx}][price]" class="form-control price-input" value="0" min="0" required>
            </td>
            <td class="text-right"><strong>Rp <span class="subtotal-text">0</span></strong></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
        $('#item-rows').append(row);
        $('.select2-items').last().select2({
            width: '100%'
        });
        recalc();
    }

    $(document).ready(function() {
        addRow();

        $('#add-row').on('click', addRow);
        $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); recalc(); });
        $(document).on('input', '.qty-input, .price-input', recalc);
        $('#tax_type, #payment_status, #down_payment_amount').on('change input', recalc);

        $(document).on('change', '.batch-select', function() {
            const price = $(this).find(':selected').data('price') || 0;
            $(this).closest('tr').find('.price-input').val(price);
            recalc();
        });

        // Discount modal apply
        $('#apply-discount').on('click', function() {
            recalc();
            $('#discountModal').modal('hide');
        });

        // DP Percent logic
        $('#dp_percent').on('input', function() {
        let p = parseFloat($(this).val()) || 0;
        let grand = parseFloat($('#label-grand-total').text().replace(/[^\d]/g, '')) || 0;
        $('#down_payment_amount').val(Math.round(grand * (p / 100)));
        recalc();
        });

        $('#calc-dp-50').on('click', function() {
            $('#dp_percent').val(50).trigger('input');
        });

        // Customer toggle
        $('#is_registered_customer').on('change', function() {
            if ($(this).is(':checked')) {
                $('#customer-select-wrapper').show();
                $('#customer-text-wrapper').hide();
            } else {
                $('#customer-select-wrapper').hide();
                $('#customer-text-wrapper').show();
            }
        });

        $('#customer-select').on('change', function() {
            const opt = $(this).find(':selected');
            if (opt.val()) {
                $('#customer-name').val(opt.data('name'));
                $('#customer-phone').val(opt.data('phone'));
            }
        });
    });
</script>
@endpush
