@extends('master')
@section('title', 'Riwayat Pembayaran Supplier')
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

            #payment-table {
                font-size: 13px !important;
                border: none !important;
            }

            #payment-table thead th {
                background-color: #f8fafc !important;
                color: #64748b !important;
                font-weight: 600 !important;
                padding: 15px 12px !important;
                border-top: none !important;
            }

            #payment-table tbody td {
                padding: 15px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            .form-control-custom {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
                height: 40px !important;
            }

            .amount-text {
                font-weight: 700;
                color: #b91c1c;
            }
        </style>
        <section class="section">
            <div class="section-header">
                <h1>Riwayat Pembayaran Supplier</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="#">Finance</a></div>
                    <div class="breadcrumb-item">Riwayat Pembayaran</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Filter Riwayat</h4>
                        <div class="card-header-form">
                            <a href="{{ route('admin.finance.settlement.index') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Pelunasan
                            </a>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="filter-card">
                            <form id="filter-form">
                                <div class="row align-items-end">
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-600 small text-muted">TANGGAL MULAI</label>
                                        <input type="date" class="form-control form-control-custom" id="start_date" name="start_date">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-600 small text-muted">TANGGAL SELESAI</label>
                                        <input type="date" class="form-control form-control-custom" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-600 small text-muted">SUPPLIER / PABRIK</label>
                                        <select class="form-control select2" id="supplier_id" name="supplier_id">
                                            <option value="">Semua Supplier</option>
                                            @foreach($suppliers as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button type="submit" class="btn btn-primary btn-block" style="height: 40px; border-radius: 8px; font-weight: 700;">
                                            <i class="fas fa-sync-alt mr-1"></i> Tampilkan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="payment-table" width="100%">
                                <thead>
                                    <tr>
                                        <th width="10px">#</th>
                                        <th>Supplier</th>
                                        <th>Tanggal Bayar</th>
                                        <th class="text-center">Total Qty</th>
                                        <th class="text-right">Total Nominal</th>
                                        <th>Petugas</th>
                                        <th>Bukti</th>
                                        <th>Catatan</th>
                                        <th width="70px" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="detailPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3" id="payment-info-header">
                        <div class="col-md-4"><strong>Supplier:</strong> <span id="detail-supplier"></span></div>
                        <div class="col-md-4"><strong>Tanggal:</strong> <span id="detail-date"></span></div>
                        <div class="col-md-4"><strong>Petugas:</strong> <span id="detail-cashier"></span></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">HPP Satuan</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detail-items-tbody"></tbody>
                            <tfoot>
                                <tr style="font-weight: bold;">
                                    <td colspan="4" class="text-right">TOTAL</td>
                                    <td class="text-right" id="detail-total"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#payment-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.finance.settlement.payment_history_data') }}",
                    type: "GET",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.supplier_id = $('#supplier_id').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'supplier_name', name: 'supplier_name' },
                    { data: 'payment_date', name: 'payment_date' },
                    { 
                        data: 'total_qty', name: 'total_qty', className: 'text-center',
                        render: function(data) {
                            return data || 0;
                        }
                    },
                    { data: 'total_amount', name: 'total_amount', className: 'text-right' },
                    { data: 'cashier_name', name: 'cashier_name', defaultContent: '-' },
                    { data: 'payment_proof_link', name: 'payment_proof_link', className: 'text-center', orderable: false },
                    { data: 'notes', name: 'notes', defaultContent: '-',
                        render: function(data) {
                            return data ? '<small>' + data + '</small>' : '-';
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[2, 'desc']]
            });

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            $(document).on('click', '.btn-view-payment', function() {
                const id = $(this).data('id');
                $('#detail-items-tbody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
                $('#detailPaymentModal').modal('show');

                $.ajax({
                    url: "{{ url('admin/finance/payment-history/detail') }}/" + id,
                    method: "GET",
                    success: function(res) {
                        if (res.status === 'success') {
                            $('#detail-supplier').text(res.payment.supplier_name || '-');
                            $('#detail-date').text(res.payment.payment_date ? new Date(res.payment.payment_date).toLocaleDateString('id-ID') : '-');
                            $('#detail-cashier').text(res.payment.cashier_name || '-');

                            let html = '';
                            let total = 0;
                            res.items.forEach((item, index) => {
                                const merek = item.merek_name || '';
                                const product = item.product_name || '';
                                const variant = item.variant_name || '';
                                const parts = [merek, product, variant].filter(Boolean);
                                const uniqueParts = [];
                                parts.forEach(p => {
                                    let isSub = false;
                                    parts.forEach(p2 => {
                                        if (p !== p2 && p2.includes(p) && p2.length > p.length) isSub = true;
                                    });
                                    if (!isSub) uniqueParts.push(p);
                                });
                                const productName = uniqueParts.join(' ') || 'Produk';
                                total += parseFloat(item.subtotal || 0);
                                html += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td><div class="font-weight-600">${productName}</div></td>
                                        <td class="text-center">${parseFloat(item.qty)}</td>
                                        <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.buy_price)}</td>
                                        <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                                    </tr>
                                `;
                            });

                            if (res.items.length === 0) {
                                html = '<tr><td colspan="5" class="text-center">Tidak ada item.</td></tr>';
                            }

                            $('#detail-items-tbody').html(html);
                            $('#detail-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
                        } else {
                            $('#detail-items-tbody').html('<tr><td colspan="5" class="text-center text-danger">Gagal mengambil data.</td></tr>');
                        }
                    },
                    error: function() {
                        $('#detail-items-tbody').html('<tr><td colspan="5" class="text-center text-danger">Gagal mengambil data.</td></tr>');
                    }
                });
            });
        });
    </script>
@endsection