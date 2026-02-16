@extends('guest')

@section('title', 'Beranda')

@section('content')
    <div class="relative">
        <main class="mx-auto max-w-8xl px-4 sm:px-6 lg:px-8">
            <div class="no-print">
                @include('guest.components.header')
            </div>

            <section aria-labelledby="products-heading" class="pt-6 pb-24">
                <h2 id="products-heading" class="sr-only">Produk</h2>
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-4 lg:items-start">
                    <div class="no-print">
                        @include('guest.components.sidebar_merek')
                    </div>

                    <div class="lg:col-span-3">
                        <div class="max-w-3xl mx-auto p-6 bg-white rounded shadow-sm my-6">
                            <!-- Print Button -->
                            <div class="mb-4 text-right no-print">
                                <button onclick="printInvoice()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Print Invoice
                                </button>
                            </div>

                            <!-- Invoice Content to be Printed -->
                            <div id="invoice">
                                <div class="grid grid-cols-2 items-center">
                                    <div>
                                        <!-- Company logo -->
                                        <img src="{{ asset('assets/img/logo.png') }}" alt="company-logo" height="100" width="100">
                                    </div>

                                    <div class="text-right">
                                        <p>Lunaray beauty factory.</p>
                                        <p class="text-gray-500 text-sm">info@lunaray.id</p>
                                        <p class="text-gray-500 text-sm mt-1">+62 822-8959-4567</p>
                                    </div>
                                </div>

                                <!-- Client info -->
                                <div class="grid grid-cols-2 items-center mt-8">
                                    <div>
                                        <p class="font-bold text-gray-800">Informasi Order :</p>
                                        <p>
                                            Order ID : {{ $transaction->midtrans_order_id }} <br>
                                            Tanggal : {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }} <br>
                                            Status : 
                                            <span class="@if($transaction->payment_status == 'paid') text-green-500
                                                        @elseif($transaction->payment_status == 'pending') text-yellow-500
                                                        @elseif($transaction->payment_status == 'failed') text-red-500
                                                        @else text-gray-500 @endif">
                                                {{ ucfirst($transaction->payment_status) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Invoice Items -->
                                <div class="-mx-4 mt-8 flow-root sm:mx-0">
                                    <table class="min-w-full">
                                        <colgroup>
                                            <col class="w-full sm:w-1/2">
                                            <col class="sm:w-1/6">
                                            <col class="sm:w-1/6">
                                            <col class="sm:w-1/6">
                                        </colgroup>
                                        <thead class="border-b border-gray-300 text-gray-900">
                                            <tr>
                                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Produk</th>
                                                <th scope="col" class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell">Quantity</th>
                                                <th scope="col" class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell">Harga</th>
                                                <th scope="col" class="py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900 sm:pr-0">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($transaction->items as $item)
                                                @php
                                                    $discountPercent = 0;
                                                    $discountInfo = null;
                                                    $hasDiscount = false;

                                                    if ($item->product->price_real && $item->product->price_real > $item->price) {
                                                        $discountPercent = round((($item->product->price_real - $item->price) / $item->product->price_real) * 100);
                                                        $hasDiscount = $discountPercent > 0;

                                                        if ($hasDiscount) {
                                                            $vouchers = $item->product->vouchers;
                                                            $activeVoucher = $vouchers->firstWhere('status', 'ACTIVE');
                                                            if ($activeVoucher) {
                                                                $discountInfo = $activeVoucher->name;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <tr class="border-b border-gray-200">
                                                    <td class="max-w-0 py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                        <div class="font-medium text-gray-900">{{ $item->product->name ?? 'Product' }}</div>
                                                        <div class="mt-1 truncate text-gray-500">{{ $item->product->category->name ?? 'No description' }}</div>
                                                    </td>
                                                    <td class="hidden px-3 py-5 text-right text-sm text-gray-500 sm:table-cell">{{ $item->qty }}</td>
                                                    <td class="hidden px-3 py-5 text-right text-sm text-gray-500 sm:table-cell">
                                                        @if($hasDiscount)
                                                            <p class="text-xs text-gray-500 line-through mb-1">Rp. {{ number_format($item->product->price_real,0,',','.') }}</p>
                                                            <p class="text-sm font-semibold text-gray-900 mb-1">Rp. {{ number_format($item->price,0,',','.') }}</p>
                                                            <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full mb-1">{{ $discountPercent }}% OFF</span>
                                                        @else
                                                            Rp. {{ number_format($item->price,0,',','.') }}
                                                        @endif
                                                    </td>
                                                    <td class="py-5 pl-3 pr-4 text-right text-sm text-gray-500 sm:pr-0">Rp. {{ number_format($item->subtotal,0,',','.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-6 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0">Subtotal</th>
                                                <th scope="row" class="pl-6 pr-3 pt-6 text-left text-sm font-normal text-gray-500 sm:hidden">Subtotal</th>
                                                <td class="pl-3 pr-6 pt-6 text-right text-sm text-gray-500 sm:pr-0">Rp. {{ number_format($transaction->total_amount + ($transaction->discount ?? 0)) }}</td>
                                            </tr>
                                            @if ($transaction->discount > 0)
                                                @php $voucher = \App\Models\Voucher::where('code', $transaction->voucher_code)->first(); @endphp
                                                <tr>
                                                    <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0">Diskon {{ $voucher?->name ?? '' }} ({{ \App\Models\Voucher::getCode($transaction->voucher_code) }}%):</th>
                                                    <th scope="row" class="pl-6 pr-3 pt-4 text-left text-sm font-normal text-gray-500 sm:hidden">Diskon {{ $voucher?->name ?? '' }} ({{ \App\Models\Voucher::getCode($transaction->voucher_code) }}%):</th>
                                                    <td class="pl-3 pr-6 pt-4 text-right text-sm text-green-600 sm:pr-0">- Rp. {{ number_format($transaction->discount) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 text-right text-sm font-semibold text-gray-900 sm:table-cell sm:pl-0">Total</th>
                                                <th scope="row" class="pl-6 pr-3 pt-4 text-left text-sm font-semibold text-gray-900 sm:hidden">Total</th>
                                                <td class="pl-3 pr-4 pt-4 text-right text-sm font-semibold text-gray-900 sm:pr-0">Rp. {{ number_format($transaction->total_amount,0,',','.') }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Footer -->
                                <div class="border-t-2 pt-4 text-xs text-gray-500 text-center mt-16">
                                    @if($transaction->payment_status == 'paid')
                                        Pembayaran faktur telah diterima. Terima kasih atas pembayaran Anda.
                                    @else
                                        Mohon lakukan pembayaran faktur sebelum tanggal jatuh tempo. Anda dapat melakukan pembayaran dengan masuk ke akun Anda melalui portal klien resmi kami.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            #invoice {
                width: 100%;
                margin: 0;
            }
        }
    </style>

    <script>
        function printInvoice() {
            window.print();
        }
    </script>
@endsection