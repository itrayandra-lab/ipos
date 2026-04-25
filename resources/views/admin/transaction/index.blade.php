@extends('master')
@section('title', 'Data Transaksi')
@section('content')
    <div class="main-content">
        <style>
            /* Premium Aesthetic Enhancements */
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

            #transaction-table {
                font-size: 13px !important;
                border: none !important;
            }

            #transaction-table thead th {
                background-color: #f8fafc !important;
                color: #64748b !important;
                text-transform: none !important;
                font-weight: 600 !important;
                padding: 15px 12px !important;
                border-top: none !important;
            }

            #transaction-table tbody td {
                padding: 15px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            /* Status Badges */
            .badge-soft {
                padding: 6px 12px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 11px;
                display: inline-flex;
                align-items: center;
            }
            .badge-soft-success { background-color: #dcfce7; color: #15803d; }
            .badge-soft-warning { background-color: #fef9c3; color: #854d0e; }
            .badge-soft-danger { background-color: #fee2e2; color: #b91c1c; }
            .badge-soft-info { background-color: #e0f2fe; color: #0369a1; }
            .badge-soft-secondary { background-color: #f1f5f9; color: #475569; }

            .badge-soft::before {
                content: "";
                width: 6px;
                height: 6px;
                border-radius: 50%;
                margin-right: 6px;
                background: currentColor;
            }

            .amount-text {
                font-weight: 700;
                color: #1e293b;
            }

            .btn-action-custom {
                border-radius: 8px !important;
                font-weight: 600 !important;
                padding: 5px 15px !important;
                background-color: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                color: #475569 !important;
            }
            .btn-action-custom:hover {
                background-color: #f1f5f9 !important;
                color: #1e293b !important;
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
        </style>
        <section class="section">
            <div class="section-header">
                <h1>Data Transaksi</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Transaksi</div>
                </div>
            </div>

            <div class="section-body">
                <!-- Redundant titles removed -->
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
                <style>
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
                    .bg-soft-success { background: #dcfce7; color: #16a34a; }
                    .bg-soft-warning { background: #fef9c3; color: #ca8a04; }
                    .bg-soft-danger { background: #fee2e2; color: #dc2626; }
                    .bg-soft-info { background: #e0f2fe; color: #0284c7; }
                </style>

                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <div class="stat-label">Total Paid</div>
                                <div class="stat-value">{{ $counts['paid'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div class="stat-label">Pending</div>
                                <div class="stat-value">{{ $counts['pending'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-danger">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div>
                                <div class="stat-label">Unpaid</div>
                                <div class="stat-value">{{ $counts['unpaid'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-soft-info">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <div class="stat-label">Credit (DP)</div>
                                <div class="stat-value">{{ $counts['credit'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Filter Transaksi</h4>
                        <div class="card-header-form">
                            <a href="{{ route('admin.transactions.report.product') }}" class="btn btn-info btn-sm mr-2" style="border-radius: 8px; font-weight: 700;">
                                <i class="fas fa-chart-line mr-1"></i> Laporan Per Produk
                            </a>
                            <button type="button" class="btn btn-premium btn-sm" onclick="printData()">
                                <i class="fas fa-print mr-1"></i> Cetak Laporan
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="filter-card">
                            <form id="filter-form">
                                <div class="row align-items-end">
                                    <div class="col-md-3 mb-3">
                                        <label for="delivery_type" class="form-label">Tipe Pengiriman</label>
                                        <select class="form-control form-control-custom" id="delivery_type" name="delivery_type">
                                            <option value="">Semua Tipe</option>
                                            <option value="pickup">Pickup</option>
                                            <option value="delivery">Delivery</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="payment_status" class="form-label">Status Pembayaran</label>
                                        <select class="form-control form-control-custom" id="payment_status" name="payment_status">
                                            <option value="">Semua Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid / Lunas</option>
                                            <option value="credit">Credit / Piutang</option>
                                            <option value="canceled">Canceled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control form-control-custom" id="start_date" name="start_date">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                                        <input type="date" class="form-control form-control-custom" id="end_date" name="end_date">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <button type="submit" class="btn btn-primary btn-block" style="height: 40px; border-radius: 8px; font-weight: 700;">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <a href="javascript:void(0)" onclick="resetFilter()" class="text-muted small" style="text-decoration: underline;">Reset Filter</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5" id="transaction-table">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>User / Admin</th>
                                    <th>Total Transaksi</th>
                                    <th>Status Pembayaran</th>
                                    <th>Pengiriman</th>
                                    <th>Tanggal Transaksi</th>
                                    <th width="10px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            // DataTable initialization
            var table = $('#transaction-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/transactions/all') }}",
                    type: "GET",
                    data: function(d) {
                        d.delivery_type = $('#delivery_type').val();
                        d.payment_status = $('#payment_status').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'user.name', name: 'user.name' },
                    { 
                        data: 'total_amount', 
                        name: 'total_amount',
                        render: function(data, type, row) {
                            return `<span class="amount-text">${data}</span>`;
                        }
                    },
                    { 
                        data: 'payment_status', 
                        name: 'payment_status',
                        render: function(data, type, row) {
                            let cls = 'badge-soft-secondary';
                            let text = data.toUpperCase();
                            if(data == 'paid' || data == 'completed') cls = 'badge-soft-success';
                            if(data == 'pending' || data == 'credit') cls = 'badge-soft-warning';
                            if(data == 'canceled' || data == 'failed') cls = 'badge-soft-danger';
                            return `<span class="badge-soft ${cls}">${text}</span>`;
                        }
                    },
                    { 
                        data: 'delivery_type', 
                        name: 'delivery_type',
                        render: function(data, type, row) {
                            let cls = 'badge-soft-info';
                            if(data == 'pickup') cls = 'badge-soft-info';
                            if(data == 'delivery') cls = 'badge-soft-secondary';
                            return `<span class="badge-soft ${cls}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                        }
                    },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Handle filter form submission
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Reset filter
            window.resetFilter = function() {
                $('#delivery_type').val('');
                $('#payment_status').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                table.draw();
            };

            // Print data
            window.printData = function() {
                var delivery_type = $('#delivery_type').val();
                var payment_status = $('#payment_status').val();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var url = "{{ url('admin/transactions/print') }}?delivery_type=" + delivery_type +
                          "&payment_status=" + payment_status +
                          "&start_date=" + start_date +
                          "&end_date=" + end_date;
                window.open(url, '_blank');
            };

            // Delete transaction
            window.deleteTransaction = function(id) {
                if (confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.')) {
                    $.ajax({
                        url: "{{ url('admin/transactions') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                if (typeof iziToast !== "undefined") {
                                    iziToast.success({
                                        title: 'Berhasil',
                                        message: 'Transaksi berhasil dihapus',
                                        position: 'topRight'
                                    });
                                } else {
                                    alert('Transaksi berhasil dihapus');
                                }
                            } else {
                                if (typeof iziToast !== "undefined") {
                                    iziToast.error({
                                        title: 'Gagal',
                                        message: response.message || 'Terjadi kesalahan saat menghapus transaksi',
                                        position: 'topRight'
                                    });
                                } else {
                                    alert('Gagal: ' + (response.message || 'Terjadi kesalahan'));
                                }
                            }
                        },
                        error: function() {
                            if (typeof iziToast !== "undefined") {
                                iziToast.error({
                                    title: 'Gagal',
                                    message: 'Terjadi kesalahan sistem',
                                    position: 'topRight'
                                });
                            } else {
                                alert('Terjadi kesalahan sistem');
                            }
                        }
                    });
                }
            };

            // Generate Invoice
            window.generateInvoice = function(id) {
                if (confirm('Apakah Anda yakin ingin membuat nomor invoice formal untuk transaksi ini?')) {
                    $.ajax({
                        url: "{{ url('admin/transactions/generate-invoice') }}/" + id,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend: function() {
                            $.LoadingOverlay("show");
                        },
                        complete: function() {
                            $.LoadingOverlay("hide");
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                iziToast.success({
                                    title: 'Berhasil',
                                    message: response.message,
                                    position: 'topRight'
                                });
                                // Automatically open print view for the new invoice
                                var printUrl = "{{ route('admin.sales.invoices.print', ':id') }}".replace(':id', id);
                                window.open(printUrl, '_blank');
                            } else {
                                iziToast.error({
                                    title: 'Gagal',
                                    message: response.message,
                                    position: 'topRight'
                                });
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                            iziToast.error({
                                title: 'Gagal',
                                message: msg,
                                position: 'topRight'
                            });
                        }
                    });
                }
            };
        });
    </script>
@endsection