@extends('master')
@section('title', 'Laporan Pelunasan Pabrik')
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
                color: #b91c1c; /* Red for cost/liability */
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
                <h1>Laporan Pelunasan Pabrik (HPP)</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="#">Finance</a></div>
                    <div class="breadcrumb-item">Pelunasan Pabrik</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Filter Laporan</h4>
                        <div class="card-header-form">
                            <div class="btn-group">
                                <a href="#" id="btn-export-excel" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>
                                <a href="#" id="btn-export-pdf" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>
                            </div>
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
                            <table class="table table-striped" id="report-table" width="100%">
                                <thead>
                                     <tr>
                                         <th width="10px">#</th>
                                         <th>Nama Produk</th>
                                         <th class="text-right">HPP Satuan</th>
                                         <th class="text-center">Total Terjual</th>
                                         <th class="text-right">Total</th>
                                         <th width="80px" class="text-center">Aksi</th>
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
                    url: "{{ route('admin.finance.settlement.data') }}",
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
                        name: 'product_name'
                    },
                    { 
                        data: 'buy_price', 
                        name: 'buy_price',
                        className: 'text-right'
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
                        data: 'total_cost', 
                        name: 'total_cost',
                        className: 'text-right',
                        render: function(data) {
                            return `<span class="amount-text">${data}</span>`;
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[3, 'desc']] // Order by Qty Terjual
            });

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
                updateExportUrls();
            });

            function updateExportUrls() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const params = `?start_date=${startDate}&end_date=${endDate}`;
                
                $('#btn-export-excel').attr('href', "{{ route('admin.finance.settlement.export.excel') }}" + params);
                $('#btn-export-pdf').attr('href', "{{ route('admin.finance.settlement.export.pdf') }}" + params);
            }

            // Initial URL update
            updateExportUrls();

            $(document).on('click', '.btn-detail', function() {
                const productId = $(this).data('product-id');
                const variantId = $(this).data('variant-id');
                const productName = $(this).data('product-name');
                const variantName = $(this).data('variant-name');
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                $('#detailModalLabel').text(`Detail Transaksi: ${productName} ${variantName ? '(' + variantName + ')' : ''}`);
                $('#detail-tbody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                $('#detailModal').modal('show');

                $.ajax({
                    url: "{{ route('admin.finance.settlement.detail') }}",
                    method: "GET",
                    data: {
                        product_id: productId,
                        variant_id: variantId,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        let html = '';
                        if (response.length === 0) {
                            html = '<tr><td colspan="6" class="text-center">Tidak ada transaksi ditemukan.</td></tr>';
                        } else {
                            response.forEach((item, index) => {
                                const date = new Date(item.created_at).toLocaleString('id-ID', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                html += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>
                                            <div class="font-weight-600 text-primary">${item.transaction_code || '#' + item.id}</div>
                                            <div class="small text-muted">${item.invoice_number || ''}</div>
                                        </td>
                                        <td>${date}</td>
                                        <td class="text-center">${item.qty}</td>
                                        <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.buy_price)}</td>
                                        <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#detail-tbody').html(html);
                    },
                    error: function() {
                        $('#detail-tbody').html('<tr><td colspan="6" class="text-center text-danger">Gagal mengambil data.</td></tr>');
                    }
                });
            });
        });
    </script>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Kode Transaksi</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">HPP Satuan</th>
                                    <th class="text-right">Total HPP</th>
                                </tr>
                            </thead>
                            <tbody id="detail-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
