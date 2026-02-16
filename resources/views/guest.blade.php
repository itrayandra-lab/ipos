<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
     @include('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="{{ asset('assets/mix/custom.css') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alata&family=Itim&family=Lora:ital,wght@0,400..700;1,400..700&family=Onest:wght@100..900&display=swap"
    rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('dist/icon/awesome.js') }}"></script>
</head>
@stack('styles')

<body class="bg-white font-sans">
    <div class="min-h-screen flex flex-col">
        <main class="flex-grow">
            <div id="loading-overlay" class="loading-overlay">
                <img src="{{ asset('assets/img/logo-black.png') }}" alt="Loading" class="loading-logo">
            </div>

            <el-dialog>
                <dialog id="mobile-filters" class="overflow-hidden backdrop:bg-transparent lg:hidden">
                    <el-dialog-backdrop
                        class="fixed inset-0 bg-black/25 transition-opacity duration-300 ease-linear data-closed:opacity-0"></el-dialog-backdrop>
                    <div tabindex="0" class="fixed inset-0 flex focus:outline-none">
                        <el-dialog-panel
                            class="relative ml-auto flex size-full max-w-xs transform flex-col overflow-y-auto bg-white pt-4 pb-6 shadow-xl transition duration-300 ease-in-out data-closed:translate-x-full">
                            <div class="flex items-center justify-between px-4">
                                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                    <i class="fa-solid fa-filter mr-2 text-indigo-600"></i>Filter
                                </h2>
                                <button type="button" command="close" commandfor="mobile-filters"
                                    class="relative -mr-2 flex size-10 items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                    <span class="absolute -inset-0.5"></span>
                                    <span class="sr-only">Tutup menu</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        data-slot="icon" aria-hidden="true" class="size-6">
                                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>

                            <form class="mt-4 border-t border-dashed" method="GET" action="{{ route('home') }}">
                                <h3 class="sr-only">Kategori</h3>
                                <ul role="list" class="px-2 py-3 font-medium text-gray-900">
                                    <li>
                                        <a href="{{ route('home') }}"
                                            class="block rounded-md px-2 py-3 transition-colors">SEMUANYA</a>
                                    </li>
                                    @foreach ($merek as $m)
                                        <li>
                                            <a href="{{ route('home', ['merek' => $m->slug]) }}"
                                                class="block rounded-md px-2 py-3 transition-colors {{ request('merek') == $m->slug ? 'bg-indigo-50 text-indigo-600' : 'hover:bg-gray-50' }}">{{ $m->name }}</a>
                                        </li>
                                    @endforeach
                                    <li>
                                        <div class="mt-6 border-t pt-4">
                                            <a href="{{ route('login') }}"
                                                class="group flex w-full items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                                                <i class="fa-solid fa-right-to-bracket mr-2 transition-transform group-hover:translate-x-1"></i>
                                                @if (!auth()->user())
                                                    Login / Masuk
                                                @else
                                                    Dashboard
                                                @endif
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </form>
                        </el-dialog-panel>
                    </div>
                </dialog>
            </el-dialog>
            
            @yield('content')
        </main>

        <footer class="shadow-inner text-black py-6 no-print">
            <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
                <p class="text- text-center md:text-left mb-4 md:mb-0">
                    Copyright &copy; <a href="https://lunaray.id" class="hover:text-blue-400">beauty latory</a>, All Rights
                    Reserved
                </p>
            </div>
        </footer>
    </div>

</body>
@stack('scripts')
<script>
    function updateCartCount() {
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        document.getElementById("cart-count").textContent = cart.length;
    }

    document.addEventListener("DOMContentLoaded", updateCartCount);

    document.addEventListener("cart-updated", updateCartCount);

    document.querySelectorAll(".add-to-cart").forEach(btn => {
        btn.addEventListener("click", function() {
            let id = this.dataset.id;
            let cart = JSON.parse(localStorage.getItem("cart")) || [];

            if (!cart.includes(id)) {
                cart.push(id);
                localStorage.setItem("cart", JSON.stringify(cart));
                document.dispatchEvent(new Event("cart-updated"));
            }
            alert('Produk dimasukan kekeranjang.')
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

</html>
