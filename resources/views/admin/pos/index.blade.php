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
                                    <select id="filter-category" class="form-control selectric">
                                        <option value="">Semua Merk</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                    <div class="card card-success h-100 sticky-top" style="top: 0px;">
                        <div class="card-header">
                            <h4>Keranjang Belanja</h4>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-warning btn-sm" id="btn-clear-cart">Clear</button>
                            </div>
                        </div>
                        <div class="card-body p-0 overflow-auto" style="max-height: 40vh;">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th width="100px">Qty</th>
                                            <th>Total</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-items">
                                        <!-- Cart Items Loaded via JS -->
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Keranjang kosong</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer border-top bg-light">
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Subtotal</div>
                                <div class="col-6 text-right font-weight-bold" id="cart-subtotal">Rp 0</div>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-1">Diskon (Voucher/Manual)</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="voucher-code" class="form-control" placeholder="Kode Voucher">
                                    <input type="number" id="discount-manual" class="form-control" placeholder="Nominal Rp">
                                </div>
                            </div>

                            <div class="row mb-3 border-top pt-2">
                                <div class="col-6 h5">Total Akhir</div>
                                <div class="col-6 text-right h5 text-primary" id="cart-total">Rp 0</div>
                            </div>

                            <hr>

                            <!-- Buyer Info -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-2">
                                        <input type="text" id="customer-name" class="form-control form-control-sm" placeholder="Nama Pelanggan">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 pl-md-3">
                                    <div class="form-group mb-2">
                                        <input type="text" id="customer-phone" class="form-control form-control-sm" placeholder="WhatsApp (08...)">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 pr-md-3">
                                    <div class="form-group mb-2">
                                        <input type="email" id="customer-email" class="form-control form-control-sm" placeholder="Email (Opsional)">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-6">
                                    <label class="small text-muted">Metode Bayar</label>
                                    <select id="payment-method" class="form-control form-control-sm">
                                        <option value="cash">Cash</option>
                                        <option value="qris">QR / QRIS</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="debit">Debit (EDC BCA)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted">Status</label>
                                    <select id="payment-status" class="form-control form-control-sm">
                                        <option value="paid">Paid (Lunas)</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="draft">Draft/Hold</option>
                                        <option value="canceled">Canceled</option>
                                    </select>
                                </div>
                            </div>

                            <button type="button" id="btn-submit-order" class="btn btn-success btn-lg btn-block mt-4">
                                <i class="fas fa-check-circle mr-2"></i> Proses Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Success Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">Transaksi Berhasil!</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <p class="mt-3 lead">Pesanan telah disimpan dengan status <strong id="modal-status-text">Paid</strong></p>
                <div class="alert alert-info" id="alert-change" style="display:none;">
                    Kembalian: <span class="h4" id="text-change">Rp 0</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btn-new-order">Transaksi Baru</button>
                <a href="#" target="_blank" class="btn btn-primary" id="btn-print-receipt">Print Struk</a>
            </div>
        </div>
    </div>
</div>

<style>
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
.product-card .add-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(103, 119, 239, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
    border-radius: 10px;
}
.product-card:hover .add-overlay {
    opacity: 1;
}
.img-container {
    height: 150px;
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
.price-tag {
    font-size: 1.1rem;
    font-weight: 700;
    color: #6777ef;
}
</style>
@endsection

@push('scripts')
<script>
    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
    let products = [];

    $(document).ready(function() {
        loadProducts();
        renderCart();

        $('#search-product').on('input', loadProducts);
        $('#filter-category').on('change', loadProducts);
        $('#btn-clear-cart').on('click', clearCart);
        $('#btn-new-order').on('click', () => {
            location.reload();
        });

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

        $('#discount-manual, #voucher-code').on('input', updateTotals);

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
                customer_name: $('#customer-name').val(),
                customer_phone: $('#customer-phone').val(),
                customer_email: $('#customer-email').val(),
                payment_method: $('#payment-method').val(),
                payment_status: $('#payment-status').val(),
                discount_manual: $('#discount-manual').val(),
                voucher_code: $('#voucher-code').val(),
                notes: ''
            };

            $.ajax({
                url: '{{ $posRoutes["store"] }}',
                method: 'POST',
                data: data,
                beforeSend: function() {
                    $.LoadingOverlay("show");
                },
                complete: function() {
                    $.LoadingOverlay("hide");
                },
                success: function(res) {
                    if (res.success) {
                        localStorage.removeItem('pos_cart');
                        $('#modal-status-text').text($('#payment-status option:selected').text());
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

    function loadProducts() {
        let search = $('#search-product').val();
        let catId = $('#filter-category').val();

        $.ajax({
            url: '{{ $posRoutes["products"] }}',
            data: { search: search, category_id: catId },
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
            
            // Generate batch options for the card
            let batchOptions = p.batches.map(b => `<option value="${b.id}" data-stock="${b.qty}">Batch: ${b.batch_no} (Sisa: ${b.qty})</option>`).join('');

            let card = `
                <div class="col-6 col-md-4 mb-3">
                    <div class="card product-card h-100 mb-0 position-relative">
                        <div class="img-container">
                            <img src="${img}" alt="${p.name}">
                        </div>
                        <div class="card-body p-2">
                            <small class="text-muted d-block">${p.category ? p.category.name : '-'}</small>
                            <div class="font-weight-bold" style="font-size: 0.85rem; height: 2.5rem; overflow: hidden;">${p.name}</div>
                            <div class="text-primary product-price mt-1 font-weight-bold">Rp ${p.offline_price.toLocaleString('id-ID')}</div>
                            
                            <select class="form-control form-control-sm mt-2 batch-selector" id="batch-for-${p.id}">
                                ${batchOptions}
                            </select>
                            
                            <button class="btn btn-warning btn-sm btn-block mt-2 shadow" onclick="addToCart(${p.id})">
                                <i class="fas fa-plus mr-1"></i> Add
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

        let batchSelector = $('#batch-for-' + productId);
        let selectedBatchId = batchSelector.val();
        let selectedBatch = product.batches.find(b => b.id == selectedBatchId);
        
        if (!selectedBatch) {
            iziToast.error({ title: 'Error', message: 'Harap pilih batch produk!', position: 'topRight' });
            return;
        }

        let existing = cart.find(item => item.batch_id == selectedBatchId);
        if (existing) {
            if (existing.qty >= selectedBatch.qty) {
                iziToast.warning({ title: 'Stok Habis', message: 'Jumlah melebihi stok batch ini.', position: 'topRight' });
                return;
            }
            existing.qty++;
        } else {
            if (selectedBatch.qty <= 0) {
                iziToast.warning({ title: 'Stok Habis', message: 'Batch ini tidak memiliki stok.', position: 'topRight' });
                return;
            }
            cart.push({
                product_id: product.id,
                batch_id: selectedBatch.id,
                name: product.name,
                batch_no: selectedBatch.batch_no,
                price: product.offline_price,
                qty: 1,
                stock: selectedBatch.qty
            });
        }
        
        saveCart();
        renderCart();
        iziToast.success({ message: product.name + ' (' + selectedBatch.batch_no + ') ditambahkan', position: 'topRight', timeout: 800 });
    }

    function renderCart() {
        let container = $('#cart-items');
        container.empty();

        if (cart.length === 0) {
            container.append('<tr><td colspan="4" class="text-center text-muted py-4">Keranjang kosong</td></tr>');
            updateTotals();
            return;
        }

        cart.forEach(item => {
            let row = `
                <tr>
                    <td>
                        <div class="font-weight-bold" style="font-size: 0.85rem;">${item.name}</div>
                        <div class="badge badge-light" style="font-size: 0.7rem;">${item.batch_no || 'No Batch'}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Rp ${item.price.toLocaleString('id-ID')}</div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm" style="width: 100px;">
                            <div class="input-group-prepend">
                                <button class="btn btn-outline-secondary btn-qty p-1" data-id="${item.batch_id}" data-action="minus"><i class="fas fa-minus" style="font-size: 0.6rem;"></i></button>
                            </div>
                            <input type="text" class="form-control text-center p-0" value="${item.qty}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-qty p-1" data-id="${item.batch_id}" data-action="plus"><i class="fas fa-plus" style="font-size: 0.6rem;"></i></button>
                            </div>
                        </div>
                    </td>
                    <td class="font-weight-bold">Rp ${(item.price * item.qty).toLocaleString('id-ID')}</td>
                    <td><button class="btn btn-link text-danger p-0 btn-remove-cart" data-id="${item.batch_id}"><i class="fas fa-times"></i></button></td>
                </tr>
            `;
            container.append(row);
        });

        updateTotals();
    }

    function updateCartQty(id, action) {
        let item = cart.find(i => i.batch_id == id);
        if (!item) return;

        if (action === 'plus') {
            if (item.qty >= item.stock) {
                 iziToast.warning({ message: 'Melebihi stok batch', position: 'topRight' });
                 return;
            }
            item.qty++;
        } else {
            item.qty--;
            if (item.qty <= 0) {
                removeFromCart(id);
                return;
            }
        }
        saveCart();
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.batch_id != id);
        saveCart();
        renderCart();
    }

    function clearCart() {
        cart = [];
        saveCart();
        renderCart();
    }

    function saveCart() {
        localStorage.setItem('pos_cart', JSON.stringify(cart));
    }

    function updateTotals() {
        let subtotal = cart.reduce((total, item) => total + (item.price * item.qty), 0);
        let discount = parseFloat($('#discount-manual').val()) || 0;
        
        $('#cart-subtotal').text('Rp ' + subtotal.toLocaleString('id-ID'));
        $('#cart-total').text('Rp ' + (subtotal - discount).toLocaleString('id-ID'));
    }
</script>
@endpush
