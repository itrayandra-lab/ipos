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
                        @if ($product)
                            @php
                                $discountPercent = 0;
                                $discountInfo = null;
                                $hasDiscount = false;

                                if ($product->price_real && $product->price_real > $product->price) {
                                    $discountPercent = round((($product->price_real - $product->price) / $product->price_real) * 100);
                                    $hasDiscount = $discountPercent > 0;

                                    if ($hasDiscount) {
                                        $vouchers = $product->vouchers;
                                        $activeVoucher = $vouchers->firstWhere('status', 'ACTIVE');
                                        if ($activeVoucher) {
                                            $discountInfo = $activeVoucher->name;
                                        }
                                    }
                                }
                            @endphp
                            <div class="bg-white">
                                <div class="">
                                    <nav aria-label="Breadcrumb" class="max-lg:hidden">
                                        <ol role="list" class="mx-auto flex max-w-2xl items-center space-x-2 px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                                            <li>
                                                <div class="flex items-center">
                                                    <a href="{{ route('home') }}" class="mr-2 text-sm font-medium text-gray-900">LUNARAY</a>
                                                    <svg viewBox="0 0 16 20" width="16" height="20" fill="currentColor" aria-hidden="true" class="h-5 w-4 text-gray-300">
                                                        <path d="M5.697 4.34L8.98 16.532h1.327L7.025 4.341H5.697z" />
                                                    </svg>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="flex items-center">
                                                    <a href="{{ route('home', ['category' => $product->category->slug]) }}" class="mr-2 text-sm font-medium text-gray-900">{{ $product->category->name }}</a>
                                                    <svg viewBox="0 0 16 20" width="16" height="20" fill="currentColor" aria-hidden="true" class="h-5 w-4 text-gray-300">
                                                        <path d="M5.697 4.34L8.98 16.532h1.327L7.025 4.341H5.697z" />
                                                    </svg>
                                                </div>
                                            </li>
                                            <li class="text-sm">
                                                <a href="#" aria-current="page" class="font-medium text-gray-500 hover:text-gray-600">{{ $product->name }}</a>
                                            </li>
                                        </ol>
                                    </nav>

                                    <!-- Image gallery -->
                                    <div class="mx-auto mt-6 max-w-2xl sm:px-6 lg:grid lg:max-w-7xl lg:grid-cols-3 lg:gap-8 lg:px-8 overflow-x-auto">
                                        @if ($product->photos->isEmpty())
                                            <img src="{{ asset('assets/img/Asset 3.png') }}" alt="{{ $product->name }}" class="row-span-2 aspect-3/4 mb-2 size-full rounded-lg object-cover" />
                                        @elseif($product->photos->count() == 1)
                                            <img src="{{ asset($product->photos->first()->foto) }}" alt="{{ $product->name }}" class="row-span-2 aspect-3/4 mb-2 size-full rounded-lg object-cover" />
                                        @else
                                            @foreach ($product->photos as $photo)
                                                <img src="{{ asset($photo->foto) }}" alt="{{ $product->name }}" class="row-span-2 aspect-3/4 mb-2 size-full rounded-lg object-cover" />
                                            @endforeach
                                        @endif
                                    </div>

                                    <div class="mx-auto max-w-2xl px-4 pt-10 pb-16 sm:px-6 lg:grid lg:max-w-7xl lg:grid-cols-3 lg:grid-rows-[auto_auto_1fr] lg:gap-x-8 lg:px-8 lg:pt-16 lg:pb-24">
                                        <div class="lg:col-span-2 lg:border-r lg:border-gray-200 lg:pr-8">
                                            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $product->category->name }} {{ $product->name }}</h1>
                                        </div>

                                        <div class="mt-4 lg:row-span-3 lg:mt-0">
                                            <h2 class="sr-only">Product information</h2>
                                            <div class="text-left">
                                                @if($hasDiscount)
                                                    <p class="text-xs text-gray-500 line-through mb-1">
                                                        Rp. {{ number_format($product->price_real,0,',','.') }}
                                                    </p>
                                                    <p class="text-2xl tracking-tight text-gray-900 mb-1">
                                                        Rp. {{ number_format($product->price,0,',','.') }}
                                                    </p>
                                                    <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full mb-1">
                                                        {{ $discountPercent }}% OFF
                                                    </span>
                                                @else
                                                    <p class="text-2xl tracking-tight text-gray-900">
                                                        Rp. {{ number_format($product->price,0,',','.') }}
                                                    </p>
                                                @endif
                                            </div>

                                            <form class="mt-5" id="checkout-form">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <div>
                                                    <h3 class="text-sm font-medium text-gray-900">Metode Pengiriman</h3>
                                                    <div class="mt-4">
                                                        <div class="flex items-center mb-4">
                                                            <input id="pickup-radio" type="radio" value="pickup" name="delivery_type" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500" checked>
                                                            <label for="pickup-radio" class="ms-2 text-sm font-medium text-gray-900">Pickup</label>
                                                        </div>
                                                        <div class="flex items-center">
                                                            <input id="delivery-radio" type="radio" value="delivery" name="delivery_type" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                                                            <label for="delivery-radio" class="ms-2 text-sm font-medium text-gray-900">Delivery</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Delivery Notes -->
                                                <div id="delivery-notes" class="mt-4 hidden">
                                                    <label for="delivery_desc" class="text-sm font-medium text-gray-900">Catatan Pengiriman</label>
                                                    <textarea id="delivery_desc" name="delivery_desc" rows="4" class="mt-2 text-sm w-full rounded-md border p-2 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Isi dengan nama, alamat dan lainnya"></textarea>
                                                </div>

                                                <!-- Quantity Counter -->
                                                <div class="mt-4">
                                                    <h3 class="text-sm font-medium text-gray-900">Jumlah</h3>
                                                    <div class="mt-4 flex items-center space-x-2">
                                                        <button type="button" id="decrease-quantity" class="h-10 w-10 flex items-center justify-center rounded-full border border-gray-400 text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition">-</button>
                                                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-28 h-10 rounded-lg border border-gray-300 text-center text-base font-medium focus:border-indigo-400 focus:ring-indigo-400">
                                                        <button type="button" id="increase-quantity" class="h-10 w-10 flex items-center justify-center rounded-full border border-gray-400 text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition">+</button>
                                                    </div>
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

                                                <!-- Total Price -->
                                                <div class="mt-4">
                                                    <h3 class="text-sm font-medium text-gray-900">Total Bayar</h3>
                                                    <p class="text-xl font-semibold text-gray-900" id="total-price">Rp. {{ number_format($product->price,0,',','.') }}</p>
                                                    <p class="text-sm text-gray-600 hidden" id="original-price"></p>
                                                    <p class="text-sm text-green-600 hidden" id="discount-info"></p>
                                                </div>

                                                @if (auth()->user())
                                                    <button type="submit" id="pay-button" class="mt-10 flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-hidden">Bayar Sekarang <i class="ms-2 fa-solid fa-comments-dollar"></i></button>
                                                @else
                                                    <a href="{{ route('login') }}" class="mt-10 flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-hidden"><i class="fa-solid fa-right-to-bracket mr-2 transition-transform group-hover:translate-x-1"></i> Login / Masuk</a>
                                                @endif
                                            </form>
                                        </div>

                                        <div class="py-5 lg:col-span-2 lg:col-start-1 lg:border-r lg:border-gray-200 lg:pt-6 lg:pr-8 lg:pb-16">
                                            <div class="mt-3">
                                                <h3 class="text-sm font-medium text-gray-900">Informasi Produk</h3>
                                                <div class="mt-4">
                                                    <ul role="list" class="list-disc space-y-2 pl-4 text-sm">
                                                        <li class="text-gray-400"><span class="text-gray-600">Neto: {{ $product->neto ? $product->neto . ' / ' : '-' }} {{ $product->pieces }}</span></li>
                                                        <li class="text-gray-400">
                                                            @if ($product->stock > 0)
                                                                <span class="text-gray-600">Stok: {{ $product->stock }}</span>
                                                            @else
                                                                <span class="text-red-500 italic">Stok Habis</span>
                                                            @endif
                                                        </li>
                                                        <li class="text-gray-400"><span class="text-gray-600">Merk: {{ $product->category->name }}</span></li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="mt-10">
                                                <h2 class="text-sm font-medium text-gray-900">Detail</h2>
                                                <div class="mt-4 space-y-6">
                                                    <p class="text-sm text-gray-600">{{ $product->description ?? ($product->category->description ?? 'Detail produk untuk ' . $product->name . ' dari kategori ' . $product->category->name . '.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white/80 py-24 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-12 w-12 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                    <h3 class="mt-2 text-xl font-semibold text-gray-900">Produk tidak ditemukan</h3>
                                    <p class="mt-1 text-sm text-gray-500">Kami tidak dapat menemukan produk yang cocok dengan pencarian atau filter Anda.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('home') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Hapus semua filter</a>
                                    </div>
                                </div>
                        @endif
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ config('midtrans.url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        let currentDiscount = 0;

        function formatCurrency(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
        }

        function updateTotalPrice() {
            const price = {{ $product->price }};
            const quantity = parseInt(quantityInput.value) || 1;
            const originalTotal = price * quantity;
            const discountAmount = originalTotal * (currentDiscount / 100);
            const finalTotal = originalTotal - discountAmount;

            const totalPriceElement = document.getElementById('total-price');
            const originalPriceElement = document.getElementById('original-price');
            const discountInfoElement = document.getElementById('discount-info');

            totalPriceElement.textContent = formatCurrency(finalTotal);

            if (currentDiscount > 0) {
                originalPriceElement.textContent = `Harga Asli: ${formatCurrency(originalTotal)}`;
                originalPriceElement.classList.remove('hidden');
                discountInfoElement.textContent = `Diskon ${currentDiscount}%: -${formatCurrency(discountAmount)}`;
                discountInfoElement.classList.remove('hidden');
            } else {
                originalPriceElement.classList.add('hidden');
                discountInfoElement.classList.add('hidden');
            }
        }

        document.getElementById('check-voucher').addEventListener('click', function () {
            const loadingOverlay = document.getElementById('loading-overlay');
            let code = document.getElementById('voucher-search').value;
            let resultBox = document.getElementById('voucher-result');

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
                console.log(data);
                resultBox.innerHTML = "";
                loadingOverlay.style.display = 'none';
                if (data.status === "success") {
                    currentDiscount = parseFloat(data.data.discount) || 0;
                    resultBox.innerHTML = `
                        <div class="inline-flex items-center gap-2 bg-green-100 text-green-800 text-sm font-medium px-4 py-2 rounded-full relative">
                            <span><i class="fa fa-ticket-alt"></i> ${data.data.name} - ${data.data.discount}%</span>
                            <button onclick="removeVoucher()" class="ml-2 text-red-600 hover:text-red-800">&times;</button>
                        </div>
                    `;
                    updateTotalPrice();
                } else {
                    currentDiscount = 0;
                    updateTotalPrice();
                    resultBox.innerHTML = `
                        <div class="inline-flex items-center gap-2 bg-red-100 text-red-800 text-sm font-medium px-4 py-2 rounded-full relative">
                            <span>${data.message}</span>
                            <button onclick="this.parentElement.remove()" class="ml-2 text-red-600 hover:text-red-800">&times;</button>
                        </div>
                    `;
                }
            })
            .catch(err => {
                loadingOverlay.style.display = 'none';
                console.error(err);
                alert("Terjadi kesalahan server.");
            });
        });

        function removeVoucher() {
            currentDiscount = 0;
            document.getElementById('voucher-result').innerHTML = '';
            updateTotalPrice();
        }

        document.querySelectorAll('input[name="delivery_type"]').forEach((radio) => {
            radio.addEventListener('change', function() {
                const deliveryNotes = document.getElementById('delivery-notes');
                if (this.value === 'delivery') {
                    deliveryNotes.classList.remove('hidden');
                } else {
                    deliveryNotes.classList.add('hidden');
                }
            });
        });

        const quantityInput = document.getElementById('quantity');
        const decreaseButton = document.getElementById('decrease-quantity');
        const increaseButton = document.getElementById('increase-quantity');

        decreaseButton.addEventListener('click', () => {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
                updateTotalPrice();
            }
        });

        increaseButton.addEventListener('click', () => {
            let value = parseInt(quantityInput.value);
            let max = parseInt(quantityInput.max);
            if (value < max) {
                quantityInput.value = value + 1;
                updateTotalPrice();
            }
        });

        quantityInput.addEventListener('input', () => {
            let value = parseInt(quantityInput.value);
            let max = parseInt(quantityInput.max);
            if (value < 1 || isNaN(value)) {
                quantityInput.value = 1;
            } else if (value > max) {
                quantityInput.value = max;
            }
            updateTotalPrice();
        });

        updateTotalPrice();

        const checkoutForm = document.getElementById('checkout-form');
        const loadingOverlay = document.getElementById('loading-overlay');

        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            loadingOverlay.style.display = 'flex';

            const formData = new FormData(checkoutForm);
            const data = {
                product_id: formData.get('product_id'),
                quantity: formData.get('quantity'),
                delivery_method: formData.get('delivery_type'),
                delivery_desc: formData.get('delivery_desc') || '',
                voucher: formData.get('voucher') || '',
                _token: formData.get('_token')
            };

            try {
                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': data._token
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    const result = await response.json();

                    window.snap.pay(result.snap_token, {
                        onSuccess: function(result) {
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
                                window.location.href = "/checkout-success?order=" + result.order_id;
                            });
                            alert("Pembayaran berhasil!");
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
                    alert('Checkout gagal: ' + (error.message || 'Terjadi kesalahan'));
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
            } finally {
                loadingOverlay.style.display = 'none';
            }
        });
    </script>
@endpush