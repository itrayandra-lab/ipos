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
                                <div class="card card-primary border shadow-sm">
                                    <div class="card-header">
                                        <h4><i class="fas fa-info-circle"></i> Informasi Umum</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">ID Transaksi</th>
                                                <td><span class="badge badge-light">#{{ $transaction->id }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal</th>
                                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kasir / Pengguna</th>
                                                <td>{{ $transaction->user->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Sumber / Channel</th>
                                                <td><span class="badge badge-info text-uppercase">{{ $transaction->source ?? 'Offline' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Catatan</th>
                                                <td>{{ $transaction->notes ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="card card-warning border shadow-sm mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-user text-warning"></i> Informasi Customer</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">Nama</th>
                                                <td>{{ $transaction->customer_name ?? 'Guest' }}</td>
                                            </tr>
                                            <tr>
                                                <th>WA / Phone</th>
                                                <td>{{ $transaction->customer_phone ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $transaction->customer_email ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-success border shadow-sm">
                                    <div class="card-header">
                                        <h4><i class="fas fa-money-bill-wave text-success"></i> Pembayaran</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">Status</th>
                                                <td>
                                                    @if($transaction->payment_status == 'paid')
                                                        <span class="badge badge-success">Lunas</span>
                                                    @else
                                                        <span class="badge badge-warning">{{ ucfirst($transaction->payment_status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Metode</th>
                                                <td><span class="badge badge-light text-uppercase">{{ $transaction->payment_method ?? 'Cash' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Subtotal</th>
                                                <td>Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kode Voucher</th>
                                                <td>{{ $transaction->voucher_code ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Potongan Diskon</th>
                                                <td class="text-danger">- Rp {{ number_format($transaction->discount ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Total Akhir</th>
                                                <td class="font-weight-bold h5 text-primary">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="card card-dark border shadow-sm mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-truck"></i> Pengiriman & Trace</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">Tipe</th>
                                                <td><span class="badge badge-secondary text-uppercase">{{ $transaction->delivery_type ?? 'Pickup' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Deskripsi</th>
                                                <td>{{ $transaction->delivery_desc ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Midtrans Order</th>
                                                <td><small class="text-muted">{{ $transaction->midtrans_order_id ?? '-' }}</small></td>
                                            </tr>
                                            <tr>
                                                <th>Midtrans Trx ID</th>
                                                <td><small class="text-muted">{{ $transaction->midtrans_transaction_id ?? '-' }}</small></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-5">📦 Item Transaksi</div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mt-2">
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
                                            <td class="font-weight-bold">{{ $item->product->name }}</td>
                                            <td><span class="badge badge-light">{{ $item->batch ? $item->batch->batch_no : '-' }}</span></td>
                                            <td>{{ $item->qty }}</td>
                                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Subtotal</th>
                                        <th class="text-right">Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</th>
                                    </tr>
                                    @if ($transaction->discount > 0)
                                        <tr>
                                            <th colspan="5" class="text-right text-danger">Diskon</th>
                                            <th class="text-right text-danger">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="5" class="text-right h5">Grand Total</th>
                                        <th class="text-right h5 text-primary">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection