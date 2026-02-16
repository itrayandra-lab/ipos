@extends('guest')

@section('title', 'Beranda')

@section('content')
    <div class="relative">

        <main class="mx-auto max-w-8xl px-4 sm:px-6 lg:px-8">
            @include('guest.components.header')

            <section aria-labelledby="products-heading" class="pt-6 pb-24">
                <h2 id="products-heading" class="sr-only">Produk</h2>
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-4 lg:items-start">
                    @include('guest.components.sidebar_merek')

                    <div class="lg:col-span-3">
                        @if ($errors->any())
                            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 flex items-start gap-3" role="alert">
                                <i class="fa-solid fa-circle-exclamation text-red-500 text-xl mt-0.5"></i>
                                <div>
                                    <span class="font-semibold">Terjadi Kesalahan!</span>
                                    <p class="text-gray-700 mt-1">Mohon periksa kembali data yang Anda masukkan:</p>
                                    <ul class="list-disc list-inside mt-2 text-gray-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($products->count() > 0)
                        <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
                            @foreach($products as $product)
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
                            <div class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                                @forelse ($product->photos->take(1) as $item)
                                    <a href="{{ route('product.show', $product->slug) }}" class="aspect-h-4 aspect-w-3 bg-gray-200 sm:aspect-none sm:h-80">
                                        <img src="{{ $item->foto }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105 sm:h-full sm:w-full" />
                                    </a>
                                @empty
                                    <a href="{{ route('product.show', $product->slug) }}" class="aspect-h-4 aspect-w-3 bg-gray-200 sm:aspect-none sm:h-80">
                                        <img src="{{ asset('assets/img/Asset 3.png') }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105 sm:h-full sm:w-full" />
                                    </a>
                                @endforelse
                                <div class="flex flex-1 flex-col space-y-2 p-4">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('product.show', $product->slug) }}">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        {{-- <p class="mt-1 text-xs text-gray-500">
                                            <span class="inline-block bg-indigo-100 text-indigo-800 rounded-full px-2 py-0.5 font-semibold">
                                                {{ $product->category->name }}
                                            </span>
                                        </p> --}}
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-left">
                                            @if($hasDiscount)
                                                <p class="text-xs text-gray-500 line-through mb-1">
                                                    Rp. {{ number_format($product->price_real,0,',','.') }}
                                                </p>
                                                <p class="text-xl font-bold text-gray-900 mb-1">
                                                    Rp. {{ number_format($product->price,0,',','.') }}
                                                </p>
                                                <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full mb-1">
                                                    {{ $discountPercent }}% OFF
                                                </span>
                                            @else
                                                <p class="text-xl font-bold text-gray-900">
                                                    Rp. {{ number_format($product->price,0,',','.') }}
                                                </p>
                                            @endif
                                        </div>
                                        <button type="button"
                                            class="rounded-full  transition add-to-cart"
                                            data-id="{{ $product->id }}">
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white/80 py-24 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-12 w-12 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            <h3 class="mt-2 text-xl font-semibold text-gray-900">Produk tidak ditemukan</h3>
                            <p class="mt-1 text-sm text-gray-500">Kami tidak dapat menemukan produk yang cocok dengan pencarian atau filter Anda.</p>
                            <div class="mt-6">
                                <a href="{{ route('home') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Hapus semua filter
                                </a>
                            </div>
                        </div>
                        @endif

                        <div class="mt-10 ml-auto text-center flex justify-center">
                            @if ($products->hasPages())
                                <nav aria-label="Pagination" class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <!-- Previous Button -->
                                        <a href="{{ $products->previousPageUrl() }}" 
                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 {{ $products->onFirstPage() ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50' }}"
                                        {{ $products->onFirstPage() ? 'aria-disabled="true"' : '' }}>
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                            </svg>
                                        </a>

                                        <div class="flex space-x-1">
                                            @php
                                                $currentPage = $products->currentPage();
                                                $lastPage = $products->lastPage();
                                                $range = 2; 
                                                $start = max(1, $currentPage - $range);
                                                $end = min($lastPage, $currentPage + $range);
                                            @endphp

                                            <!-- First Page -->
                                            @if ($start > 1)
                                                <a href="{{ $products->url(1) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50"
                                                aria-current="{{ $products->currentPage() == 1 ? 'page' : '' }}">
                                                    1
                                                </a>
                                                @if ($start > 2)
                                                    <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">...</span>
                                                @endif
                                            @endif

                                            <!-- Page Range -->
                                            @for ($page = $start; $page <= $end; $page++)
                                                <a href="{{ $products->url($page) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium {{ $products->currentPage() == $page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                                                aria-current="{{ $products->currentPage() == $page ? 'page' : '' }}">
                                                    {{ $page }}
                                                </a>
                                            @endfor

                                            <!-- Last Page -->
                                            @if ($end < $lastPage)
                                                @if ($end < $lastPage - 1)
                                                    <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">...</span>
                                                @endif
                                                <a href="{{ $products->url($lastPage) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50"
                                                aria-current="{{ $products->currentPage() == $lastPage ? 'page' : '' }}">
                                                    {{ $lastPage }}
                                                </a>
                                            @endif
                                        </div>

                                        <!-- Next Button -->
                                        <a href="{{ $products->nextPageUrl() }}"
                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 {{ $products->hasMorePages() ? 'hover:bg-gray-50' : 'cursor-not-allowed opacity-50' }}"
                                        {{ $products->hasMorePages() ? '' : 'aria-disabled="true"' }}>
                                            <svg class=" h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection

@push('scripts')


@endpush