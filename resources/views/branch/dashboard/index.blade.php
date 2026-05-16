@extends('master')
@section('title', 'Dashboard Cabang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Dashboard Cabang</h1>
            @if($warehouse)
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><i class="fas fa-warehouse mr-1"></i> {{ $warehouse->name }}</div>
            </div>
            @endif
        </div>
        <div class="section-body">
            @if(!$warehouse)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Akun Anda belum terhubung ke gudang manapun. Hubungi administrator.
            </div>
            @else

            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Stok</h4>
                            </div>
                            <div class="card-body">
                                {{ number_format($totalStock, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pengajuan Pending</h4>
                            </div>
                            <div class="card-body">
                                {{ $pendingRequests }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Penjualan Bulan Ini</h4>
                            </div>
                            <div class="card-body">
                                Rp {{ number_format($monthlySales, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Return Pending</h4>
                            </div>
                            <div class="card-body">
                                {{ $pendingReturns }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Aksi Cepat</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">
                                    <a href="{{ route('branch.stock_requests.create') }}" class="text-decoration-none">
                                        <div class="card bg-primary text-white mb-0">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-paper-plane fa-2x mb-2"></i>
                                                <div class="font-weight-bold">Buat Pengajuan</div>
                                                <small class="opacity-50">Barang ke Pusat</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('branch.sales.create') }}" class="text-decoration-none">
                                        <div class="card bg-success text-white mb-0">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-cash-register fa-2x mb-2"></i>
                                                <div class="font-weight-bold">Input Penjualan</div>
                                                <small class="opacity-50">Harian</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="{{ route('branch.returns.create') }}" class="text-decoration-none">
                                        <div class="card bg-warning text-white mb-0">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-undo-alt fa-2x mb-2"></i>
                                                <div class="font-weight-bold">Buat Return</div>
                                                <small class="opacity-50">Barang ke Pusat</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Info Cabang</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <div class="text-muted small">Nama Gudang</div>
                                    <div class="font-weight-bold">{{ $warehouse->name }}</div>
                                </li>
                                <li class="mb-3">
                                    <div class="text-muted small">Kode</div>
                                    <div class="font-weight-bold">{{ $warehouse->code ?? '-' }}</div>
                                </li>
                                <li>
                                    <div class="text-muted small">Alamat</div>
                                    <div>{{ $warehouse->address ?? '-' }}</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Pengajuan Barang Terbaru</h4>
                            <div class="card-header-action">
                                <a href="{{ route('branch.stock_requests.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr><th>No. Ref</th><th>Tgl</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentRequests as $req)
                                        <tr>
                                            <td><a href="{{ route('branch.stock_requests.show', $req->id) }}"><code>{{ $req->reference_number }}</code></a></td>
                                            <td>{{ $req->created_at->format('d/m/Y') }}</td>
                                            <td>{!! $req->status_label !!}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada pengajuan</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Penjualan Harian Terbaru</h4>
                            <div class="card-header-action">
                                <a href="{{ route('branch.sales.index') }}" class="btn btn-sm btn-success">Lihat Semua</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr><th>No. Ref</th><th>Tanggal</th><th>Total</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentSales as $sale)
                                        <tr>
                                            <td><a href="{{ route('branch.sales.show', $sale->id) }}"><code>{{ $sale->reference_number }}</code></a></td>
                                            <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                                            <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada penjualan</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection
