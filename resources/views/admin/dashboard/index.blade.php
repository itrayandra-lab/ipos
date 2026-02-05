@extends('master')
@section('title', 'Dashboard Admin - ')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <!-- Jumlah User -->
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Jumlah User</h4>
                                </div>
                                <div class="card-body">
                                    {{ $userCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jumlah Produk -->
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Jumlah Produk</h4>
                                </div>
                                <div class="card-body">
                                    {{ $productCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jumlah Transaksi Hari Ini -->
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Transaksi Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ $transactionToday }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pendapatan Hari Ini -->
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Pendapatan Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    Rp {{ number_format($incomeToday, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($lowStockProducts->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning alert-has-icon">
                            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="alert-body">
                                <div class="alert-title">Stok Menipis!</div>
                                Ada {{ $lowStockProducts->count() }} produk yang sudah mencapai batas minimum stok.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Daftar Stok Menipis</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-md">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Stok Sekarang</th>
                                            <th>Batas Minimum</th>
                                            <th>Aksi</th>
                                        </tr>
                                        @foreach($lowStockProducts as $low)
                                        <tr>
                                            <td>{{ $low->name }}</td>
                                            <td><span class="text-danger font-weight-bold">{{ $low->stock }}</span></td>
                                            <td>{{ $low->min_stock_alert }}</td>
                                            <td><a href="{{ route('admin.batches.index', $low->id) }}" class="btn btn-primary btn-sm">Tambah Batch</a></td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <!-- Produk Terbaru -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Produk Terbaru</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled list-unstyled-border">
                                    @foreach ($latestProducts as $product)
                                        <li class="media">
                                            @foreach ($product->photos as $item)
                                                <img class="mr-3 rounded" width="50"
                                                 src="{{ asset($item->foto ?? 'default.png') }}"
                                                 alt="produk">
                                            @endforeach
                                            <div class="media-body">
                                                <div class="float-right text-primary">
                                                    {{ $product->created_at->diffForHumans() }}
                                                </div>
                                                <div class="media-title">{{ $product->name }}</div>
                                                <span class="text-small text-muted">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection
