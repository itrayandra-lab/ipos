@extends('master')
@section('title', 'Laporan Penjualan Per Produk')
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
                margin-bottom: 20px;
                border: 1px solid #e2e8f0;
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

            .stat-icon-sm {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                flex-shrink: 0;
            }

            #report-table {
                font-size: 13px !important;
                border: none !important;
            }

            #report-table thead th {
                background-color: #f8fafc !important;
                color: #64748b !important;
                font-weight: 600 !important;
                padding: 8px 10px !important;
                border-top: none !important;
            }

            #report-table tbody td {
                padding: 8px 10px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            .qty-badge {
                background-color: #e0f2fe;
                color: #0369a1;
                padding: 2px 8px;
                border-radius: 6px;
                font-weight: 700;
            }

            .amount-text {
                font-weight: 700;
                color: #1e293b;
            }

            .btn-premium {
                background: var(--primary-gradient) !important;
                border: none !important;
                border-radius: 8px !important;
                padding: 10px 20px !important;
                font-weight: 700 !important;
                color: #fff !important;
                box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2) !important;
            }

            .form-control-custom {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
                height: 40px !important;
            }
        </style>
        <section class="section">
            <div class="section-header">
                <h1>Laporan Penjualan Per Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transaksi</a></div>
                    <div class="breadcrumb-item">Laporan Per Produk</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Filter Laporan</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-premium btn-sm" onclick="printReport()">
                                <i class="fas fa-print mr-1"></i> Cetak Laporan
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="exportBatch()" style="border-radius:8px;font-weight:700;">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row mb-3">
                            <div class="col-lg-12 mb-3">
                                <div class="stat-card" style="border-left: 4px solid #0d9488; background: linear-gradient(135deg, #f0fdfa, #ccfbf1);">
                                    <div class="stat-icon-sm" style="background:#ccfbf1;">
                                        <i class="fas fa-wallet" style="color:#0d9488;"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="stat-label" style="font-size: 13px; letter-spacing: 1px;font-weight:600;color:#94a3b8;">Total Revenue</div>
                                        <div class="stat-value-rev" id="rev-total" style="font-size: 26px; font-weight: 900;">
                                            Rp {{ number_format($revenue->total ?? 0, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <div class="stat-card" style="border-left: 4px solid #16a34a;">
                                    <div class="stat-icon-sm" style="background:#dcfce7;">
                                        <i class="fas fa-check-circle" style="color:#16a34a;"></i>
                                    </div>
                                    <div class="ml-3" style="flex:1;">
                                        <div class="stat-label" style="font-size:12px;font-weight:600;color:#94a3b8;">Paid</div>
                                        <div class="stat-value-rev" id="rev-paid" style="font-size:17px;font-weight:800;">Rp {{ number_format($revenue->paid ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <div class="stat-card" style="border-left: 4px solid #dc2626;">
                                    <div class="stat-icon-sm" style="background:#fee2e2;">
                                        <i class="fas fa-exclamation-circle" style="color:#dc2626;"></i>
                                    </div>
                                    <div class="ml-3" style="flex:1;">
                                        <div class="stat-label" style="font-size:12px;font-weight:600;color:#94a3b8;">Unpaid</div>
                                        <div class="stat-value-rev" id="rev-unpaid" style="font-size:17px;font-weight:800;">Rp {{ number_format($revenue->unpaid ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <div class="stat-card" style="border-left: 4px solid #ca8a04;">
                                    <div class="stat-icon-sm" style="background:#fef9c3;">
                                        <i class="fas fa-clock" style="color:#ca8a04;"></i>
                                    </div>
                                    <div class="ml-3" style="flex:1;">
                                        <div class="stat-label" style="font-size:12px;font-weight:600;color:#94a3b8;">Pending</div>
                                        <div class="stat-value-rev" id="rev-pending" style="font-size:17px;font-weight:800;">Rp {{ number_format($revenue->pending ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <div class="stat-card" style="border-left: 4px solid #0284c7;">
                                    <div class="stat-icon-sm" style="background:#e0f2fe;">
                                        <i class="fas fa-credit-card" style="color:#0284c7;"></i>
                                    </div>
                                    <div class="ml-3" style="flex:1;">
                                        <div class="stat-label" style="font-size:12px;font-weight:600;color:#94a3b8;">Credit / DP</div>
                                        <div class="stat-value-rev" id="rev-credit" style="font-size:17px;font-weight:800;">Rp {{ number_format($revenue->credit ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-card">
                            <form id="filter-form">
                                <div class="row align-items-end">
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-600 small text-muted">TANGGAL MULAI</label>
                                        <input type="date" class="form-control form-control-custom" id="start_date" name="start_date" value="{{ date('Y-m-01') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-600 small text-muted">TANGGAL SELESAI</label>
                                        <input type="date" class="form-control form-control-custom" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-600 small text-muted">CHANNEL</label>
                                        <select class="form-control form-control-custom" id="source" name="source">
                                            <option value="">Semua Channel</option>
                                            @foreach($sources as $src)
                                                <option value="{{ $src }}">{{ ucfirst($src) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button type="submit" class="btn btn-primary btn-block" style="height: 40px; border-radius: 8px; font-weight: 700;">
                                            <i class="fas fa-sync-alt mr-1"></i> Update Laporan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="report-table">
                                <thead>
                                     <tr>
                                         <th width="10px">#</th>
                                         <th>Nama Produk</th>
                                         <th class="text-center">Qty Terjual</th>
                                         <th class="text-right">HPP Satuan</th>
                                         <th class="text-right">Total HPP</th>
                                         <th class="text-right">Harga Jual</th>
                                         <th class="text-right">Total Penjualan</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                 </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#report-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/transactions/report/product/all') }}",
                    type: "GET",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.source = $('#source').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { 
                        data: 'product_name', 
                        name: 'product_name',
                        render: function(data, type, row) {
                            let label = '';
                            if (row.variant_name) {
                                label = (row.merek_name || '') + ' ' + row.variant_name;
                            } else {
                                label = (row.merek_name || '') + ' ' + data;
                            }
                            return `<div class="font-weight-600">${label}</div>`;
                        }
                    },
                    { 
                        data: 'total_qty', 
                        name: 'total_qty',
                        className: 'text-center',
                        searchable: false,
                        render: function(data) {
                            return `<span class="qty-badge">${data}</span>`;
                        }
                    },
                    { 
                        data: 'product_hpp', 
                        name: 'product_hpp',
                        className: 'text-right',
                        searchable: false,
                        render: function(data) {
                            return `<span class="amount-text" style="color:#64748b;">${data}</span>`;
                        }
                    },
                    { 
                        data: 'total_hpp', 
                        name: 'total_hpp',
                        className: 'text-right',
                        searchable: false,
                        render: function(data) {
                            return `<span class="amount-text" style="color:#64748b;">${data}</span>`;
                        }
                    },
                    { 
                        data: 'selling_price', 
                        name: 'selling_price',
                        className: 'text-right',
                        searchable: false,
                        render: function(data) {
                            return `<span class="amount-text">${data}</span>`;
                        }
                    },
                    { 
                        data: 'total_amount', 
                        name: 'total_amount',
                        className: 'text-right',
                        searchable: false,
                        render: function(data) {
                            return `<span class="amount-text">${data}</span>`;
                        }
                    }
                ],
                order: [[2, 'desc']] // Order by Qty Terjual
            });

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
                loadRevenue();
            });

            function loadRevenue() {
                $.ajax({
                    url: '{{ route("admin.transactions.revenue") }}',
                    type: 'GET',
                    data: {
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                        source: $('#source').val()
                    },
                    success: function(res) {
                        $('#rev-total').text('Rp ' + parseInt(res.total || 0).toLocaleString('id-ID'));
                        $('#rev-paid').text('Rp ' + parseInt(res.paid || 0).toLocaleString('id-ID'));
                        $('#rev-unpaid').text('Rp ' + parseInt(res.unpaid || 0).toLocaleString('id-ID'));
                        $('#rev-pending').text('Rp ' + parseInt(res.pending || 0).toLocaleString('id-ID'));
                        $('#rev-credit').text('Rp ' + parseInt(res.credit || 0).toLocaleString('id-ID'));
                    }
                });
            }

            window.printReport = function() {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var source = $('#source').val();
                var url = "{{ url('admin/transactions/report/product/print') }}?start_date=" + start_date + "&end_date=" + end_date + "&source=" + source;
                window.open(url, '_blank');
            };

            window.exportBatch = function() {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var url = "{{ url('admin/transactions/report/product/export-batch') }}?start_date=" + start_date + "&end_date=" + end_date;
                window.location.href = url;
            };
        });
    </script>
@endsection
