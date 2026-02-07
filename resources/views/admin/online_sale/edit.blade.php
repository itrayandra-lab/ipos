@extends('master')

@section('title', 'Edit Online Marketplace Sale')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pasar / Edit Penjualan Online</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.online_sale.index') }}">Online Sale</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.online_sale.history') }}">Riwayat</a></div>
                <div class="breadcrumb-item active">Edit</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Edit Rekam Penjualan Marketplace</h2>
            <p class="section-lead">Lakukan perubahan pada data transaksi. Stok akan otomatis disesuaikan.</p>

            <form action="{{ route('admin.online_sale.update', $transaction->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Pesanan</h4>
                            </div>
                            <div class="card-body">
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <div class="form-group">
                                    <label>Platform Sumber</label>
                                    <select name="source" class="form-control selectric" required>
                                        <option value="">Pilih Platform</option>
                                        @foreach($channels as $channel)
                                            <option value="{{ $channel->slug }}" {{ $transaction->source == $channel->slug ? 'selected' : '' }}>{{ $channel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Tanggal Transaksi</label>
                                    <input type="datetime-local" name="transaction_date" class="form-control" value="{{ $transaction->created_at->format('Y-m-d\TH:i') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Nomor Pesanan / Catatan</label>
                                    <textarea name="notes" class="form-control" placeholder="Contoh: No Pesanan platform" style="height: 100px;">{{ $transaction->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Daftar Produk</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-primary" id="add-item">
                                        <i class="fas fa-plus"></i> Tambah Baris
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md">
                                        <thead>
                                            <tr>
                                                <th width="70%">Produk & Batch (Cari di sini)</th>
                                                <th width="20%">Qty</th>
                                                <th width="10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="items-container">
                                            @foreach($transaction->items as $index => $item)
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[{{ $index }}][product_batch_id]" class="form-control select2 batch-dropdown" required>
                                                        <option value="">Ketik nama produk atau batch...</option>
                                                        @foreach($batchList as $batch)
                                                            <option value="{{ $batch->id }}" 
                                                                    data-stock="{{ $batch->stock }}"
                                                                    data-prices="{{ json_encode($batch->prices) }}"
                                                                    {{ $item->product_batch_id == $batch->id ? 'selected' : '' }}>
                                                                {{ $batch->text }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][qty]" class="form-control qty-input" required min="1" value="{{ $item->qty }}">
                                                    <small class="batch-info text-info"></small>
                                                    <div class="mt-2 suggested-price-wrapper" style="display:none;">
                                                        <small class="text-muted">Rekomendasi:</small><br>
                                                        <span class="badge badge-light suggested-price-text"></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($index > 0)
                                                    <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('admin.online_sale.history') }}" class="btn btn-secondary btn-lg mr-2">Batal</a>
                                <button class="btn btn-success btn-lg" type="submit">Update Transaksi</button>
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
    let itemIndex = {{ $transaction->items->count() }};

    function initSelect2(element) {
        $(element).select2({
            placeholder: 'Ketik nama produk/batch...',
            width: '100%',
            allowClear: true
        });
    }

    $(document).ready(function() {
        initSelect2('.batch-dropdown');
        updatePriceSuggestions();
    });

    $(document).on('change', '.batch-dropdown, select[name="source"]', function() {
        updatePriceSuggestions();
    });

    function updatePriceSuggestions() {
        let source = $('select[name="source"]').val();
        
        $('.item-row').each(function() {
            let row = $(this);
            let selectedOption = row.find('.batch-dropdown option:selected');
            let stock = selectedOption.data('stock');
            let prices = selectedOption.data('prices');

            if(stock !== undefined) {
                row.find('.batch-info').text('Stok Global: ' + stock);
            } else {
                row.find('.batch-info').text('');
            }

            if(source && prices && prices[source]) {
                row.find('.suggested-price-text').text('Rp ' + prices[source].toLocaleString('id-ID'));
                row.find('.suggested-price-wrapper').show();
            } else {
                row.find('.suggested-price-wrapper').hide();
            }
        });
    }

    $('#add-item').click(function() {
        let html = `
            <tr class="item-row">
                <td>
                    <select name="items[${itemIndex}][product_batch_id]" class="form-control batch-dropdown" required>
                        <option value="">Ketik nama produk atau batch...</option>
                        @foreach($batchList as $batch)
                            <option value="{{ $batch->id }}" 
                                    data-stock="{{ $batch->stock }}"
                                    data-prices="{{ json_encode($batch->prices) }}">
                                {{ $batch->text }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${itemIndex}][qty]" class="form-control qty-input" required min="1" value="1">
                    <small class="batch-info text-info"></small>
                    <div class="mt-2 suggested-price-wrapper" style="display:none;">
                        <small class="text-muted">Rekomendasi:</small><br>
                        <span class="badge badge-light suggested-price-text"></span>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        
        let $newRow = $(html);
        $('#items-container').append($newRow);
        initSelect2($newRow.find('.batch-dropdown'));
        updatePriceSuggestions();
        itemIndex++;
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
    });
</script>
@endpush
