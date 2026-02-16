@extends('master')

@section('title', 'Tampilan Kasir (POS)')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Kasir</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Kasir</div>
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
                                <div class="col-12 col-md-6 mb-2 mb-md-0">
                                    <input type="text" id="search-product" class="form-control" placeholder="Cari produk...">
                                </div>
                                <div class="col-12 col-md-6">
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
                                        <label class="small text-muted font-weight-bold">Metode Bayar</label>
                                        <select id="payment-method" class="form-control selectric">
                                            <option value="cash">💵 Cash</option>
                                            <option value="qris">📱 QR / QRIS</option>
                                            <option value="transfer">🏦 Transfer</option>
                                            <option value="debit">💳 Debit/EDC</option>
                                        </select>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <label class="small text-muted font-weight-bold">Status</label>
                                        <select id="payment-status" class="form-control selectric">
                                            <option value="paid">Lunas</option>
                                            <option value="unpaid">Belum Bayar</option>
                                            <option value="draft">Pending</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Cash Received Field (Only shown for Cash) -->
                                <div id="cash-payment-info" class="mb-3">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted font-weight-bold">Uang Diterima</label>
                                        <input type="number" id="cash-received" class="form-control form-control-lg" placeholder="0">
                                    </div>
                                    <div class="row no-gutters mt-2">
                                        <div class="col-6 text-muted">Kembalian</div>
                                        <div class="col-6 text-right font-weight-bold text-success h5" id="cash-change">Rp 0</div>
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
                    <label class="font-weight-bold text-dark">Diskon Manual (Nominal Rp)</label>
                    <input type="number" id="discount-manual" class="form-control form-control-lg" placeholder="0">
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
                    <label class="font-weight-bold text-dark">Search WA Pelanggan (Cepat)</label>
                    <div class="input-group">
                        <input type="text" id="customer-phone" class="form-control form-control-lg" placeholder="08xxxxxxxx">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white"><i class="fab fa-whatsapp text-success h5 mb-0"></i></span>
                        </div>
                    </div>
                    <small class="text-muted">Ketik nomor WA untuk mencari secara otomatis.</small>
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
    let products = [];
    let affiliates = @json($affiliates);
    let affiliateProductRates = {};
    let voucherDiscount = 0;

    $(document).ready(function() {
        loadProducts();
        renderCart();

        $('#search-product').on('input', loadProducts);
        $('#filter-merek').on('change', loadProducts);
        $('#btn-clear-cart').on('click', clearCart);
        $('#btn-new-order').on('click', () => {
            location.reload();
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
            let phone = $(this).val();
            if (phone.length < 8) {
                $('#customer-id').val('');
                $('#summary-customer').text('Umum');
                return;
            }

            lookupTimer = setTimeout(function() {
                $.ajax({
                    url: '{{ route("admin.customers.check") }}',
                    method: 'GET',
                    data: { phone: phone },
                    success: function(res) {
                        if (res.success) {
                            $('#customer-id').val(res.data.id);
                            $('#customer-name').val(res.data.name);
                            $('#customer-email').val(res.data.email);
                            $('#summary-customer').text(res.data.name);
                            iziToast.info({ title: 'Info', message: 'Customer: ' + res.data.name, position: 'topRight', timeout: 2000 });
                        } else {
                            $('#customer-id').val('');
                            $('#summary-customer').text('Umum');
                        }
                    }
                });
            }, 500);
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
                    qty: item.qty
                })),
                customer_id: $('#customer-id').val(),
                customer_name: $('#customer-name').val(),
                customer_phone: $('#customer-phone').val(),
                payment_method: $('#payment-method').val(),
                payment_status: $('#payment-status').val(),
                discount_manual: $('#discount-manual').val(),
                voucher_code: $('#voucher-code').val(),
                affiliate_id: $('#affiliate-select').val(),
                affiliate_fee_mode: $('#affiliate-mode').val(),
                notes: '-' // Default notes or pull from a field if added
            };

            $.ajax({
                url: '{{ $posRoutes["store"] }}',
                method: 'POST',
                data: data,
                beforeSend: function() { $.LoadingOverlay("show"); },
                complete: function() { $.LoadingOverlay("hide"); },
                success: function(res) {
                    if (res.success) {
                        localStorage.removeItem('pos_cart');
                        $('#final-total-text').text($('#cart-total').text());
                        $('#btn-print-receipt').attr('href', '{{ $posRoutes["receipt"] }}/' + res.transaction_id);
                        $('#receiptModal').modal('show');
                    }
                },
                error: function(err) {
                    swal('Error', err.responseJSON?.message || 'Gagal menyimpan transaksi', 'error');
                }
            });
        });
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

    function loadProducts() {
        let search = $('#search-product').val();
        let mrkId = $('#filter-merek').val();
        $.ajax({
            url: '{{ $posRoutes["products"] }}',
            data: { search: search, merek_id: mrkId },
            success: function(res) {
                products = res;
                renderProducts();
            }
        });
    }

    function renderProducts() {
        let container = $('#product-list');
        container.empty();
        if (products.length === 0) {
            container.append('<div class="col-12 text-center py-4">Produk tidak ditemukan</div>');
            return;
        }

        products.forEach(p => {
            let img = p.photos.length > 0 ? '{{ asset("") }}' + p.photos[0].foto : '{{ asset("assets/img/Asset 3.png") }}';
            let batchOptions = p.batches.map(b => `<option value="${b.id}" data-stock="${b.qty}">Batch: ${b.batch_no} (Sisa: ${b.qty})</option>`).join('');

            let card = `
                <div class="col-6 col-md-4 mb-3">
                    <div class="card product-card h-100 mb-0 position-relative">
                        <div class="img-container" onclick="addToCart(${p.id})">
                            <img src="${img}" alt="${p.name}">
                        </div>
                        <div class="card-body p-2">
                            <div class="font-weight-bold" style="font-size: 0.8rem; height: 2.2rem; overflow: hidden;">${p.name}</div>
                            <div class="text-primary product-price mt-1 font-weight-bold small">Rp ${parseInt(p.offline_price).toLocaleString('id-ID')}</div>
                            <select class="form-control form-control-sm mt-1 batch-selector" id="batch-for-${p.id}" style="font-size: 0.7rem; height: auto; padding: 2px 5px;">
                                ${batchOptions}
                            </select>
                            <button class="btn btn-warning btn-sm btn-block mt-2 py-1" onclick="addToCart(${p.id})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    function addToCart(productId) {
        let product = products.find(p => p.id == productId);
        if (!product) return;
        let batchId = $('#batch-for-' + productId).val();
        let batch = product.batches.find(b => b.id == batchId);
        
        if (!batch) {
            iziToast.error({ title: 'Error', message: 'Pilih batch!', position: 'topRight' });
            return;
        }

        let existing = cart.find(item => item.batch_id == batchId);
        if (existing) {
            if (existing.qty >= batch.qty) {
                iziToast.warning({ message: 'Stok Habis', position: 'topRight' });
                return;
            }
            existing.qty++;
        } else {
            if (batch.qty <= 0) {
                iziToast.warning({ message: 'Stok Habis', position: 'topRight' });
                return;
            }
            cart.push({
                product_id: product.id, batch_id: batch.id, name: product.name,
                price: product.offline_price, qty: 1, stock: batch.qty
            });
        }
        saveCart(); renderCart();
        iziToast.success({ message: product.name + ' ditambahkan', position: 'topRight', timeout: 500 });
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
            container.append(`
                <tr class="cart-item-row">
                    <td>
                        <div class="font-weight-bold small">${item.name}</div>
                        <div class="text-muted small">Rp ${displayPrice.toLocaleString('id-ID')} x ${item.qty}</div>
                    </td>
                    <td class="text-right">
                        <div class="font-weight-bold">Rp ${(displayPrice * item.qty).toLocaleString('id-ID')}</div>
                        <div class="btn-group btn-group-sm mt-1">
                            <button class="btn btn-light btn-qty" data-id="${item.batch_id}" data-action="minus"><i class="fas fa-minus small"></i></button>
                            <button class="btn btn-light btn-qty font-weight-bold px-2" disabled>${item.qty}</button>
                            <button class="btn btn-light btn-qty" data-id="${item.batch_id}" data-action="plus"><i class="fas fa-plus small"></i></button>
                            <button class="btn btn-light text-danger btn-remove-cart ml-1" data-id="${item.batch_id}"><i class="fas fa-trash small"></i></button>
                        </div>
                    </td>
                </tr>
            `);
        });
        updateTotals();
    }

    function updateCartQty(id, action) {
        let item = cart.find(i => i.batch_id == id);
        if (!item) return;
        if (action === 'plus') {
            if (item.qty < item.stock) item.qty++; else iziToast.warning({ message: 'Melebihi stok', position: 'topRight' });
        } else {
            item.qty--;
            if (item.qty <= 0) { removeFromCart(id); return; }
        }
        saveCart(); renderCart();
    }

    function removeFromCart(id) { cart = cart.filter(item => item.batch_id != id); saveCart(); renderCart(); }
    function clearCart() { cart = []; saveCart(); renderCart(); }
    function saveCart() { localStorage.setItem('pos_cart', JSON.stringify(cart)); }

    function updateTotals() {
        let discountManual = parseFloat($('#discount-manual').val()) || 0;
        let subtotal = 0;
        let affiliateFee = 0;

        cart.forEach(item => {
            let itemPrice = parseFloat(item.price);
            let itemMarkup = getProductMarkup(item.product_id, itemPrice);
            subtotal += (itemPrice + itemMarkup) * item.qty;
            if ($('#affiliate-select').val()) affiliateFee += itemMarkup * item.qty;
        });

        let totalDiscount = discountManual + voucherDiscount;
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
