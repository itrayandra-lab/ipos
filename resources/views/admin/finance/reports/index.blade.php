@extends('master')
@section('title', 'Laporan Keuangan')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Laporan Arus Kas & Laba Rugi</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item">Reports</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Filter Laporan</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <div class="col-md-4">
                                    <label>Dari Tanggal</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date', date('Y-m-01')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label>Sampai Tanggal</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary mr-2">Tampilkan</button>
                                    <button type="button" class="btn btn-success" onclick="window.print()">Cetak</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $start = request('start_date', date('Y-m-01'));
                $end = request('end_date', date('Y-m-d'));
                
                $income = \App\Models\Transaction::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                            ->where('payment_status', 'paid')
                            ->sum('total_amount');
                
                $expenses = \App\Models\Expense::whereBetween('transaction_date', [$start, $end])
                            ->sum('amount');
                            
                $net = $income - $expenses;
            @endphp

            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Pendapatan (Lunas)</h4>
                            </div>
                            <div class="card-body">
                                Rp {{ number_format($income, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Pengeluaran</h4>
                            </div>
                            <div class="card-body">
                                Rp {{ number_format($expenses, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-statistic-1">
                        <div class="card-icon {{ $net >= 0 ? 'bg-primary' : 'bg-warning' }}">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Laba/Rugi Bersih</h4>
                            </div>
                            <div class="card-body">
                                Rp {{ number_format($net, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Rincian Pengeluaran per Kategori</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $categoryExpenses = \App\Models\Expense::with('category')
                                    ->whereBetween('transaction_date', [$start, $end])
                                    ->select('expense_category_id', \DB::raw('SUM(amount) as total'))
                                    ->groupBy('expense_category_id')
                                    ->get();
                            @endphp
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryExpenses as $ce)
                                    <tr>
                                        <td>{{ $ce->category->name ?? 'Tanpa Kategori' }}</td>
                                        <td class="text-right">Rp {{ number_format($ce->total, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
