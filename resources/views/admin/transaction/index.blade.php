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
                letter-spacing: 0.3px;
            }

            #transaction-table tbody td {
                padding: 15px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            #transaction-table tbody tr:hover td {
                background-color: #f8fafc;
            }

            /* Status Badges */
            .badge-soft {
                padding: 4px 10px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 10px;
                display: inline-flex;
                align-items: center;
                letter-spacing: 0.3px;
            }
            .badge-soft-success { background-color: #dcfce7; color: #15803d; }
            .badge-soft-warning { background-color: #fef9c3; color: #854d0e; }
            .badge-soft-danger { background-color: #fee2e2; color: #b91c1c; }
            .badge-soft-info { background-color: #e0f2fe; color: #0369a1; }
            .badge-soft-secondary { background-color: #f1f5f9; color: #475569; }

            .amount-text {
                font-weight: 700;
                color: #1e293b;
                font-size: 14px;
            }

            .cell-merged {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .cell-merged .label-sub {
                font-size: 11px;
                color: #94a3b8;
            }

            .btn-action-custom {
                border-radius: 6px !important;
                font-weight: 600 !important;
                padding: 4px 12px !important;
                background-color: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                color: #475569 !important;
                font-size: 12px !important;
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
                            <a href="{{ route('admin.online_sale.create') }}" class="btn btn-premium btn-sm mr-2">
                                <i class="fas fa-plus mr-1"></i> Tambah Penjualan Platform
                            </a>
                            <a href="{{ route('admin.transactions.report.product') }}" class="btn btn-info btn-sm mr-2" style="border-radius: 8px; font-weight: 700;">
                                <i class="fas fa-chart-line mr-1"></i> Laporan Per Produk
                            </a>
                            <button class="btn btn-success btn-sm mr-2" style="border-radius: 8px; font-weight: 700;" onclick="showImportModal()">
                                <i class="fas fa-file-upload mr-1"></i> Import Excel
                            </button>
                            <div class="dropdown d-inline">
                                <button class="btn btn-premium btn-sm dropdown-toggle" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-file-export mr-1"></i> Cetak Laporan
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="printData()">
                                        <i class="fas fa-print mr-2 text-primary"></i> Print Browser
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="exportExcel()">
                                        <i class="fas fa-file-excel mr-2 text-success"></i> Export Excel
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="exportPdf()">
                                        <i class="fas fa-file-pdf mr-2 text-danger"></i> Export PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="filter-card">
                            <form id="filter-form">
                                <div class="row align-items-end">
                                    <div class="col-md-2 mb-3">
                                        <label for="warehouse_filter" class="form-label">Cabang</label>
                                        <select class="form-control form-control-custom" id="warehouse_filter" name="warehouse_id">
                                            <option value="">Semua Cabang</option>
                                            @foreach($warehouses as $wh)
                                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label for="source_filter" class="form-label">Saluran</label>
                                        <select class="form-control form-control-custom" id="source_filter" name="source">
                                            <option value="">Semua Saluran</option>
                                            <option value="offline">Offline</option>
                                            @foreach(\App\Models\ChannelSetting::orderBy('name')->get() as $channel)
                                                <option value="{{ $channel->slug }}">{{ $channel->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
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
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-premium table-hover mb-0" id="transaction-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="15%">ID Transaksi</th>
                                        <th width="15%">Kasir / Admin</th>
                                        <th width="15%">Total & Status</th>
                                        <th width="15%">Saluran & Gudang</th>
                                        <th width="10%">Pengiriman</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="10%" class="text-center">Aksi</th>
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

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel"><i class="fas fa-file-upload mr-2"></i>Import Transaksi dari Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="import-form" action="{{ route('admin.transactions.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Format file: <strong>.xlsx, .xls</strong> (maks 5MB).<br>
                            Template terdiri dari 3 sheet:<br>
                            <strong>Transaksi</strong> — isi data penjualan (kolom product_name pakai dropdown dari sheet Referensi Produk)<br>
                            <strong>Referensi Produk</strong> — daftar produk (otomatis, hanya untuk patokan)<br>
                            <strong>Referensi Pelanggan</strong> — daftar pelanggan (otomatis, hanya untuk patokan)
                        </div>
                        <div class="form-group">
                            <label>Pilih File Excel</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="import-file" name="file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="import-file">Pilih file...</label>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.transactions.import.template') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download mr-1"></i> Download Template Excel (Multi Sheet)
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" id="btn-import">
                            <i class="fas fa-upload mr-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
                        d.warehouse_id = $('#warehouse_filter').val();
                        d.delivery_type = $('#delivery_type').val();
                        d.payment_status = $('#payment_status').val();
                        d.source = $('#source_filter').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { 
                        data: 'transaction_code', 
                        name: 'transaction_code',
                        render: function(data, type, row) {
                            let code = data || '#'+row.id;
                            return `<span style="font-family:monospace;font-size:12px;font-weight:600;color:#475569">${code}</span>`;
                        }
                    },
                    { data: 'user.name', name: 'user.name' },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data, type, row) {
                            let cls = 'badge-soft-secondary';
                            let label = (row.payment_status || '').toUpperCase();
                            if(row.payment_status == 'paid' || row.payment_status == 'completed') cls = 'badge-soft-success';
                            if(row.payment_status == 'pending' || row.payment_status == 'credit') cls = 'badge-soft-warning';
                            if(row.payment_status == 'canceled' || row.payment_status == 'failed') cls = 'badge-soft-danger';
                            return `<div class="cell-merged"><span class="amount-text">${data}</span><span class="badge-soft ${cls}" style="align-self:flex-start">${label}</span></div>`;
                        }
                    },
                    {
                        data: 'source',
                        name: 'source',
                        render: function(data, type, row) {
                            if (!data || data == 'offline') return '<span class="badge badge-light">Offline</span>';
                            let colors = {
                                'shopee': 'badge-soft-warning',
                                'tokopedia': 'badge-soft-success',
                                'tiktok': 'badge-soft-secondary',
                                'whatsapp': 'badge-soft-info'
                            };
                            let cls = colors[data.toLowerCase()] || 'badge-soft-info';
                            let wh = row.warehouse_name || '';
                            let sourceHtml = `<span class="badge-soft ${cls}">${data.toUpperCase()}</span>`;
                            if (wh) {
                                return `<div class="cell-merged">${sourceHtml}<span class="label-sub">${wh}</span></div>`;
                            }
                            return sourceHtml;
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
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            // Handle filter form submission
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Reset filter
            window.resetFilter = function() {
                $('#warehouse_filter').val('');
                $('#delivery_type').val('');
                $('#payment_status').val('');
                $('#source_filter').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                table.draw();
            };

            // Print data
            window.printData = function() {
                var url = "{{ route('admin.transactions.index') }}/print?" + $('#filter-form').serialize();
                window.open(url, '_blank');
            };

            // Export Excel
            window.exportExcel = function() {
                var url = "{{ route('admin.transactions.export.excel') }}?" + $('#filter-form').serialize();
                window.location.href = url;
            };

            // Export PDF
            window.exportPdf = function() {
                var url = "{{ route('admin.transactions.export.pdf') }}?" + $('#filter-form').serialize();
                window.location.href = url;
            };

            // Import Modal
            window.showImportModal = function() {
                $('#importModal').modal('show');
            };

            // Custom file input
            $('#import-file').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });

            // Import form submit
            $('#import-form').on('submit', function(e) {
                var fileInput = $('#import-file')[0];
                if (!fileInput.files || !fileInput.files[0]) {
                    e.preventDefault();
                    if (typeof iziToast !== "undefined") {
                        iziToast.error({ title: 'Error', message: 'Pilih file terlebih dahulu', position: 'topRight' });
                    } else {
                        alert('Pilih file terlebih dahulu');
                    }
                    return;
                }
                $('#btn-import').addClass('btn-progress').attr('disabled', true);
            });

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