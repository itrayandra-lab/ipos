@extends('guest')

@section('title', 'Beranda')

@section('content')
    <div class="relative">
        <main class="mx-auto max-w-8xl px-4 sm:px-6 lg:px-8">
            @include('guest.components.header')

            <section aria-labelledby="products-heading" class="pt-6 pb-24">
                <h2 id="products-heading" class="sr-only">Produk</h2>
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-4 lg:items-start">
                    @include('guest.components.sidebar_categories')

                    <div class="lg:col-span-3">
                        <section class="pt-6 pb-24">
                            <div class="grid grid-cols-12 gap-6">
                                <div class="col-span-12 xl:col-span-8">
                                    <div class="flex items-center justify-between pb-8 border-b border-gray-300">
                                        <h2 class="font-bold text-3xl text-black">Keranjang Belanja</h2>
                                        <h2 id="cart-items-count" class="font-bold text-xl text-gray-600">0 Item</h2>
                                    </div>

                                    <div id="cart-products" class="mt-6"></div>
                                </div>

                                <div class="col-span-12 xl:col-span-4 bg-gray-50 p-6 rounded-lg">
                                    <h2 class="font-bold text-2xl text-black pb-4 border-b">Ringkasan Pesanan</h2>

                                    <form id="checkout-form" class="mt-6">
                                        @csrf
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">Metode Pengiriman</h3>
                                            <div class="mt-3">
                                                <label class="flex items-center mb-2">
                                                    <input id="pickup-radio" type="radio" value="pickup" name="delivery_type" class="w-4 h-4 text-indigo-600" checked>
                                                    <span class="ml-2 text-sm">Pickup</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input id="delivery-radio" type="radio" value="delivery" name="delivery_type" class="w-4 h-4 text-indigo-600">
                                                    <span class="ml-2 text-sm">Delivery</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div id="delivery-notes" class="hidden mt-4">
                                            <label for="delivery_desc" class="block text-sm font-medium text-gray-700">Catatan Pengiriman</label>
                                            <textarea name="delivery_desc" id="delivery_desc" rows="3" class="mt-1 w-full border rounded-lg p-2 text-sm" placeholder="Isi dengan nama, alamat dan lainnya"></textarea>
                                        </div>

                                        <div class="relative mt-4 border border-1 rounded-full">
                                            <label for="voucher-search" class="mb-2 text-sm font-medium text-gray-900 sr-only">Cari Voucher</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                                    <i class="fa fa-ticket-alt text-gray-500"></i>
                                                </div>
                                                <input type="search" name="voucher" placeholder="Cari voucher..." id="voucher-search" class="block w-full p-4 ps-10 text-sm text-gray-900 rounded-full" />
                                                <button type="button" id="check-voucher" class="text-white absolute end-2.5 bottom-2.5 bg-indigo-700 hover:bg-blue-800 font-medium rounded-full text-sm px-4 py-2">Cari</button>
                                            </div>
                                        </div>

                                        <div id="voucher-result" class="mt-3"></div>

                                        <div class="py-6 border-t mt-6">
                                            <div class="flex items-center justify-between">
                                                <p id="cart-summary-count" class="font-medium text-lg">0 Item</p>
                                                <p id="cart-subtotal" class="font-semibold text-lg text-gray-600">Rp. 0</p>
                                            </div>
                                            <div id="discount-info" class="hidden flex items-center justify-between mt-2">
                                                <p class="text-sm text-gray-600">Diskon</p>
                                                <p id="discount-amount" class="text-sm text-green-600"></p>
                                            </div>
                                            <div class="flex items-center justify-between mt-2">
                                                <p class="font-medium text-lg">Total</p>
                                                <p id="cart-summary-total" class="font-semibold text-xl text-indigo-600">Rp. 0</p>
                                            </div>
                                        </div>

                                        @auth
                                            <button type="submit" class="w-full text-center bg-indigo-600 rounded-xl py-3 px-6 font-semibold text-lg text-white transition hover:bg-indigo-700">
                                                Bayar Sekarang
                                            </button>
                                        @else
                                        <a href="{{ route('login') }}" class="w-full text-center ml-auto bg-indigo-600 rounded-xl py-3 px-6 font-semibold text-lg text-white transition hover:bg-indigo-700">
                                            Login Terlebih Dahulu
                                        </a>
                                        @endauth
                                    </form>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ config('midtrans.url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const cartContainer = document.getElementById("cart-products");
            const cartCount = document.getElementById("cart-items-count");
            const summaryCount = document.getElementById("cart-summary-count");
            const summaryTotal = document.getElementById("cart-summary-total");
            const subtotalElement = document.getElementById("cart-subtotal");
            const discountInfo = document.getElementById("discount-info");
            const discountAmount = document.getElementById("discount-amount");
            const deliveryNotes = document.getElementById("delivery-notes");
            const checkoutForm = document.getElementById("checkout-form");
            const voucherSearch = document.getElementById("voucher-search");
            const checkVoucherButton = document.getElementById("check-voucher");
            const voucherResult = document.getElementById("voucher-result");
            const loadingOverlay = document.getElementById('loading-overlay');

            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            let currentDiscount = 0;
            let voucherCode = null;

            if (cart.length === 0) {
                cartContainer.innerHTML = `<p class="text-gray-500">Keranjang masih kosong</p>`;
                return;
            }

            fetch("{{ route('cart.fetch') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    ids: cart
                })
            })
            .then(res => res.json())
            .then(products => {
                renderProducts(products);
            });

            function formatCurrency(amount) {
                return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
            }

            function renderProducts(products) {
                let totalItems = 0;
                let totalHarga = 0;
                cartContainer.innerHTML = "";

                products.forEach(product => {
                    let qty = 1;
                    totalItems += qty;
                    totalHarga += product.price * qty;

                    let priceDisplay = '';
                    if (product.price_real && product.price_real > product.price) {
                        let discountPercent = Math.round(((product.price_real - product.price) / product.price_real) * 100);
                        let discountInfo = '';
                        if (product.vouchers && product.vouchers.length > 0) {
                            let activeVoucher = product.vouchers.find(v => v.status === 'ACTIVE');
                            if (activeVoucher) {
                                discountInfo = activeVoucher.name;
                            }
                        }
                        priceDisplay = `
                            <p class="text-sm text-gray-500 line-through mb-1">Rp. ${parseInt(product.price_real).toLocaleString()}</p>
                            <p class="text-base font-semibold text-gray-900 mb-1">Rp. ${parseInt(product.price).toLocaleString()}</p>
                            <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full mb-1">${discountPercent}% OFF</span>
                            ${discountInfo ? `<small class="text-gray-600 block">${discountInfo}</small>` : ''}
                        `;
                    } else {
                        priceDisplay = `<p class="text-base font-semibold text-gray-900">Rp. ${parseInt(product.price).toLocaleString()}</p>`;
                    }

                    let item = document.createElement("div");
                    item.className = "flex flex-col md:flex-row items-center gap-5 py-6 border-b";

                    item.innerHTML = `
                        <div class="w-28">
                            <img src="${product.photos?.[0]?.foto ? '/' + product.photos[0].foto : '/assets/img/human.png'}" class="rounded-xl object-cover">
                        </div>
                        <div class="flex-1">
                            <h6 class="font-semibold">${product.name}</h6>
                            <div class="text-left">${priceDisplay}</div>
                        </div>
                        <div class="flex items-center">
                            <button class="decrease bg-gray-200 px-3 py-1 rounded-l" data-id="${product.id}">-</button>
                            <input type="number" value="1" min="1" class="qty-input w-12 h-8 text-center border-y" data-id="${product.id}" data-price="${product.price}">
                            <button class="increase bg-gray-200 px-3 py-1 rounded-r" data-id="${product.id}">+</button>
                        </div>
                        <div class="w-28 text-right font-semibold subtotal" data-id="${product.id}">
                            ${formatCurrency(product.price)}
                        </div>
                        <button class="ml-4 text-red-600 remove" data-id="${product.id}"><i class="fa-solid fa-trash" style="color: #d00b0b;"></i></button>
                    `;
                    cartContainer.appendChild(item);
                });

                updateSummary();

                document.querySelectorAll(".increase").forEach(btn => {
                    btn.addEventListener("click", () => updateQty(btn.dataset.id, 1));
                });
                document.querySelectorAll(".decrease").forEach(btn => {
                    btn.addEventListener("click", () => updateQty(btn.dataset.id, -1));
                });
                document.querySelectorAll(".remove").forEach(btn => {
                    btn.addEventListener("click", () => removeItem(btn.dataset.id));
                });

                document.querySelectorAll(".qty-input").forEach(input => {
                    input.addEventListener("input", () => {
                        let qty = parseInt(input.value);
                        if (isNaN(qty) || qty < 1) {
                            qty = 1;
                            input.value = qty;
                        }
                        updateQtyManual(input.dataset.id, qty);
                    });
                });
            }

            function updateQty(id, delta) {
                const input = document.querySelector(`.qty-input[data-id="${id}"]`);
                let qty = parseInt(input.value) + delta;
                if (qty < 1) qty = 1;
                input.value = qty;
                updateQtyManual(id, qty);
            }

            function updateQtyManual(id, qty) {
                const input = document.querySelector(`.qty-input[data-id="${id}"]`);
                const price = parseInt(input.dataset.price);
                const newTotal = price * qty;

                document.querySelector(`.subtotal[data-id="${id}"]`).textContent = formatCurrency(newTotal);
                updateSummary();
            }

            function removeItem(id) {
                cart = cart.filter(pid => pid != id);
                localStorage.setItem("cart", JSON.stringify(cart));
                location.reload();
            }

            function updateSummary() {
                let totalItems = 0;
                let totalHarga = 0;
                document.querySelectorAll(".qty-input").forEach(input => {
                    totalItems += parseInt(input.value);
                    let subtotal = document.querySelector(`.subtotal[data-id="${input.dataset.id}"]`).textContent;
                    subtotal = parseInt(subtotal.replace(/\D/g, ""));
                    totalHarga += subtotal;
                });

                const discountAmount = totalHarga * (currentDiscount / 100);
                const finalTotal = totalHarga - discountAmount;

                cartCount.textContent = totalItems + " Item";
                summaryCount.textContent = totalItems + " Item";
                subtotalElement.textContent = formatCurrency(totalHarga);
                summaryTotal.textContent = formatCurrency(finalTotal);

                if (currentDiscount > 0) {
                    discountInfo.classList.remove("hidden");
                    discountAmount.textContent = `- ${currentDiscount}%`;
                } else {
                    discountInfo.classList.add("hidden");
                }
            }

            function removeVoucher() {
                currentDiscount = 0;
                voucherCode = null;
                voucherResult.innerHTML = '';
                voucherSearch.value = '';
                updateSummary();
            }

            checkVoucherButton.addEventListener("click", function() {
                const code = voucherSearch.value.trim();
                if (!code) {
                    alert("Silakan masukkan kode voucher!");
                    return;
                }

                loadingOverlay.style.display = 'flex';

                fetch("/voucher", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ voucher: code })
                })
                .then(res => res.json())
                .then(data => {
                    loadingOverlay.style.display = 'none';
                    voucherResult.innerHTML = "";
                    if (data.status === "success") {
                        currentDiscount = parseFloat(data.data.discount) || 0;
                        voucherCode = data.data.code;
                        voucherResult.innerHTML = `
                            <div class="inline-flex items-center gap-2 bg-green-100 text-green-800 text-sm font-medium px-4 py-2 rounded-full relative">
                                <span><i class="fa fa-ticket-alt"></i> ${data.data.name} - ${data.data.discount}%</span>
                                <button type="button" class="voucher-remove-btn ml-2 text-red-600 hover:text-red-800">&times;</button>
                            </div>
                        `;
                        const removeButton = voucherResult.querySelector('.voucher-remove-btn');
                        removeButton.addEventListener('click', removeVoucher);
                        discountAmount.textContent = `- ${currentDiscount}%`;
                        updateSummary();
                    } else {
                        currentDiscount = 0;
                        voucherCode = null;
                        updateSummary();
                        voucherResult.innerHTML = `
                            <div class="inline-flex items-center gap-2 bg-red-100 text-red-800 text-sm font-medium px-4 py-2 rounded-full relative">
                                <span>${data.message}</span>
                                <button type="button" class="ml-2 text-red-600 hover:text-red-800">&times;</button>
                            </div>
                        `;
                        // Attach event listener to the error message remove button
                        const errorRemoveButton = voucherResult.querySelector('button');
                        errorRemoveButton.addEventListener('click', () => errorRemoveButton.parentElement.remove());
                    }
                })
                .catch(err => {
                    loadingOverlay.style.display = 'none';
                    console.error(err);
                    alert("Terjadi kesalahan server.");
                });
            });

            document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
                radio.addEventListener("change", function() {
                    if (this.value === "delivery") {
                        deliveryNotes.classList.remove("hidden");
                    } else {
                        deliveryNotes.classList.add("hidden");
                    }
                });
            });

            checkoutForm.addEventListener("submit", async function(e) {
                e.preventDefault();
                loadingOverlay.style.display = 'flex';
                const items = [];
                document.querySelectorAll(".qty-input").forEach(input => {
                    items.push({
                        id: input.dataset.id,
                        quantity: parseInt(input.value),
                        price: parseInt(input.dataset.price)
                    });
                });

                const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
                const deliveryDesc = document.getElementById("delivery_desc").value;

                const payload = {
                    items,
                    delivery_type: deliveryType,
                    delivery_desc: deliveryType === "delivery" ? deliveryDesc : "",
                    voucher: voucherCode || ""
                };

                try {
                    const response = await fetch("{{ url('checkout') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(payload)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        window.snap.pay(result.snap_token, {
                            onSuccess: function(result) {
                                alert("Pembayaran berhasil!");
                                localStorage.removeItem("cart");
                                console.log(result);

                                fetch("/midtrans/callback", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                                    },
                                    body: JSON.stringify({
                                        order_id: result.order_id,
                                        transaction_status: result.transaction_status
                                    })
                                }).then(res => res.json())
                                .then(data => {
                                    console.log("Callback response:", data);
                                    window.location.href = "/checkout-success?order=" + result.order_id;
                                });
                            },
                            onPending: function(result) {
                                alert("Pembayaran tertunda. Silakan selesaikan pembayaran.");
                                fetch("/midtrans/callback", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                                    },
                                    body: JSON.stringify({
                                        order_id: result.order_id,
                                        transaction_status: result.transaction_status
                                    })
                                });
                            },
                            onError: function(result) {
                                alert("Pembayaran gagal. Silakan coba lagi.");
                                fetch("/midtrans/callback", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                                    },
                                    body: JSON.stringify({
                                        order_id: result.order_id,
                                        transaction_status: "error"
                                    })
                                });
                            },
                            onClose: function() {
                                alert("Anda menutup popup pembayaran.");
                            }
                        });
                    } else {
                        const error = await response.json();
                        alert("Gagal mendapatkan Snap token: " + (error.message || "Silakan coba lagi."));
                    }
                } catch (error) {
                    console.error(error);
                    alert("Terjadi kesalahan. Silakan coba lagi.");
                } finally {
                    loadingOverlay.style.display = 'none';
                }
            });
        });
    </script>
@endpush