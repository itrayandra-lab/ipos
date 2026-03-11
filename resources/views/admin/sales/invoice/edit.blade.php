@extends('master')

@push('styles')
<style>
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
            <h1>Edit Invoice</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.invoices.index') }}">Invoice</a></div>
                <div class="breadcrumb-item active">Edit</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.sales.invoices.update', $transaction->id) }}" method="POST" id="invoice-form">
                @csrf
                @method('PUT')
                
                <div class="card">
                    <div class="card-header">
                        <h4>Info Invoice</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. Invoice</label>
                                    <input type="text" name="invoice_number" class="form-control" value="{{ $transaction->invoice_number }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="transaction_date" class="form-control" value="{{ $transaction->created_at->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Jatuh Tempo</label>
                                    <input type="date" name="due_date" class="form-control" value="{{ $transaction->due_date ? $transaction->due_date->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Customer</label>
                                    <div id="customer-select-wrapper" style="display:none;">
                                        <select name="customer_id" class="form-control select2" id="customer-select" style="width: 100%;">
                                            <option value="">-- Pilih Customer --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}" {{ $transaction->customer_id == $c->id ? 'selected' : '' }}>
                                                    {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="customer-text-wrapper">
                                        <input type="text" name="customer_name" id="customer-name" class="form-control" value="{{ $transaction->customer_name }}" placeholder="Nama manual">
                                    </div>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" id="is_registered_customer" {{ $transaction->customer_id ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_registered_customer">Customer Terdaftar</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. Telepon</label>
                                    <input type="text" name="customer_phone" id="customer-phone" class="form-control" value="{{ $transaction->customer_phone }}" placeholder="08xx">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Metode Pembayaran</label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="cash" {{ $transaction->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="transfer" {{ $transaction->payment_method == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                        <option value="qris" {{ $transaction->payment_method == 'qris' ? 'selected' : '' }}>QRIS</option>
                                        <option value="debit" {{ $transaction->payment_method == 'debit' ? 'selected' : '' }}>Debit/EDC</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status Pembayaran</label>
                                    <select name="payment_status" class="form-control" required>
                                        <option value="draft" {{ $transaction->payment_status == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="unpaid" {{ $transaction->payment_status == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                                        <option value="paid" {{ $transaction->payment_status == 'paid' ? 'selected' : '' }}>Lunas</option>
                                        <option value="credit" {{ $transaction->payment_status == 'credit' ? 'selected' : '' }}>Kredit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ $transaction->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Item</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50%">Produk</th>
                                        <th style="width: 15%">Qty</th>
                                        <th style="width: 20%">Harga</th>
                                        <th style="width: 15%">Subtotal</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="item-rows">
                                    @foreach($transaction->items as $index => $item)
                                    <tr>
                                        <td>
                                            <select name="items[{{ $index }}][product_batch_id]" class="form-control select2-items" required>
                                                <option value="">-- Pilih Barang --</option>
                                                @foreach($batchList as $batch)
                                                    <option value="{{ $batch['id'] }}" data-price="{{ $batch['price'] }}" {{ $item->product_batch_id == $batch['id'] ? 'selected' : '' }}>
                                                        {{ $batch['text'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][qty]" class="form-control item-qty" value="{{ $item->qty }}" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][price]" class="form-control item-price" value="{{ $item->price }}" min="0" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control item-subtotal" value="Rp {{ number_format($item->subtotal, 0, ',', '.') }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Ringkasan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subtotal</label>
                                    <input type="text" id="subtotal" class="form-control" value="Rp {{ number_format($transaction->total_amount + $transaction->tax_amount - $transaction->discount, 0, ',', '.') }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Diskon</label>
                                    <input type="number" name="discount" id="discount" class="form-control" value="{{ $transaction->discount }}" min="0" step="0.01">
                                </div>
                                <div class="form-group">
                                    <label>Pajak</label>
                                    <input type="number" name="tax_amount" id="tax_amount" class="form-control" value="{{ $transaction->tax_amount }}" min="0" step="0.01">
                                </div>
                                <div class="form-group">
                                    <label><strong>Total</strong></label>
                                    <input type="text" id="total" class="form-control" value="Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}" readonly style="font-size: 18px; font-weight: bold;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5 text-right">
                    <a href="{{ route('admin.sales.invoices.index') }}" class="btn btn-secondary btn-lg px-5">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
const batchData = @json($batchList);
let rowIndex = {{ count($transaction->items) }};

function buildBatchOptions() {
    let html = '<option value="">-- Pilih Barang --</option>';
    batchData.forEach(function(b) {
        html += `<option value="${b.id}" data-price="${b.price}">${b.text}</option>`;
    });
    return html;
}

function addRow() {
    const idx = rowIndex++;
    const row = `
    <tr>
        <td>
            <select name="items[${idx}][product_batch_id]" class="form-control select2-items" required>
                ${buildBatchOptions()}
            </select>
        </td>
        <td>
            <input type="number" name="items[${idx}][qty]" class="form-control item-qty" value="1" min="1" required>
        </td>
        <td>
            <input type="number" name="items[${idx}][price]" class="form-control item-price" value="0" min="0" step="0.01" required>
        </td>
        <td>
            <input type="text" class="form-control item-subtotal" value="Rp 0" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
        </td>
    </tr>`;
    $('#item-rows').append(row);
    $('.select2-items').last().select2({ width: '100%' });
    updateTotals();
}

function updateTotals() {
    let subtotal = 0;
    $('#item-rows tr').each(function() {
        let qty = parseFloat($(this).find('.item-qty').val()) || 0;
        let price = parseFloat($(this).find('.item-price').val()) || 0;
        let itemSubtotal = qty * price;
        subtotal += itemSubtotal;
        $(this).find('.item-subtotal').val('Rp ' + itemSubtotal.toLocaleString('id-ID'));
    });
    
    let discount = parseFloat($('#discount').val()) || 0;
    let tax = parseFloat($('#tax_amount').val()) || 0;
    let total = (subtotal + tax) - discount;
    
    $('#subtotal').val('Rp ' + subtotal.toLocaleString('id-ID'));
    $('#total').val('Rp ' + total.toLocaleString('id-ID'));
}

$(document).ready(function() {
    $('.select2-items').select2({ width: '100%' });
    
    $('#add-row').on('click', addRow);
    
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });
    
    $(document).on('change', '.item-qty, .item-price, #discount, #tax_amount', updateTotals);
    
    $(document).on('change', '.select2-items', function() {
        let option = $(this).find(':selected');
        let price = option.data('price');
        $(this).closest('tr').find('.item-price').val(price);
        updateTotals();
    });
    
    $('#is_registered_customer').on('change', function() {
        if ($(this).is(':checked')) {
            $('#customer-select-wrapper').show();
            $('#customer-text-wrapper').hide();
            $('#customer-name').removeAttr('required');
        } else {
            $('#customer-select-wrapper').hide();
            $('#customer-text-wrapper').show();
            $('#customer-name').attr('required', 'required');
        }
    });
    
    $('#customer-select').on('change', function() {
        let opt = $(this).find(':selected');
        if (opt.val()) {
            $('#customer-name').val(opt.data('name'));
            $('#customer-phone').val(opt.data('phone'));
        }
    });
    
    updateTotals();
});
</script>
@endsection
