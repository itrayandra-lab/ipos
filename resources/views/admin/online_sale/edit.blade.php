@extends('master')
@section('title', 'Edit Rekam Penjualan')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    .section-header {
        background: #fff;
        padding: 20px 25px !important;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px !important;
        border-left: 5px solid #0d9488;
    }

    .premium-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 40px rgba(0,0,0,0.04);
        margin-bottom: 30px;
    }

    .card-header-premium {
        background: #fff;
        padding: 22px 25px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: 20px 20px 0 0;
    }

    .card-header-premium h4 {
        margin: 0;
        font-weight: 700;
        color: #0d9488;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-label-custom {
        font-weight: 700;
        color: #64748b;
        font-size: 12px;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-premium {
        border-radius: 10px !important;
        border: 1.5px solid #e2e8f0 !important;
        padding: 10px 15px !important;
        height: auto !important;
        transition: all 0.3s ease;
    }

    .form-control-premium:focus {
        border-color: #0d9488 !important;
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1) !important;
    }

    /* Select2 Table Style */
    .select2-container--default .select2-selection--single {
        border-radius: 10px !important;
        border: 1.5px solid #e2e8f0 !important;
        height: auto !important;
        min-height: 42px !important;
        padding-top: 5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        white-space: normal !important;
        word-wrap: break-word !important;
        line-height: 1.4 !important;
        padding: 8px 12px !important;
        font-size: 13px !important;
    }

    #items-table th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 11px !important;
        letter-spacing: 0.5px !important;
        padding: 15px !important;
        border: none !important;
    }

    #items-table td {
        padding: 12px 15px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .amount-input {
        font-weight: 700 !important;
        text-align: right !important;
        color: #0f172a !important;
    }

    .subtotal-label {
        font-weight: 800;
        color: #0d9488;
        font-size: 16px;
    }

    .btn-premium-save {
        background: var(--primary-gradient) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 12px 35px !important;
        font-weight: 700 !important;
        color: #fff !important;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 13px !important;
        box-shadow: 0 4px 15px rgba(13, 148, 136, 0.3) !important;
        transition: all 0.3s ease;
    }

    .btn-premium-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 148, 136, 0.4) !important;
    }

    .card-footer-premium {
        background: #f8fafc;
        padding: 25px 30px;
        border-top: 1px solid #f1f5f9;
        border-radius: 0 0 20px 20px;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Edit Rekam Penjualan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Data Transaksi</a></div>
                <div class="breadcrumb-item active">Edit #{{ $transaction->transaction_code ?: $transaction->id }}</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            <form action="{{ route('admin.online_sale.update', $transaction->id) }}" method="POST" enctype="multipart/form-data" id="online-sale-form">
                @csrf
                
                <!-- TOP: Order Information -->
                <div class="premium-card">
                    <div class="card-header-premium">
                        <h4><i class="fas fa-info-circle mr-2"></i> Informasi Saluran & Pesanan</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label-custom">Saluran / Platform</label>
                                    <select name="source" class="form-control form-control-premium select2" required id="source-select">
                                        <option value="">Pilih Saluran...</option>
                                        @foreach($channels as $channel)
                                            <option value="{{ $channel->slug }}" {{ $transaction->source == $channel->slug ? 'selected' : '' }}>{{ $channel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label-custom">Nama Customer</label>
                                    <input type="text" name="customer_name" class="form-control form-control-premium" value="{{ $transaction->notes }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label-custom">No. Pesanan / ID Transaksi</label>
                                    <input type="text" name="notes" class="form-control form-control-premium" value="{{ $transaction->notes }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label-custom">Update Bukti Bayar (Opsional)</label>
                                    <input type="file" name="payment_receipt" class="form-control form-control-premium">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label-custom">Tanggal Transaksi</label>
                                    <input type="date" name="transaction_date" class="form-control form-control-premium" value="{{ old('transaction_date', $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label-custom">Biaya Admin Marketplace (Rp)</label>
                                    <input type="number" name="discount" id="marketplace-discount" class="form-control form-control-premium discount-input" placeholder="0" value="{{ (int)$transaction->discount }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MIDDLE: Product List -->
                <div class="premium-card" style="overflow: visible;">
                    <div class="card-header-premium">
                        <h4><i class="fas fa-shopping-basket mr-2"></i> Item Penjualan</h4>
                    </div>
                    <div class="card-body p-0" style="overflow: visible;">
                        <div class="table-outer" style="overflow: visible;">
                            <table class="table mb-0" id="items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 45%;">Produk & Batch</th>
                                        <th style="width: 15%;">Qty</th>
                                        <th style="width: 20%;">Harga Satuan</th>
                                        <th style="width: 15%;">Total</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-container">
                                    @foreach($transaction->items as $index => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $index }}][product_batch_id]" class="form-control batch-dropdown select2-item" required>
                                                <option value="">-- Pilih Produk --</option>
                                                @foreach($batchList as $batch)
                                                    <option value="{{ $batch->id }}" 
                                                            data-prices='@json($batch->prices)' 
                                                            data-stock="{{ $batch->stock }}"
                                                            {{ $item->product_batch_id == $batch->id ? 'selected' : '' }}>
                                                        {{ $batch->text }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][qty]" class="form-control form-control-premium qty-input" value="{{ $item->qty }}" min="1" required>
                                            <small class="batch-info text-primary font-weight-bold mt-1 d-block"></small>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-0 small">Rp</span>
                                                </div>
                                                <input type="number" name="items[{{ $index }}][price]" class="form-control form-control-premium price-input amount-input" value="{{ (int)$item->price }}" required min="0">
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-dark">Rp <span class="row-subtotal">0</span></strong>
                                        </td>
                                        <td class="text-center">
                                            @if($index > 0)
                                            <button type="button" class="btn btn-link text-danger remove-item p-0"><i class="fas fa-times-circle fa-lg"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-item" style="border-radius: 8px; font-weight: 700;">
                                <i class="fas fa-plus mr-1"></i> Tambah Item
                            </button>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM: Summary & Action -->
                <div class="row">
                    <div class="col-md-7"></div>
                    <div class="col-md-5">
                        <div class="premium-card">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted font-weight-bold">SUBTOTAL</span>
                                    <span class="subtotal-label" id="label-subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3" id="discount-row" style="display:none;">
                                    <span class="text-muted font-weight-bold">Biaya Admin Marketplace</span>
                                    <span class="text-danger font-weight-bold">- Rp <span id="label-discount">0</span></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                    <span class="h6 mb-0 font-weight-bold text-dark">TOTAL AKHIR</span>
                                    <span class="h4 mb-0 font-weight-bold text-primary" id="label-grand-total">Rp 0</span>
                                </div>
                            </div>
                            <div class="card-footer-premium text-right">
                                <button type="submit" class="btn btn-premium-save shadow-sm">
                                    <i class="fas fa-sync-alt mr-2"></i> Simpan Perubahan
                                </button>
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
    const batchData = @json($batchList);
    let itemIndex = {{ $transaction->items->count() }};

    function formatNumber(n) {
        return parseInt(n || 0).toLocaleString('id-ID');
    }

    function initSelect2(element) {
        $(element).select2({
            placeholder: 'Cari produk/batch...',
            width: '100%'
        });
    }

    function buildBatchOptions() {
        let html = '<option value="">-- Pilih Produk --</option>';
        batchData.forEach(function(b) {
            html += `<option value="${b.id}" data-prices='${JSON.stringify(b.prices)}' data-stock="${b.stock}">${b.text}</option>`;
        });
        return html;
    }

    function recalc() {
        let total = 0;
        $('.item-row').each(function() {
            const qty = parseFloat($(this).find('.qty-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            const sub = qty * price;
            $(this).find('.row-subtotal').text(formatNumber(sub));
            total += sub;
        });

        const discount = parseFloat($('#marketplace-discount').val()) || 0;
        const grandTotal = Math.max(0, total - discount);

        $('#label-subtotal').text('Rp ' + formatNumber(total));

        if (discount > 0) {
            $('#discount-row').show();
            $('#label-discount').text(formatNumber(discount));
        } else {
            $('#discount-row').hide();
        }

        $('#label-grand-total').text('Rp ' + formatNumber(grandTotal));
    }

    $(document).ready(function() {
        initSelect2('.select2-item');
        recalc();

        $('#add-item').click(function() {
            const idx = itemIndex++;
            const html = `
                <tr class="item-row">
                    <td>
                        <select name="items[${idx}][product_batch_id]" class="form-control batch-dropdown select2-item" required>
                            ${buildBatchOptions()}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${idx}][qty]" class="form-control form-control-premium qty-input" value="1" min="1" required>
                        <small class="batch-info text-primary font-weight-bold mt-1 d-block"></small>
                    </td>
                    <td>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0 small">Rp</span>
                            </div>
                            <input type="number" name="items[${idx}][price]" class="form-control form-control-premium price-input amount-input" value="0" required min="0">
                        </div>
                    </td>
                    <td class="text-right">
                        <strong class="text-dark">Rp <span class="row-subtotal">0</span></strong>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-link text-danger remove-item p-0"><i class="fas fa-times-circle fa-lg"></i></button>
                    </td>
                </tr>
            `;
            $('#items-container').append(html);
            initSelect2($(`.select2-item`).last());
            recalc();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            recalc();
        });

        $(document).on('input', '.qty-input, .price-input, .discount-input', recalc);

        $(document).on('change', '.batch-dropdown, #source-select', function() {
            const row = $(this).closest('.item-row');
            if (row.length === 0) return;

            if ($(this).attr('id') === 'source-select') {
                updateAllPrices();
            } else {
                updateRowPrice(row);
            }
        });

        function updateRowPrice(row) {
            const selectedOpt = row.find('.batch-dropdown option:selected');
            const prices = selectedOpt.data('prices');
            const stock = selectedOpt.data('stock');
            const source = $('#source-select').val();

            if (stock !== undefined) {
                row.find('.batch-info').text('Stok: ' + stock);
            }

            if (prices) {
                let price = 0;
                if (source && prices[source]) {
                    price = prices[source];
                } else {
                    const values = Object.values(prices);
                    if (values.length > 0) price = values[0];
                }
                
                const priceInput = row.find('.price-input');
                priceInput.val(price);
                recalc();
            }
        }

        function updateAllPrices() {
            $('.item-row').each(function() {
                updateRowPrice($(this));
            });
        }
    });
</script>
@endpush
