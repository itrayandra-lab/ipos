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
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Edit Surat Jalan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></div>
                <div class="breadcrumb-item active">Edit</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.sales.delivery_notes.update', $deliveryNote->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card">
                    <div class="card-header">
                        <h4>Info Surat Jalan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="transaction_date" class="form-control" value="{{ $deliveryNote->transaction_date->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Metode Pengiriman</label>
                                    <select name="delivery_type" class="form-control" required>
                                        <option value="pickup" {{ $deliveryNote->delivery_type == 'pickup' ? 'selected' : '' }}>Pickup</option>
                                        <option value="kurir" {{ $deliveryNote->delivery_type == 'kurir' ? 'selected' : '' }}>Kurir</option>
                                        <option value="ekspedisi" {{ $deliveryNote->delivery_type == 'ekspedisi' ? 'selected' : '' }}>Ekspedisi</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <div id="customer-select-wrapper" style="display:none;">
                                        <select name="customer_id" class="form-control select2" id="customer-select" style="width: 100%;">
                                            <option value="">-- Pilih Customer --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address }}" {{ $deliveryNote->customer_id == $c->id ? 'selected' : '' }}>
                                                    {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="customer-text-wrapper">
                                        <input type="text" name="customer_name" id="customer-name" class="form-control" value="{{ $deliveryNote->customer_name }}" placeholder="Nama manual" required>
                                    </div>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" id="is_registered_customer" {{ $deliveryNote->customer_id ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_registered_customer">Customer Terdaftar</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No. Telepon</label>
                                    <input type="text" name="customer_phone" id="customer-phone" class="form-control" value="{{ $deliveryNote->customer_phone }}" placeholder="08xx">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Alamat Pengiriman</label>
                            <textarea name="delivery_address" id="delivery-address" class="form-control" rows="3" placeholder="Masukkan alamat tujuan pengiriman">{{ $deliveryNote->delivery_address }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Catatan / Keterangan</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $deliveryNote->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Barang</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 55%">Nama Barang (Batch)</th>
                                        <th style="width: 12%">Qty</th>
                                        <th style="width: 25%">Keterangan</th>
                                        <th style="width: 8%"></th>
                                    </tr>
                                </thead>
                                <tbody id="item-rows">
                                    @foreach($deliveryNote->items as $index => $item)
                                    <tr>
                                        <td>
                                            <select name="items[{{ $index }}][product_batch_id]" class="form-control select2-items" required>
                                                <option value="">-- Pilih Barang --</option>
                                                @foreach($batchList as $batch)
                                                    <option value="{{ $batch['id'] }}" {{ $item->product_batch_id == $batch['id'] ? 'selected' : '' }}>
                                                        {{ $batch['text'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][qty]" class="form-control" value="{{ $item->qty }}" min="1" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][description]" class="form-control" value="{{ $item->description }}" placeholder="Keterangan (opsional)">
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

                <div class="mb-5 text-right">
                    <a href="{{ route('admin.sales.delivery_notes.index') }}" class="btn btn-secondary btn-lg px-5">
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
const nettoAttributes = @json($nettoAttributes);
let rowIndex = {{ count($deliveryNote->items) }};

function buildBatchOptions() {
    let html = '<option value="">-- Pilih Barang --</option>';
    batchData.forEach(function(b) {
        html += `<option value="${b.id}">${b.text}</option>`;
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
            <input type="number" name="items[${idx}][qty]" class="form-control" value="1" min="1" required>
        </td>
        <td>
            <input type="text" name="items[${idx}][description]" class="form-control" placeholder="Keterangan (opsional)">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
        </td>
    </tr>`;
    $('#item-rows').append(row);
    $('.select2-items').last().select2({ width: '100%' });
}

$(document).ready(function() {
    $('.select2-items').select2({ width: '100%' });

    $('#add-row').on('click', addRow);
    $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); });

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
        const opt = $(this).find(':selected');
        if (opt.val()) {
            $('#customer-name').val(opt.data('name'));
            $('#customer-phone').val(opt.data('phone'));
            $('#delivery-address').val(opt.data('address'));
        }
    });

    // Trigger change event on load to set initial state
    $('#is_registered_customer').trigger('change');
});
</script>
@endsection
