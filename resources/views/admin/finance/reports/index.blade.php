@extends('master')
@section('title', 'Laporan Penjualan')
@section('content')
<div class="main-content">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        }
        .section-header {
            background: #fff;
            padding: 20px 25px !important;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px !important;
            border-left: 5px solid #0d9488;
        }
        .section-header h1 {
            font-weight: 800 !important;
            color: #1e293b !important;
            letter-spacing: -0.5px;
        }
        .card {
            border-radius: 15px !important;
            border: none !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
        }
        .card-header {
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 20px 25px !important;
        }
        .card-header h4 {
            color: #0d9488 !important;
            font-weight: 700 !important;
        }
        .filter-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 0;
            border: 1px solid #e2e8f0;
        }
        .form-label {
            font-weight: 600;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .form-control-custom {
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            height: 40px !important;
        }
        .btn-premium {
            background: var(--primary-gradient) !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 8px 15px !important;
            font-weight: 700 !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2) !important;
        }
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            transition: transform 0.2s;
            border: 1px solid #f1f5f9;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
        }
        .stat-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .stat-value {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
        }
        .stat-value-sm {
            font-size: 17px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
        }
        .bg-soft-success { background: #dcfce7; color: #16a34a; }
        .bg-soft-warning { background: #fef9c3; color: #ca8a04; }
        .bg-soft-danger { background: #fee2e2; color: #dc2626; }
        .bg-soft-info { background: #e0f2fe; color: #0284c7; }
        .bg-soft-primary { background: #e0e7ff; color: #4338ca; }
        .bg-soft-teal { background: #ccfbf1; color: #0d9488; }
        .bg-soft-secondary { background: #f1f5f9; color: #64748b; }

        table {
            font-size: 13px !important;
            border: none !important;
        }
        table thead th {
            background-color: #f8fafc !important;
            color: #64748b !important;
            text-transform: none !important;
            font-weight: 600 !important;
            padding: 12px 15px !important;
            border-top: none !important;
            letter-spacing: 0.3px;
        }
        table tbody td {
            padding: 12px 15px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        table tbody tr:hover td {
            background-color: #f8fafc;
        }
        .amount-text {
            font-weight: 700;
            color: #1e293b;
            font-size: 14px;
        }
    </style>
    <section class="section">
        <div class="section-header">
            <h1>Laporan Penjualan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item">Reports</div>
                <div class="breadcrumb-item">Penjualan</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Filter Laporan</h4>
                </div>
                <div class="card-body">
                    <div class="filter-card">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="form-label">Dari Tanggal</div>
                                <input type="date" name="start_date" class="form-control form-control-custom" value="{{ $start->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="form-label">Sampai Tanggal</div>
                                <input type="date" name="end_date" class="form-control form-control-custom" value="{{ $end->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-premium btn-block">
                                    <i class="fas fa-eye mr-1"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-12 mb-3">
                    <div class="stat-card" style="border-left: 4px solid #0d9488; background: linear-gradient(135deg, #f0fdfa, #ccfbf1); padding: 20px 24px;">
                        <div class="stat-icon bg-soft-teal" style="width: 52px; height: 52px; font-size: 24px; border-radius: 14px;">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div>
                            <div class="stat-label" style="font-size: 13px; letter-spacing: 1px;">Gross Sales</div>
                            <div class="stat-value" style="font-size: 26px; font-weight: 900;">Rp {{ number_format($grossSales, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="stat-card" style="border-left: 4px solid #ca8a04; padding: 18px 20px;">
                        <div class="stat-icon bg-soft-warning" style="width: 44px; height: 44px; font-size: 18px;">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div style="flex:1;">
                            <div class="stat-label">Diskon & Retur</div>
                            <div class="stat-value-sm">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="stat-card" style="border-left: 4px solid #0284c7; padding: 18px 20px;">
                        <div class="stat-icon bg-soft-info" style="width: 44px; height: 44px; font-size: 18px;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div style="flex:1;">
                            <div class="stat-label">Net Sales</div>
                            <div class="stat-value-sm">Rp {{ number_format($netSales, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="stat-card" style="border-left: 4px solid #64748b; padding: 18px 20px;">
                        <div class="stat-icon bg-soft-secondary" style="width: 44px; height: 44px; font-size: 18px;">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div style="flex:1;">
                            <div class="stat-label">COGS (HPP)</div>
                            <div class="stat-value-sm">Rp {{ number_format($cogs, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="stat-card" style="border-left: 4px solid {{ $grossProfit >= 0 ? '#4338ca' : '#dc2626' }}; padding: 18px 20px;">
                        <div class="stat-icon {{ $grossProfit >= 0 ? 'bg-soft-primary' : 'bg-soft-danger' }}" style="width: 44px; height: 44px; font-size: 18px;">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div style="flex:1;">
                            <div class="stat-label">Gross Profit</div>
                            <div class="stat-value-sm">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Ringkasan per Saluran</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Saluran</th>
                                        <th class="text-right">Transaksi</th>
                                        <th class="text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($channelSummary as $c)
                                    <tr>
                                        <td>{{ ucfirst($c->source) }}</td>
                                        <td class="text-right">{{ $c->total_tx }}</td>
                                        <td class="text-right amount-text">Rp {{ number_format($c->total_revenue, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center">Tidak ada data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Ringkasan per Hari</h4>
                        </div>
                        <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th class="text-right">Transaksi</th>
                                        <th class="text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dailySummary as $d)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($d->date)->isoFormat('D MMM YYYY') }}</td>
                                        <td class="text-right">{{ $d->total_tx }}</td>
                                        <td class="text-right amount-text">Rp {{ number_format($d->total_revenue, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center">Tidak ada data</td></tr>
                                    @endforelse
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
