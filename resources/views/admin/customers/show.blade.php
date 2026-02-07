@extends('master')
@section('title', 'Detail Customer')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Customer</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customer Analysis</a></div>
                    <div class="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card profile-widget">
                            <div class="profile-widget-header text-center pt-3 d-flex justify-content-center">
                                <img alt="image" src="https://ui-avatars.com/api/?name={{ urlencode($customer->customer_name) }}&background=random&size=128" class="rounded-circle shadow-sm" style="width: 100px; height: 100px;">
                            </div>
                            <div class="profile-widget-description text-center">
                                <div class="profile-widget-name">{{ $customer->customer_name }}</div>
                                <div class="text-muted">{{ $customer->customer_phone }}</div>
                                <div class="text-muted small">{{ $customer->customer_email ?? '-' }}</div>
                                <hr>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="text-small font-weight-bold text-muted text-uppercase">Total Belanja</div>
                                        <div class="h6 font-weight-bold text-primary">Rp {{ number_format($customer->total_spending) }}</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="text-small font-weight-bold text-muted text-uppercase">Transaksi</div>
                                        <div class="h6 font-weight-bold">{{ $customer->total_transactions }}x</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="text-small font-weight-bold text-muted text-uppercase">Avg Order</div>
                                        <div class="h6">Rp {{ number_format($customer->avg_transaction) }}</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="text-small font-weight-bold text-muted text-uppercase">Terakhir Beli</div>
                                        <div class="h6">{{ \Carbon\Carbon::parse($customer->last_transaction)->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    @php
                                        $daysSinceLast = now()->diffInDays(\Carbon\Carbon::parse($customer->last_transaction));
                                        $statusClass = 'badge-info';
                                        $statusText = 'Aktif';
                                        if ($daysSinceLast > 60) { $statusClass = 'badge-danger'; $statusText = 'Tidak Aktif'; }
                                        elseif ($customer->total_transactions > 10) { $statusClass = 'badge-primary'; $statusText = 'Loyal'; }
                                        elseif ($customer->total_transactions > 3) { $statusClass = 'badge-success'; $statusText = 'Potensial'; }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>🛍️ Produk Favorit</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled list-unstyled-border">
                                    @foreach($favoriteProducts as $fav)
                                    <li class="media">
                                        <div class="media-body">
                                            <div class="float-right text-primary font-weight-bold">{{ $fav->total_qty }} Qty</div>
                                            <div class="media-title">{{ $fav->product->name }}</div>
                                            <div class="text-small text-muted">Total Nilai: Rp {{ number_format($fav->total_value) }}</div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>🧾 Riwayat Transaksi</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-md">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Total</th>
                                                <th>Metode</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transactions as $trx)
                                            <tr>
                                                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="font-weight-bold">Rp {{ number_format($trx->total_amount) }}</td>
                                                <td><span class="badge badge-light text-uppercase">{{ $trx->payment_method }}</span></td>
                                                <td class="text-center"><a href="{{ url('admin/transactions/show/'.$trx->id) }}" class="btn btn-sm btn-outline-primary">Lihat Transaksi</a></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
