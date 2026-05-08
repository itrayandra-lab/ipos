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

            #report-table {
                font-size: 13px !important;
                border: none !important;
            }

            #report-table thead th {
                background-color: #f8fafc !important;
                color: #64748b !important;
                font-weight: 600 !important;
                padding: 15px 12px !important;
                border-top: none !important;
            }

            #report-table tbody td {
                padding: 15px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            .qty-badge {
                background-color: #e0f2fe;
                color: #0369a1;
                padding: 4px 10px;
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
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="filter-card">
                            <form id="filter-form">
                                <div class="row align-items-end">
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-600 small text-muted">TANGGAL MULAI</label>
                                        <input type="date" class="form-control form-control-custom" id="start_date" name="start_date" value="{{ date('Y-m-01') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="font-weight-600 small text-muted">TANGGAL SELESAI</label>
                                        <input type="date" class="form-control form-control-custom" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
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
                                         <th>Variant</th>
                                         <th class="text-center">Qty Terjual</th>
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
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { 
                        data: 'product_name', 
                        name: 'product_name',
                        render: function(data, type, row) {
                            return `<div class="font-weight-600">${row.merek_name || ''} ${data}</div>`;
                        }
                    },
                    { 
                        data: 'variant_name', 
                        name: 'variant_name',
                        render: function(data, type, row) {
                            return `<div class="text-muted small">${data || '-'}</div>`;
                        }
                    },
                    { 
                        data: 'total_qty', 
                        name: 'total_qty',
                        className: 'text-center',
                        render: function(data) {
                            return `<span class="qty-badge">${data}</span>`;
                        }
                    },
                    { 
                        data: 'total_amount', 
                        name: 'total_amount',
                        className: 'text-right',
                        render: function(data) {
                            return `<span class="amount-text">${data}</span>`;
                        }
                    }
                ],
                order: [[3, 'desc']] // Order by Qty Terjual
            });

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            window.printReport = function() {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var url = "{{ url('admin/transactions/report/product/print') }}?start_date=" + start_date + "&end_date=" + end_date;
                window.open(url, '_blank');
            };
        });
    </script>
@endsection
