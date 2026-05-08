@extends('master')

@section('title', 'Rekam Penjualan Marketplace')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Marketplace</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Marketplace Entry</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <!-- Panel Kiri: Pemilihan Produk -->
                <div class="col-12 col-lg-7">
                    <div class="card card-primary h-100 mb-0">
                        <div class="card-header">
                            <h4>Katalog Produk</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12 col-md-4 mb-2 mb-md-0">
                                    <input type="text" id="search-product" class="form-control" placeholder="Cari produk...">
                                </div>
                                <div class="col-12 col-md-4 mb-2 mb-md-0">
                                    <select id="filter-warehouse" class="form-control selectric">
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <select id="filter-merek" class="form-control selectric">
                                        <option value="">Semua Merk</option>
                                        @foreach($merek as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <hr>

                            <div class="row overflow-auto" id="product-list" style="max-height: 60vh;">
                                <!-- Product Cards Loaded via AJAX -->
                                <div class="col-12 text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Kanan: Keranjang & Transaksi -->
                <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                    <div class="card card-primary h-100 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <h4>Keranjang Belanja</h4>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-outline-warning btn-sm" id="btn-clear-cart">Clear</button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <!-- Cart Table -->
                            <div class="table-responsive mb-3" style="max-height: 35vh; overflow-y: auto;">
                                <table class="table table-sm">
                                    <tbody id="cart-items">
                                        <tr><td class="text-center text-muted py-4">Keranjang kosong</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Payment & Summary -->
                            <div class="summary-box border-top pt-3">
                                <div class="row no-gutters mb-2">
                                    <div class="col-6 text-muted">Subtotal</div>
                                    <div class="col-6 text-right font-weight-bold" id="cart-subtotal">Rp 0</div>
                                </div>

                                <div class="ringkas-info">
                                    <span>Diskon: <strong id="summary-discount" class="text-danger">Rp 0</strong></span>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-discount"><i class="fas fa-plus"></i></button>
                                </div>

                                <!-- TOTAL DISPLAY -->
                                <div class="pos-total-display">
                                    <div class="pos-total-label">Total Harus Bayar</div>
                                    <div class="pos-total-amount" id="cart-total">Rp 0</div>
                                </div>

                                <!-- Payment Settings -->
                                <div class="row mb-3">
                                    <div class="col-6 pr-1">
                                        <select id="marketplace-source" class="form-control selectric">
                                            @foreach($channels as $channel)
                                                <option value="{{ $channel->slug }}">{{ $channel->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <label class="small text-muted font-weight-bold">Status</label>
                                        <select id="payment-status" class="form-control selectric">
                                            <option value="paid">Lunas</option>
                                            <option value="unpaid">Belum Bayar</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="small text-muted font-weight-bold">Tanggal Transaksi</label>
                                        <input type="date" id="transaction-date" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <!-- Invoice Checkbox -->
                                <div class="form-group mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="generate-invoice-check">
                                        <label class="custom-control-label font-weight-bold text-primary" for="generate-invoice-check">Cetak Invoice formal? (Format: INV/...)</label>
                                    </div>
                                </div>

                                <!-- Cash Received Field (Only shown for Cash) -->
                                <div id="online-extra-info" class="mb-3">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted font-weight-bold">No. Pesanan / Resi</label>
                                        <input type="text" id="order-notes" class="form-control" placeholder="Input nomor pesanan...">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted font-weight-bold">Bukti Bayar (Opsional)</label>
                                        <input type="file" id="payment-receipt" class="form-control" accept="image/*,application/pdf">
                                    </div>
                                </div>

                                <!-- Secondary Contexts -->
                                <div class="row no-gutters mb-2">
                                    <div class="col-6 pr-1">
                                        <div class="ringkas-info py-2 px-3 mb-0" style="min-height: 55px;">
                                            <div class="small w-100 overflow-hidden text-truncate">
                                                <div class="text-muted small">Customer</div>
                                                <strong id="summary-customer">Umum</strong>
                                            </div>
                                            <button class="btn btn-sm btn-info ml-2" data-toggle="modal" data-target="#modal-customer-lookup"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <div class="ringkas-info py-2 px-3 mb-0" style="min-height: 55px;">
                                            <div class="small w-100 overflow-hidden text-truncate">
                                                <div class="text-muted small">Referral</div>
                                                <strong id="summary-referral">-</strong>
                                            </div>
                                            <button class="btn btn-sm btn-info ml-2" data-toggle="modal" data-target="#modal-affiliate"><i class="fas fa-user-tag"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Btn -->
                                <button type="button" id="btn-submit-order" class="btn btn-success btn-lg btn-block tablet-btn mt-3 shadow">
                                    PROSES TRANSAKSI
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal 1: Discount / Voucher -->
<div class="modal fade modal-fullscreen-tablet" id="modal-discount" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Diskon & Voucher</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Kode Voucher</label>
                    <div class="input-group">
                        <input type="text" id="voucher-code" class="form-control form-control-lg" placeholder="Masukkan kode voucher">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="btn-apply-voucher">Gunakan</button>
                        </div>
                    </div>
                </div>
                <div class="form-group border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="font-weight-bold text-dark mb-0">Diskon Manual</label>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-primary" id="btn-discount-nominal">Rp</button>
                            <button type="button" class="btn btn-outline-primary" id="btn-discount-persen">%</button>
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="number" id="discount-manual-input" class="form-control form-control-lg" placeholder="0" min="0">
                        <div class="input-group-append">
                            <span class="input-group-text" id="discount-unit-label">Rp</span>
                        </div>
                    </div>
                    <small class="text-muted" id="discount-persen-info" style="display:none;">
                        = Rp <span id="discount-persen-nominal">0</span>
                    </small>
                    <input type="hidden" id="discount-manual" value="0">
                </div>
                <div class="alert alert-light mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Voucher:</span>
                        <strong id="modal-voucher-val" class="text-danger">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Manual:</span>
                        <strong id="modal-manual-val" class="text-danger">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mt-2 h5">
                        <span>Total Diskon:</span>
                        <strong id="modal-total-discount-val">Rp 0</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block tablet-btn" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Customer Search / Quick Add -->
<div class="modal fade modal-fullscreen-tablet" id="modal-customer-lookup" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Informasi Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label class="font-weight-bold text-dark">Search Pelanggan (Cepat)</label>
                    <div class="input-group">
                        <input type="text" id="customer-phone" class="form-control form-control-lg" placeholder="No WA / Nama Pelanggan" autocomplete="new-password" spellcheck="false" autocorrect="off"  autocapitalize="off">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white"><i class="fab fa-whatsapp text-success h5 mb-0"></i></span>
                        </div>
                    </div>
                    <small class="text-muted">Ketik nomor WA atau nama untuk mencari secara otomatis.</small>
                    <div id="wa-suggestions" class="list-group mt-1" style="display:none; position:relative; z-index:999;"></div>
                </div>

                <div class="border-top pt-3">
                    <label class="small text-muted mb-2">Data Pelanggan (Baru/Edit)</label>
                    <input type="hidden" id="customer-id" value="">
                    <div class="form-group mb-2">
                        <input type="text" id="customer-name" class="form-control" placeholder="Nama Lengkap">
                    </div>
                    <div class="form-group mb-2">
                        <input type="email" id="customer-email" class="form-control" placeholder="Email (Opsional)">
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm btn-block mt-2" id="btn-save-customer-ajax">
                        <i class="fas fa-save mr-1"></i> Daftarkan Sebagai Pelanggan Tetap
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block tablet-btn" data-dismiss="modal">Simpan & Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Affiliate / referral -->
<div class="modal fade modal-fullscreen-tablet" id="modal-affiliate" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Referral & Komisi</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Pilih Affiliate / Dokter</label>
                    <select id="affiliate-select" class="form-control form-control-lg">
                        <option value="">-- Tanpa Referral --</option>
                        @foreach($affiliates as $aff)
                            <option value="{{ $aff->id }}">{{ $aff->name }} ({{ $aff->type->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="affiliate-options-panel" style="display:none;">
                    <div class="form-group mt-3">
                        <label class="small text-muted font-weight-bold">Mode Fee</label>
                        <select id="affiliate-mode" class="form-control">
                            <option value="ADD_TO_PRICE">Tambah ke Harga Customer (+)</option>
                            <option value="FROM_MARGIN">Potong dari Margin Toko</option>
                        </select>
                    </div>
                    <div class="alert alert-info mt-3 text-center">
                        <div class="small text-uppercase opacity-70">Estimasi Komisi</div>
                        <div class="h3 mb-0" id="affiliate-fee-display">Rp 0</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block tablet-btn" data-dismiss="modal">Terapkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Item Discount -->
<div class="modal fade modal-fullscreen-tablet" id="modal-item-discount" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Diskon Item</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="item-discount-batch-id">
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Produk</label>
                    <div id="item-discount-product-name" class="h6 text-primary mb-0"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark">Jenis Diskon</label>
                    <select id="item-discount-type" class="form-control selectric">
                        <option value="nominal">Nominal (Rp)</option>
                        <option value="percent">Persentase (%)</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark" id="label-item-discount-value">Nilai Diskon</label>
                    <input type="number" id="item-discount-value" class="form-control form-control-lg" placeholder="0">
                </div>
                <div class="alert alert-light mt-4 border">
                    <div class="d-flex justify-content-between">
                        <span>Total Potongan:</span>
                        <strong id="item-discount-calc-display" class="text-danger">Rp 0</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block tablet-btn" id="btn-save-item-discount">Terapkan Diskon</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center pb-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h3 class="text-success mb-1">Transaksi Sukses!</h3>
                <p class="text-muted">Nomor struk akan dicetak secara otomatis.</p>
                
                <div class="bg-light p-3 rounded mb-4 mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Total Belanja:</span>
                        <strong id="final-total-text">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Kembalian:</span>
                        <strong class="text-success h4 mb-0" id="text-change">Rp 0</strong>
                    </div>
                </div>

                <div class="row no-gutters">
                    <div class="col-6 pr-1">
                        <button type="button" class="btn btn-outline-primary btn-block tablet-btn" id="btn-new-order">Transaksi Baru</button>
                    </div>
                    <div class="col-6 pl-1">
                        <a href="#" target="_blank" class="btn btn-primary btn-block tablet-btn" id="btn-print-receipt"><i class="fas fa-print mr-2"></i> Print Struk</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* POS Redesign Styles */
.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px rgba(0,0,0,0.1);
}
.img-container {
    height: 120px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}
.img-container img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

/* Tablet & Laptop UX Optimization */
.tablet-btn {
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 600;
}

.pos-total-display {
    background: #6777ef;
    color: #fff;
    padding: 20px;
    border-radius: 10px;
    text-align: right;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.pos-total-label {
    font-size: 0.9rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.pos-total-amount {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
}

.cart-item-row td {
    vertical-align: middle !important;
    padding: 10px 5px !important;
}

.ringkas-info {
    font-size: 0.9rem;
    padding: 12px;
    background: #f4f6f9;
    border-radius: 8px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Fullscreen Modal for Tablet */
@media (max-width: 991px) {
    .modal-fullscreen-tablet .modal-dialog {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        max-width: none;
    }
    .modal-fullscreen-tablet .modal-content {
        height: 100%;
        border: 0;
        border-radius: 0;
    }
}
</style>
@endsection

@push('scripts')
<script>
    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
    let batchList = @json($batchList);
    let batchesGrouped = {}; // Group by product+variant
    let affiliates = @json($affiliates);
    let affiliateProductRates = {};
    let voucherDiscount = 0;

    // Group batches by product+variant combination
    batchList.forEach(batch => {
        let key = batch.text.split('(')[0].trim(); // Merek + Product + Variant
        if (!batchesGrouped[key]) {
            batchesGrouped[key] = {
                display_name: key,
                price: batch.price,
                product_id: batch.product_id,
                batches: []
            };
        }
        batchesGrouped[key].batches.push(batch);
    });

    $(document).ready(function() {
        renderProducts();
        renderCart();

        $('#search-product').on('input', renderProducts);
        $('#filter-merek').on('change', renderProducts);
        $('#filter-warehouse').on('change', function() {
            clearCart();
            renderProducts();
        });
        $('#btn-clear-cart').on('click', clearCart);
        $('#btn-new-order').on('click', () => {
            location.reload();
        });

        // Modal: Discount Manual Toggle (Nominal / Persen)
        let discountMode = 'nominal'; // 'nominal' or 'persen'

        $('#btn-discount-nominal').on('click', function() {
            discountMode = 'nominal';
            $(this).removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btn-discount-persen').removeClass('btn-primary').addClass('btn-outline-primary');
            $('#discount-unit-label').text('Rp');
            $('#discount-persen-info').hide();
            $('#discount-manual-input').val('').attr('placeholder', '0').attr('max', '');
            $('#discount-manual').val(0);
            updateTotals();
        });

        $('#btn-discount-persen').on('click', function() {
            discountMode = 'persen';
            $(this).removeClass('btn-outline-primary').addClass('btn-primary');
            $('#btn-discount-nominal').removeClass('btn-primary').addClass('btn-outline-primary');
            $('#discount-unit-label').text('%');
            $('#discount-persen-info').show();
            $('#discount-manual-input').val('').attr('placeholder', '0').attr('max', '100');
            $('#discount-manual').val(0);
            updateTotals();
        });

        $('#discount-manual-input').on('input', function() {
            let val = parseFloat($(this).val()) || 0;
            if (discountMode === 'persen') {
                if (val > 100) { $(this).val(100); val = 100; }
                let subtotal = getSubtotal();
                let nominal = Math.round(subtotal * val / 100);
                $('#discount-persen-nominal').text(nominal.toLocaleString('id-ID'));
                $('#discount-manual').val(nominal);
            } else {
                $('#discount-manual').val(val);
            }
            updateTotals();
        });

        // Reset discount input saat modal dibuka
        $('#modal-discount').on('show.bs.modal', function() {
            let currentNominal = parseFloat($('#discount-manual').val()) || 0;
            if (discountMode === 'nominal') {
                $('#discount-manual-input').val(currentNominal > 0 ? currentNominal : '');
            }
            // Update info persen jika mode persen aktif
            if (discountMode === 'persen') {
                let subtotal = getSubtotal();
                let pct = parseFloat($('#discount-manual-input').val()) || 0;
                let nominal = Math.round(subtotal * pct / 100);
                $('#discount-persen-nominal').text(nominal.toLocaleString('id-ID'));
                $('#discount-manual').val(nominal);
                updateTotals();
            }
        });

        // Modal: Discount Sync
        $('#voucher-code, #discount-manual').on('input', function() {
            updateTotals();
        });
        $('#btn-apply-voucher').on('click', applyVoucher);

        // Modal: Customer Search
        let lookupTimer;
        $('#customer-phone').on('input', function() {
            clearTimeout(lookupTimer);
            let phone = $(this).val().trim();
            let $suggestions = $('#wa-suggestions');

            if (phone.length < 3) {
                $suggestions.hide().empty();
                $('#customer-id').val('');
                $('#summary-customer').text('Umum');
                return;
            }

            lookupTimer = setTimeout(function() {
                let internalReq = $.ajax({
                    url: '{{ route("admin.customers.check") }}',
                    method: 'GET',
                    data: { phone: phone }
                });

                let invitationReq = $.ajax({
                    url: '{{ route("admin.pos.search_invitation") }}',
                    method: 'GET',
                    data: { phone: phone }
                });

                $.when(
                    internalReq.then(function(d) { return d; }, function() { return null; }),
                    invitationReq.then(function(d) { return d; }, function() { return null; })
                ).done(function(internal, invitation) {
                    let suggestions = [];

                    // Internal DB results
                    if (internal && internal.success && internal.data.length > 0) {
                        internal.data.forEach(function(item) {
                            suggestions.push({
                                name: item.name,
                                phone: item.phone || phone,
                                email: item.email || '',
                                id: item.id,
                                source: 'pelanggan'
                            });
                        });
                    }

                    // Invitation API results
                    if (invitation && invitation.status === 'success' && invitation.data.length > 0) {
                        let seen = {};
                        invitation.data.forEach(function(item) {
                            let key = (item.phone || '') + '|' + (item.name || '');
                            if (seen[key]) return;
                            seen[key] = true;
                            let duplicate = suggestions.some(function(s) { return s.phone && s.phone === item.phone; });
                            if (!duplicate) {
                                suggestions.push({
                                    name: item.name,
                                    phone: item.phone || phone,
                                    email: '',
                                    id: null,
                                    source: 'undangan'
                                });
                            }
                        });
                    }

                    $suggestions.empty();
                    if (suggestions.length === 0) {
                        $suggestions.hide();
                        return;
                    }

                    suggestions.forEach(function(s) {
                        let badge = s.source === 'pelanggan'
                            ? '<span class="badge badge-success ml-1">pelanggan</span>'
                            : '<span class="badge badge-warning ml-1">undangan</span>';
                        let $item = $('<a href="#" class="list-group-item list-group-item-action py-2"></a>');
                        $item.html('<i class="fab fa-whatsapp text-success mr-1"></i> <strong>' + s.name + '</strong> <small class="text-muted ml-1">' + s.phone + '</small>' + badge);
                        $item.on('click', function(e) {
                            e.preventDefault();
                            $('#customer-phone').val(s.phone);
                            $('#customer-name').val(s.name);
                            $('#customer-email').val(s.email);
                            $('#customer-id').val(s.id || '');
                            $('#summary-customer').text(s.name);
                            $suggestions.hide().empty();
                            iziToast.info({ title: 'Info', message: 'Customer: ' + s.name, position: 'topRight', timeout: 2000 });
                        });
                        $suggestions.append($item);
                    });
                    $suggestions.show();
                });
            }, 400);
        });

        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#customer-phone, #wa-suggestions').length) {
                $('#wa-suggestions').hide();
            }
        });

        $('#btn-save-customer-ajax').on('click', function() {
            let name = $('#customer-name').val();
            let phone = $('#customer-phone').val();
            if(!name || !phone) {
                swal('Peringatan', 'Nama dan WA wajib diisi!', 'warning');
                return;
            }
            $.ajax({
                url: '{{ route("admin.customers.store_ajax") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', name: name, phone: phone, email: $('#customer-email').val() },
                success: function(res) {
                    if (res.success) {
                        $('#customer-id').val(res.data.id);
                        $('#summary-customer').text(res.data.name);
                        iziToast.success({ title: 'Berhasil', message: 'Customer terdaftar', position: 'topRight' });
                    }
                }
            });
        });

        // Modal: Affiliate Sync
        $('#affiliate-select').on('change', function() {
            let val = $(this).val();
            let text = $("#affiliate-select option:selected").text();
            if (val) {
                $('#affiliate-options-panel').slideDown();
                $('#summary-referral').text(text.split('(')[0]);
                renderCart();
                loadAffiliateRates(val);
            } else {
                $('#affiliate-options-panel').slideUp();
                $('#summary-referral').text('-');
                affiliateProductRates = {};
                renderCart();
            }
        });
        $('#affiliate-mode').on('change', renderCart);

        // Payment: Cash Logic
        $('#payment-method').on('change', function() {
            if ($(this).val() === 'cash') {
                $('#cash-payment-info').slideDown();
            } else {
                $('#cash-payment-info').slideUp();
            }
        });

        $('#cash-received').on('input', calculateChange);

        // Instant quantity controls
        $(document).on('click', '.btn-qty', function() {
            let id = $(this).data('id');
            let action = $(this).data('action');
            updateCartQty(id, action);
        });

        $(document).on('click', '.btn-remove-cart', function() {
            let id = $(this).data('id');
            removeFromCart(id);
        });

        // Submit Order
        $('#btn-submit-order').on('click', function() {
            if (cart.length === 0) {
                swal('Peringatan', 'Keranjang masih kosong!', 'warning');
                return;
            }

            let data = {
                _token: '{{ csrf_token() }}',
                items: cart.map(item => ({
                    product_id: item.product_id,
                    batch_id: item.batch_id,
                    qty: item.qty,
                    discount: item.discount || 0
                })),
                customer_id: $('#customer-id').val(),
                customer_name: $('#customer-name').val(),
                customer_phone: $('#customer-phone').val(),
                source: $('#marketplace-source').val(),
                payment_method: 'transfer', // Default for online
                payment_status: $('#payment-status').val(),
                discount_manual: $('#discount-manual').val(),
                voucher_code: $('#voucher-code').val(),
                affiliate_id: $('#affiliate-select').val(),
                affiliate_fee_mode: $('#affiliate-mode').val(),
                generate_invoice: $('#generate-invoice-check').is(':checked') ? 1 : 0,
                transaction_date: $('#transaction-date').val(),
                warehouse_id: $('#filter-warehouse').val(),
                notes: $('#order-notes').val()
            };

            // Use FormData for file upload
            let formData = new FormData();
            for (let key in data) {
                if (key === 'items') {
                    formData.append(key, JSON.stringify(data[key]));
                } else {
                    formData.append(key, data[key]);
                }
            }
            
            let receiptFile = $('#payment-receipt')[0].files[0];
            if (receiptFile) {
                formData.append('payment_receipt', receiptFile);
            }

            $.ajax({
                url: '{{ $posRoutes["store"] }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() { $.LoadingOverlay("show"); },
                complete: function() { $.LoadingOverlay("hide"); },
                success: function(res) {
                    if (res.success) {
                        localStorage.removeItem('pos_cart');
                        $('#final-total-text').text($('#cart-total').text());
                        
                        let printUrl = '{{ $posRoutes["receipt"] }}/' + res.transaction_id;
                        $('#btn-print-receipt').attr('href', printUrl);

                        // If invoice was generated, add an extra button to print formal invoice
                        if (res.invoice_number) {
                            let invUrl = '{{ route("admin.sales.invoices.print", ":id") }}'.replace(':id', res.transaction_id);
                            $('#btn-print-invoice-formal').remove(); // Clean up old ones
                            $('#btn-print-receipt').after(`
                                <a href="${invUrl}" target="_blank" class="btn btn-info btn-block tablet-btn mt-2" id="btn-print-invoice-formal">
                                    <i class="fas fa-file-invoice mr-2"></i> Print Invoice Formal
                                </a>
                            `);
                        } else {
                            $('#btn-print-invoice-formal').remove();
                        }

                        $('#receiptModal').modal('show');
                    }
                },
                error: function(err) {
                    swal('Error', err.responseJSON?.message || 'Gagal menyimpan transaksi', 'error');
                }
            });
        });
        
        // Item-level discount modal handler
        $(document).on('click', '.btn-item-discount', function() {
            let id = $(this).data('id');
            let item = cart.find(i => i.pseudo_batch_id == id);
            if (item) {
                $('#item-discount-batch-id').val(id);
                $('#item-discount-product-name').text(item.name);
                $('#item-discount-value').val(item.discount_value || 0);
                $('#item-discount-type').val(item.discount_type || 'nominal').trigger('change');
                $('#modal-item-discount').modal('show');
                updateItemDiscountPreview();
            }
        });

        $('#item-discount-value, #item-discount-type').on('input change', function() {
            updateItemDiscountPreview();
        });

        $('#btn-save-item-discount').on('click', function() {
            let id = $('#item-discount-batch-id').val();
            let item = cart.find(i => i.pseudo_batch_id == id);
            if (item) {
                let type = $('#item-discount-type').val();
                let value = parseFloat($('#item-discount-value').val()) || 0;
                let displayPrice = parseFloat(item.price) + getProductMarkup(item.product_id, item.price);
                let totalItemPrice = displayPrice * item.qty;
                
                let actualDiscount = 0;
                if (type === 'percent') {
                    actualDiscount = totalItemPrice * (value / 100);
                } else {
                    actualDiscount = value;
                }

                item.discount = actualDiscount;
                item.discount_type = type;
                item.discount_value = value;
                
                saveCart();
                renderCart();
                $('#modal-item-discount').modal('hide');
                iziToast.success({ message: 'Diskon item diterapkan', position: 'topRight', timeout: 1000 });
            }
        });

        function updateItemDiscountPreview() {
            let id = $('#item-discount-batch-id').val();
            let item = cart.find(i => i.batch_id == id);
            if (!item) return;

            let type = $('#item-discount-type').val();
            let value = parseFloat($('#item-discount-value').val()) || 0;
            let displayPrice = parseFloat(item.price) + getProductMarkup(item.product_id, item.price);
            let totalItemPrice = displayPrice * item.qty;
            let calculated = 0;

            if (type === 'percent') {
                calculated = totalItemPrice * (value / 100);
                $('#label-item-discount-value').text('Nilai Diskon (%)');
            } else {
                calculated = value;
                $('#label-item-discount-value').text('Nilai Diskon (Nominal Rp)');
            }

            $('#item-discount-calc-display').text('Rp ' + calculated.toLocaleString('id-ID'));
        }
    });

    function loadAffiliateRates(affiliateId) {
        $.ajax({
            url: '{{ route("admin.affiliates.rates", ":id") }}'.replace(':id', affiliateId),
            method: 'GET',
            success: function(res) {
                affiliateProductRates = res;
                renderCart();
            }
        });
    }

    function calculateChange() {
        let total = parseInt($('#cart-total').text().replace(/[^0-9]/g, '')) || 0;
        let received = parseInt($('#cash-received').val()) || 0;
        let change = Math.max(0, received - total);
        $('#cash-change').text('Rp ' + change.toLocaleString('id-ID'));
        $('#text-change').text('Rp ' + change.toLocaleString('id-ID'));
    }

    function getProductMarkup(productId, basePrice) {
        let affiliateId = $('#affiliate-select').val();
        let mode = $('#affiliate-mode').val();
        
        if (affiliateId && mode === 'ADD_TO_PRICE') {
            let affiliate = affiliates.find(a => a.id == affiliateId);
            if (!affiliate) return 0;
            let rate = affiliateProductRates[productId] || affiliateProductRates[productId.toString()];
            if (rate) {
                return rate.fee_method === 'percent' ? basePrice * (parseFloat(rate.fee_value) / 100) : parseFloat(rate.fee_value);
            } 
            return affiliate.fee_method === 'percent' ? basePrice * (parseFloat(affiliate.fee_value) / 100) : parseFloat(affiliate.fee_value);
        }
        return 0;
    }

    function renderProducts() {
        let search = $('#search-product').val();
        let mrkId = $('#filter-merek').val();
        let whId = $('#filter-warehouse').val();
        
        let container = $('#product-list');
        container.html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>');
        
        $.ajax({
            url: '{{ $posRoutes["products"] }}',
            method: 'GET',
            data: { search: search, merek_id: mrkId, warehouse_id: whId },
            success: function(variants) {
                container.empty();
                if (variants.length === 0) {
                    container.append('<div class="col-12 text-center py-4">Produk tidak ditemukan atau stok habis di gudang ini.</div>');
                    return;
                }

                // Update batchList with fresh data from server
                variants.forEach(v => {
                    if (v.is_bundle) {
                        let pseudoId = 'bundle-' + v.product_id;
                        let existingBatch = batchList.find(bl => bl.id === pseudoId);
                        if (!existingBatch) {
                            batchList.push({
                                id: pseudoId,
                                text: v.name,
                                price: v.offline_price,
                                stock: v.total_stock,
                                product_id: v.product_id,
                                variant_id: null,
                                batch_no: 'BUNDLE',
                                is_bundle: true
                            });
                        } else {
                            existingBatch.stock = v.total_stock;
                            existingBatch.price = v.offline_price;
                        }
                    } else {
                        v.batches.forEach(b => {
                            let existingBatch = batchList.find(bl => bl.id === b.id);
                            if (!existingBatch) {
                                batchList.push({
                                    id: b.id,
                                    text: v.name,
                                    price: b.selling_price,
                                    stock: b.qty,
                                    product_id: v.product_id,
                                    variant_id: v.variant_id,
                                    batch_no: b.batch_no
                                });
                            } else {
                                existingBatch.stock = b.qty;
                                existingBatch.price = b.selling_price;
                            }
                        });
                    }
                });

                variants.forEach((v) => {
                    let img = v.photo
                        ? v.photo
                        : '{{ asset("assets/img/Asset 3.png") }}';

                    let uniqueId = 'variant-' + v.id;

                    let batchOptions = '';
                    if (v.is_bundle) {
                        batchOptions = `<option value="bundle-${v.product_id}" data-stock="${v.total_stock}" data-price="${v.offline_price}">Paket Bundling (Stok: ${v.total_stock})</option>`;
                    } else {
                        batchOptions = v.batches.map(b =>
                            `<option value="${b.id}" data-stock="${b.qty}" data-price="${b.selling_price}">${b.batch_no} (Stok: ${b.qty})</option>`
                        ).join('');
                    }

                    let firstBatchPrice = v.is_bundle ? v.offline_price : (v.batches.length > 0 ? v.batches[0].selling_price : v.offline_price);

                    let card = `
                        <div class="col-6 col-md-4 mb-3">
                            <div class="card product-card h-100 mb-0 position-relative">
                                <div class="img-container" onclick="addToCartFromVariant('${uniqueId}')">
                                    <img src="${img}" alt="${v.name}">
                                </div>
                                <div class="card-body p-2">
                                    <div class="font-weight-bold" style="font-size: 0.8rem; height: 2.2rem; overflow: hidden;">${v.name}</div>
                                    ${v.netto ? `<div class="text-muted" style="font-size: 0.75rem;">${v.netto}</div>` : ''}
                                    <div class="text-primary product-price mt-1 font-weight-bold small" id="price-display-${uniqueId}">Rp ${parseInt(firstBatchPrice).toLocaleString('id-ID')}</div>
                                    <select class="form-control form-control-sm mt-1 batch-selector" id="${uniqueId}" data-product-id="${v.product_id}" data-variant-id="${v.variant_id || ''}" style="font-size: 0.7rem; height: auto; padding: 2px 5px;" onchange="updatePriceDisplay('${uniqueId}')">
                                        ${batchOptions}
                                    </select>
                                    <button class="btn btn-warning btn-sm btn-block mt-2 py-1" onclick="addToCartFromVariant('${uniqueId}')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.append(card);
                });
            }
        });
    }

    function updatePriceDisplay(uniqueId) {
        let selector = $('#' + uniqueId);
        let selectedOption = selector.find('option:selected');
        let price = selectedOption.data('price');
        if (price) {
            $('#price-display-' + uniqueId).text('Rp ' + parseInt(price).toLocaleString('id-ID'));
        }
    }

    function addToCartFromVariant(uniqueId) {
        let selector = $('#' + uniqueId);
        let batchId = selector.val();
        let productId = selector.data('product-id');
        
        if (!batchId) {
            iziToast.error({ title: 'Error', message: 'Pilih batch!', position: 'topRight' });
            return;
        }

        let batch = batchList.find(b => b.id == batchId);
        if (!batch) return;

        // Use price from the selected option's data-price attribute (variant->price)
        let selectedOption = selector.find('option:selected');
        let sellingPrice = parseFloat(selectedOption.data('price')) || batch.price;

        let existing = cart.find(item => item.pseudo_batch_id == batchId);
        if (existing) {
            if (existing.qty >= batch.stock) {
                iziToast.warning({ message: 'Stok Habis', position: 'topRight' });
                return;
            }
            existing.qty++;
            // Recalculate percentage discount
            if (existing.discount_type === 'percent') {
                let dp = parseFloat(existing.price) + getProductMarkup(existing.product_id, existing.price);
                existing.discount = (dp * existing.qty) * (existing.discount_value / 100);
            }
        } else {
            if (batch.stock <= 0) {
                iziToast.warning({ message: 'Stok Habis', position: 'topRight' });
                return;
            }
            cart.push({
                product_id: productId,
                batch_id: batchId.toString().startsWith('bundle-') ? null : batch.id,
                pseudo_batch_id: batchId, // Keep original for cart index
                name: batch.text.split('(')[0].trim(),
                price: sellingPrice,
                qty: 1,
                stock: batch.stock,
                discount: 0,
                is_bundle: batch.is_bundle || false
            });
        }
        saveCart();
        renderCart();
        iziToast.success({ message: 'Ditambahkan ke keranjang', position: 'topRight', timeout: 500 });
    }

    function renderCart() {
        let container = $('#cart-items');
        container.empty();
        if (cart.length === 0) {
            container.append('<tr><td class="text-center text-muted py-4">Keranjang kosong</td></tr>');
            updateTotals(); return;
        }

        cart.forEach(item => {
            let displayPrice = parseFloat(item.price) + getProductMarkup(item.product_id, item.price);
            let itemSubtotal = (displayPrice * item.qty) - (item.discount || 0);
            container.append(`
                <tr class="cart-item-row">
                    <td style="width: 55%;">
                        <div class="font-weight-bold small">${item.name}</div>
                        <div class="text-muted small">Rp ${displayPrice.toLocaleString('id-ID')} x ${item.qty}</div>
                        ${item.discount > 0 ? `<div class="text-danger small font-weight-bold mt-1"><i class="fas fa-tag mr-1"></i> -Rp ${item.discount.toLocaleString('id-ID')}</div>` : ''}
                    </td>
                    <td class="text-right">
                        <div class="font-weight-bold">Rp ${itemSubtotal.toLocaleString('id-ID')}</div>
                        <div class="btn-group btn-group-sm mt-1">
                            <button class="btn btn-light btn-qty" data-id="${item.pseudo_batch_id}" data-action="minus"><i class="fas fa-minus small"></i></button>
                            <button class="btn btn-light btn-qty font-weight-bold px-2" disabled>${item.qty}</button>
                            <button class="btn btn-light btn-qty" data-id="${item.pseudo_batch_id}" data-action="plus"><i class="fas fa-plus small"></i></button>
                            <button class="btn btn-info btn-item-discount ml-1" data-id="${item.pseudo_batch_id}"><i class="fas fa-tag small"></i></button>
                            <button class="btn btn-light text-danger btn-remove-cart ml-1" data-id="${item.pseudo_batch_id}"><i class="fas fa-trash small"></i></button>
                        </div>
                    </td>
                </tr>
            `);
        });
        updateTotals();
    }

    function updateCartQty(id, action) {
        let item = cart.find(i => i.pseudo_batch_id == id);
        if (!item) return;
        if (action === 'plus') {
            if (item.qty < item.stock) item.qty++; else iziToast.warning({ message: 'Melebihi stok', position: 'topRight' });
        } else {
            item.qty--;
            if (item.qty <= 0) { removeFromCart(id); return; }
        }

        // Recalculate discount if percentage-based
        if (item.discount_type === 'percent') {
            let displayPrice = parseFloat(item.price) + getProductMarkup(item.product_id, item.price);
            item.discount = (displayPrice * item.qty) * (item.discount_value / 100);
        }

        saveCart(); renderCart();
    }

    function removeFromCart(id) { cart = cart.filter(item => item.pseudo_batch_id != id); saveCart(); renderCart(); }
    function clearCart() { cart = []; saveCart(); renderCart(); }
    function saveCart() { localStorage.setItem('pos_cart', JSON.stringify(cart)); }

    function getSubtotal() {
        let subtotal = 0;
        cart.forEach(item => {
            let itemPrice = parseFloat(item.price);
            let itemMarkup = getProductMarkup(item.product_id, itemPrice);
            subtotal += (itemPrice + itemMarkup) * item.qty;
        });
        return subtotal;
    }

    function updateTotals() {
        let discountManual = parseFloat($('#discount-manual').val()) || 0;
        let subtotal = 0;
        let itemDiscountsTotal = 0;
        let affiliateFee = 0;

        cart.forEach(item => {
            let itemPrice = parseFloat(item.price);
            let itemMarkup = getProductMarkup(item.product_id, itemPrice);
            subtotal += (itemPrice + itemMarkup) * item.qty;
            itemDiscountsTotal += parseFloat(item.discount || 0);
            if ($('#affiliate-select').val()) affiliateFee += itemMarkup * item.qty;
        });

        let totalDiscount = discountManual + voucherDiscount + itemDiscountsTotal;
        let total = Math.max(0, subtotal - totalDiscount);
        
        // Update Sidebar View
        $('#cart-subtotal').text('Rp ' + subtotal.toLocaleString('id-ID'));
        $('#summary-discount').text('Rp ' + totalDiscount.toLocaleString('id-ID'));
        $('#cart-total').text('Rp ' + total.toLocaleString('id-ID'));

        // Update Modal Preview
        $('#modal-voucher-val').text('Rp ' + voucherDiscount.toLocaleString('id-ID'));
        $('#modal-manual-val').text('Rp ' + discountManual.toLocaleString('id-ID'));
        $('#modal-total-discount-val').text('Rp ' + totalDiscount.toLocaleString('id-ID'));
        
        // Update Affiliate Preview
        $('#affiliate-fee-display').text('Rp ' + affiliateFee.toLocaleString('id-ID'));
        calculateChange();
    }

    function applyVoucher() {
        let code = $('#voucher-code').val();
        if (!code) return;

        let items = cart.map(item => {
            let displayPrice = parseFloat(item.price) + getProductMarkup(item.product_id, item.price);
            return {
                product_id: item.product_id,
                qty: item.qty,
                subtotal: displayPrice * item.qty
            };
        });

        $.ajax({
            url: '{{ $posRoutes["verify_voucher"] }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', code: code, items: items },
            success: function(res) {
                if (res.success) {
                    voucherDiscount = res.discount;
                    updateTotals();
                    iziToast.success({ title: 'Voucher Applied', message: 'Diskon Rp ' + res.discount.toLocaleString('id-ID'), position: 'topRight' });
                } else {
                    voucherDiscount = 0; updateTotals();
                    swal('Gagal', res.message, 'error');
                }
            }
        });
    }
</script>
@endpush
