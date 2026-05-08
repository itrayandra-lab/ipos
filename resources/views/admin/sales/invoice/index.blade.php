@extends('master')

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

        #invoice-table {
            font-size: 13px !important;
            border: none !important;
        }

        #invoice-table thead th {
            background-color: #f8fafc !important;
            color: #64748b !important;
            text-transform: none !important;
            font-weight: 600 !important;
            padding: 15px 12px !important;
            border-top: none !important;
        }

        #invoice-table tbody td {
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
            padding: 5px 12px !important;
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
            padding: 10px 20px !important;
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
            <h1>Invoice Penjualan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Invoice</div>
            </div>
        </div>

        <div class="section-body">
            <!-- Redundant titles removed -->
            @if(session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Filter Invoice</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.sales.invoices.create') }}" class="btn btn-premium btn-sm">
                            <i class="fas fa-plus mr-1"></i> Buat Invoice Manual
                        </a>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="filter-card">
                        <form id="filter-form" class="w-100">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label for="payment_status" class="form-label">Status Pembayaran</label>
                                    <select class="form-control form-control-custom" id="payment_status" name="payment_status">
                                        <option value="">Semua Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid / Lunas</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="credit">Credit / DP</option>
                                        <option value="failed">Failed</option>
                                        <option value="canceled">Canceled</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control form-control-custom" id="start_date" name="start_date">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control form-control-custom" id="end_date" name="end_date">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button type="submit" class="btn btn-primary btn-block" style="height: 40px; border-radius: 8px; font-weight: 700;">
                                        <i class="fas fa-filter"></i> Filter Data
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
                    <div class="table-responsive">
                        <table class="table table-striped" id="invoice-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>No. Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#invoice-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.sales.invoices.all') }}",
                data: function(d) {
                    d.payment_status = $('#payment_status').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { 
                    data: 'invoice_number', 
                    name: 'invoice_number',
                    render: function(data) {
                        return `<span class="font-weight-700 text-primary">${data}</span>`;
                    }
                },
                { data: 'created_at', name: 'created_at' },
                { data: 'customer_name', name: 'customer_name' },
                { 
                    data: 'total_amount', 
                    name: 'total_amount',
                    render: function(data) {
                        return `<span class="amount-text">${data}</span>`;
                    }
                },
                { data: 'payment_status', name: 'payment_status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        window.resetFilter = function() {
            $('#payment_status').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            table.draw();
        };
    });

    function deleteInvoice(id) {
        if (!confirm('Hapus invoice ini? Stok akan dikembalikan jika status paid.')) return;
        $.ajax({
            url: '/admin/sales/invoices/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    $('#invoice-table').DataTable().ajax.reload();
                    alert(res.message);
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                alert('Gagal: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
            }
        });
    }
</script>
@endpush
