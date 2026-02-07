@extends('master')
@section('title', 'Detail Affiliate')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Affiliate</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('admin.affiliates.index') }}">Affiliate User</a></div>
                    <div class="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Transaksi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalTransactions }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Komisi</h4>
                                </div>
                                <div class="card-body">
                                    Rp {{ number_format($totalCommissions) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Tipe Affiliate</h4>
                                </div>
                                <div class="card-body">
                                    {{ $affiliate->type ? $affiliate->type->name : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Riwayat Transaksi Affiliate: {{ $affiliate->name }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="trx-affiliate-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Tanggal</th>
                                                <th>Customer</th>
                                                <th>Total Transaksi</th>
                                                <th>Estimasi Komisi</th>
                                                <th>Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transactions as $trx)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    {{ $trx->customer_name }}<br>
                                                    <small class="text-muted">{{ $trx->customer_phone }}</small>
                                                </td>
                                                <td class="font-weight-bold">Rp {{ number_format($trx->total_amount) }}</td>
                                                <td class="text-success font-weight-bold">Rp {{ number_format($trx->affiliate_fee_total) }}</td>
                                                <td>
                                                    <a href="{{ url('admin/transactions/show/'.$trx->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                                </td>
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
