@extends('master')

@section('title', 'Record Online Marketplace Sale')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pasar / Penjualan Online</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Online Sale</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Rekam Penjualan Marketplace</h2>
            <p class="section-lead">Pilih platform, lalu ketik untuk mencari produk dan batch spesifik.</p>

            <form action="{{ route('admin.online_sale.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Pesanan</h4>
                            </div>
                            <div class="card-body">
                                @if(session('message'))
                                    <div class="alert alert-success">{{ session('message') }}</div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <div class="form-group">
                                    <label>Platform Sumber</label>
                                    <select name="source" class="form-control selectric" required>
                                        <option value="">Pilih Platform</option>
                                        <option value="shopee" {{ old('source') == 'shopee' ? 'selected' : '' }}>Shopee</option>
                                        <option value="tokopedia" {{ old('source') == 'tokopedia' ? 'selected' : '' }}>Tokopedia</option>
                                        <option value="tiktok" {{ old('source') == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Nomor Pesanan / Catatan</label>
                                    <textarea name="notes" class="form-control" placeholder="Contoh: No Pesanan platform" style="height: 100px;">{{ old('notes') }}</textarea>
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
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[0][product_batch_id]" class="form-control select2 batch-dropdown" required>
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
                                                    <input type="number" name="items[0][qty]" class="form-control qty-input" required min="1" value="1">
                                                    <small class="batch-info text-info"></small>
                                                    <div class="mt-2 suggested-price-wrapper" style="display:none;">
                                                        <small class="text-muted">Rekomendasi:</small><br>
                                                        <span class="badge badge-light suggested-price-text"></span>
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-success btn-lg" type="submit">Simpan Transaksi</button>
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
    let itemIndex = 1;

    function initSelect2(element) {
        $(element).select2({
            placeholder: 'Ketik nama produk/batch...',
            width: '100%',
            allowClear: true
        });
    }

    // Initialize first row
    $(document).ready(function() {
        initSelect2('.batch-dropdown');
    });

    // Handle batch selection change for info and pricing
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
                row.find('.batch-info').text('Tersedia: ' + stock);
                row.find('.qty-input').attr('max', stock);
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

    // Add new row
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
        itemIndex++;
    });

    // Remove row
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
    });
</script>
@endpush
