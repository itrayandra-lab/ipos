@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Edit Surat Jalan (Manual)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></div>
                <div class="breadcrumb-item">Edit</div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.sales.delivery_notes.update', $deliveryNote->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Customer</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Customer (Pilih dari Data Mitra)</label>
                                    <select name="customer_id" id="customer_id" class="form-control select2">
                                        <option value="">-- Manual Input / Umum --</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" 
                                                data-name="{{ $c->name }}" 
                                                data-phone="{{ $c->phone }}"
                                                {{ $deliveryNote->customer_id == $c->id ? 'selected' : '' }}>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nama Customer</label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $deliveryNote->customer_name }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Telepon</label>
                                    <input type="text" name="customer_phone" id="customer_phone" class="form-control" value="{{ $deliveryNote->customer_phone }}">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="transaction_date" class="form-control" value="{{ \Carbon\Carbon::parse($deliveryNote->transaction_date)->format('Y-m-d') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Tipe Pengiriman</label>
                                    <select name="delivery_type" class="form-control">
                                        <option value="pickup" {{ $deliveryNote->delivery_type == 'pickup' ? 'selected' : '' }}>Pickup</option>
                                        <option value="delivery" {{ $deliveryNote->delivery_type == 'delivery' ? 'selected' : '' }}>Delivery</option>
                                        <option value="expedition" {{ $deliveryNote->delivery_type == 'expedition' ? 'selected' : '' }}>Expedisi</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="3">{{ $deliveryNote->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Item Barang / Produk</h4>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <select id="product_selector" class="form-control select2">
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach($batchList as $batch)
                                                <option value="{{ $batch['id'] }}" data-text="{{ $batch['text'] }}" data-stock="{{ $batch['stock'] }}">
                                                    {{ $batch['text'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" id="add_item" class="btn btn-primary btn-block">Tambah</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="item_table">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th style="width: 15%">Qty</th>
                                                <th style="width: 10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($deliveryNote->items as $item)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="items[{{ $loop->index }}][product_batch_id]" value="{{ $item->product_batch_id }}">
                                                    {{ $item->product ? $item->product->name : 'N/A' }} ({{ $item->batch ? $item->batch->batch_no : 'N/A' }})
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $loop->index }}][qty]" class="form-control" value="{{ $item->qty }}" min="1" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('admin.sales.delivery_notes.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-success">Update Surat Jalan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let itemIndex = {{ $deliveryNote->items->count() }};

        $('#customer_id').change(function() {
            let selected = $(this).find(':selected');
            if (selected.val() !== "") {
                $('#customer_name').val(selected.data('name'));
                $('#customer_phone').val(selected.data('phone'));
            }
        });

        $('#add_item').click(function() {
            let selector = $('#product_selector');
            let batchId = selector.val();
            let text = selector.find(':selected').data('text');
            let stock = selector.find(':selected').data('stock');

            if (!batchId) {
                alert('Pilih produk terlebih dahulu');
                return;
            }

            // Check if already exists
            let exists = false;
            $('input[name^="items"]').each(function() {
                if ($(this).val() == batchId) {
                    exists = true;
                    return false;
                }
            });

            if (exists) {
                alert('Produk sudah ada di daftar');
                return;
            }

            let html = `
                <tr>
                    <td>
                        <input type="hidden" name="items[${itemIndex}][product_batch_id]" value="${batchId}">
                        ${text}
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][qty]" class="form-control" value="1" min="1" max="${stock}" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;

            $('#item_table tbody').append(html);
            itemIndex++;
            selector.val('').trigger('change');
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
        });
    });
</script>
@endpush
