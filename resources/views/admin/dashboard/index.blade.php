@extends('master')
@section('title', 'Dashboard Analisis - ')
@section('content')
<div class="main-content">
    <style>
        /* Premium Dashboard Aesthetic */
        .section-header {
            background: #fff;
            padding: 20px 25px !important;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px !important;
            border-left: 5px solid #0d9488;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-header h1 {
            font-weight: 800 !important;
            color: #1e293b !important;
            margin-bottom: 0;
        }

        /* Summary Cards */
        .summary-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            height: 100%;
            transition: all 0.3s;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }
        .summary-card p {
            color: #64748b;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .summary-card h3 {
            font-size: 24px;
            font-weight: 900;
            color: #1e293b;
            margin: 0;
        }
        .summary-card .trend {
            font-size: 11px;
            font-weight: 700;
            margin-top: 10px;
        }

        /* Chart Containers */
        .chart-container {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            height: 420px; /* Fixed height for consistency */
            display: flex;
            flex-direction: column;
        }
        .chart-content-wrapper {
            flex: 1;
            position: relative;
            min-height: 0;
        }
        .chart-title {
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        /* Stat Card Mini (Payment Status) */
        .stat-card-mini {
            background: #fff;
            border-radius: 16px;
            padding: 15px;
            display: flex;
            align-items: center;
            border: 1px solid #f1f5f9;
            margin-bottom: 15px;
            transition: all 0.2s;
        }
        .stat-card-mini:hover {
            background: #f8fafc;
        }
        .stat-icon-mini {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-right: 12px;
        }
        .bg-soft-success { background: #dcfce7; color: #16a34a; }
        .bg-soft-warning { background: #fef9c3; color: #ca8a04; }
        .bg-soft-danger { background: #fee2e2; color: #dc2626; }
        .bg-soft-info { background: #e0f2fe; color: #0284c7; }

        .progress-thin { height: 6px; border-radius: 3px; }
    </style>

    <section class="section">
        <div class="section-header">
            <div>
                <h1 class="d-inline mr-2">Dashboard Analisis</h1>
                <span class="badge badge-soft-info px-3 py-2" style="border-radius: 20px;">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ $today }}
                </span>
            </div>
            <div class="text-muted small font-weight-700">
                Laporan Performa Bisnis
            </div>
        </div>

        <div class="section-body">
            <!-- Top Row: Analysis Summary -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card">
                        <p>Pendapatan Hari Ini</p>
                        <h3>Rp {{ number_format($incomeToday, 0, ',', '.') }}</h3>
                        <div class="trend text-success"><i class="fas fa-arrow-up"></i> Real-time</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card">
                        <p>Pendapatan Bulan Ini</p>
                        <h3>Rp {{ number_format($incomeMonthly, 0, ',', '.') }}</h3>
                        <div class="trend text-primary"><i class="fas fa-chart-line"></i> Monthly</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card">
                        <p>Avg. Order Value (AOV)</p>
                        <h3>Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</h3>
                        <div class="trend text-info"><i class="fas fa-info-circle"></i> Nilai per-transaksi</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="summary-card">
                        <p>Total User</p>
                        <h3>{{ $userCount }}</h3>
                        <div class="trend text-muted"><i class="fas fa-user-friends"></i> Terdaftar</div>
                    </div>
                </div>
            </div>

            <!-- Middle Row: Sales Trend & Payment Distribution -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="chart-container">
                        <div class="chart-title">
                            <span>Tren Penjualan (30 Hari Terakhir)</span>
                        </div>
                        <div class="chart-content-wrapper">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-container">
                        <div class="chart-title">Metode Pembayaran</div>
                        <div class="chart-content-wrapper">
                            <canvas id="paymentChart"></canvas>
                        </div>
                        <div class="mt-3 overflow-auto" style="max-height: 120px;">
                            @foreach($paymentMethods['labels'] as $index => $label)
                            <div class="d-flex justify-content-between small font-weight-700 mb-2">
                                <span>{{ $label }}</span>
                                <span class="text-primary">{{ $paymentMethods['totals'][$index] }} Order</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Top Products -->
                <div class="col-lg-7">
                    <div class="chart-container">
                        <div class="chart-title">
                            <span>Produk Terlaris</span>
                            <a href="{{ route('admin.transactions.report.product') }}" class="btn btn-sm btn-link text-primary font-weight-bold p-0">Detail Laporan</a>
                        </div>
                        <div class="chart-content-wrapper overflow-auto">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-right">Qty Terjual</th>
                                        <th style="width: 30%">Popularitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $maxQty = $topProducts->max('total_qty') ?: 1; @endphp
                                    @foreach($topProducts as $tp)
                                    <tr>
                                        <td>
                                            <div class="font-weight-700 text-dark">{{ $tp->product->name ?? 'N/A' }}</div>
                                            <div class="text-muted small font-weight-bold">{{ $tp->product->merek->name ?? '' }}</div>
                                        </td>
                                        <td class="text-right font-weight-800">{{ $tp->total_qty }}</td>
                                        <td>
                                            <div class="progress progress-thin mt-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($tp->total_qty / $maxQty) * 100 }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Mini Cards -->
                <div class="col-lg-5">
                    <div class="chart-container">
                        <div class="chart-title">Status Pembayaran Keseluruhan</div>
                        <div class="chart-content-wrapper d-flex flex-column justify-content-between">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="stat-card-mini mb-0">
                                        <div class="stat-icon-mini bg-soft-success">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div>
                                            <div class="small font-weight-700 text-muted">PAID</div>
                                            <div class="font-weight-800 h5 mb-0">{{ $counts['paid'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="stat-card-mini mb-0">
                                        <div class="stat-icon-mini bg-soft-warning">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <div class="small font-weight-700 text-muted">PENDING</div>
                                            <div class="font-weight-800 h5 mb-0">{{ $counts['pending'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="stat-card-mini mb-0">
                                        <div class="stat-icon-mini bg-soft-danger">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </div>
                                        <div>
                                            <div class="small font-weight-700 text-muted">UNPAID</div>
                                            <div class="font-weight-800 h5 mb-0">{{ $counts['unpaid'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="stat-card-mini mb-0">
                                        <div class="stat-icon-mini bg-soft-info">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div>
                                            <div class="small font-weight-700 text-muted">CREDIT</div>
                                            <div class="font-weight-800 h5 mb-0">{{ $counts['credit'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-light rounded mt-auto" style="border-left: 4px solid #0d9488;">
                                <p class="small text-muted mb-0 font-weight-600">
                                    <i class="fas fa-info-circle mr-1 text-primary"></i> Data di atas mencerminkan status transaksi saat ini di seluruh sistem Anda.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($salesChart['labels']) !!},
                datasets: [{
                    label: 'Pendapatan',
                    data: {!! json_encode($salesChart['totals']) !!},
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d9488'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            },
                            font: { weight: 'bold' }
                        },
                        grid: { borderDash: [5, 5], drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Payment Method Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($paymentMethods['labels']) !!},
                datasets: [{
                    data: {!! json_encode($paymentMethods['totals']) !!},
                    backgroundColor: ['#0d9488', '#0ea5e9', '#8b5cf6', '#f59e0b', '#ef4444'],
                    hoverOffset: 4,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { weight: 'bold', size: 11 }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    });
</script>
@endpush
