@extends('master')

@section('title', 'Detail Transaksi')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Transaksi</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ url('admin/transactions') }}">Data Transaksi</a></div>
                    <div class="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Detail Transaksi #{{ $transaction->id }}</h2>
                <p class="section-lead">Informasi lengkap tentang transaksi.</p>

                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Transaksi</h4>
                        <div class="card-header-action">
                            <a href="{{ url('admin/transactions') }}" class="btn btn-primary">Kembali</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informasi Umum</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>ID Transaksi</th>
                                        <td>{{ $transaction->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pengguna</th>
                                        <td>{{ $transaction->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email Pengguna</th>
                                        <td>{{ $transaction->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Subtotal</th>
                                        <td>Rp {{ number_format($transaction->items->sum(fn($item) => $item->subtotal), 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Diskon</th>
                                        <td>
                                            @if (\App\Models\Voucher::getCode($transaction->voucher_code)  > 0)
                                                {{ \App\Models\Voucher::getCode($transaction->voucher_code)  }}% (Rp {{ number_format($transaction->items->sum(fn($item) => $item->subtotal) * (\App\Models\Voucher::getCode($transaction->voucher_code)  / 100), 0, ',', '.') }})
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kode Voucher</th>
                                        <td>{{ $transaction->voucher_code ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status Pembayaran</th>
                                        <td>{{ ucfirst($transaction->payment_status) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipe Pengiriman</th>
                                        <td>{{ ucfirst($transaction->delivery_type) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Deskripsi Pengiriman</th>
                                        <td>{{ $transaction->delivery_desc ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Transaksi</th>
                                        <td>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Midtrans Order ID</th>
                                        <td>{{ $transaction->midtrans_order_id ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Midtrans Transaction ID</th>
                                        <td>{{ $transaction->midtrans_transaction_id ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h5 class="mt-4">Item Transaksi</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produk</th>
                                    <th>Batch</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaction->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product->name }}</td>
                                        <td><span class="badge badge-light">{{ $item->batch ? $item->batch->batch_no : '-' }}</span></td>
                                        <td>{{ $item->qty }}</td>
                                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Subtotal</th>
                                    <th>Rp {{ number_format($transaction->items->sum(fn($item) => $item->subtotal), 0, ',', '.') }}</th>
                                </tr>
                                @if ($transaction->discount > 0)
                                    <tr>
                                        <th colspan="4" class="text-right">Diskon ({{ \App\Models\Voucher::getCode($transaction->voucher_code) }}%)</th>
                                        <th>- Rp {{ number_format($transaction->items->sum(fn($item) => $item->subtotal) * (\App\Models\Voucher::getCode($transaction->voucher_code)  / 100), 0, ',', '.') }}</th>
                                    </tr>
                                @endif
                                <tr>
                                    <th colspan="4" class="text-right">Total</th>
                                    <th>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection