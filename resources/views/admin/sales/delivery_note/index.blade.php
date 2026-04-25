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

        #dn-table {
            font-size: 13px !important;
            border: none !important;
        }

        #dn-table thead th {
            background-color: #f8fafc !important;
            color: #64748b !important;
            text-transform: none !important;
            font-weight: 600 !important;
            padding: 15px 12px !important;
            border-top: none !important;
        }

        #dn-table tbody td {
            padding: 15px 12px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
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
            <h1>Surat Jalan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Surat Jalan</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Filter Surat Jalan</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.sales.delivery_notes.create') }}" class="btn btn-premium btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Surat Jalan
                        </a>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="filter-card">
                        <form id="filter-form" class="w-100">
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
                        <table class="table table-striped" id="dn-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>No Surat Jalan</th>
                                    <th>Customer</th>
                                    <th>Tanggal</th>
                                    <th style="width: 20%">Aksi</th>
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
        var table = $('#dn-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.sales.delivery_notes.all') }}",
                data: function(d) {
                    d.delivery_type = $('#delivery_type').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { 
                    data: 'delivery_note_no', 
                    name: 'delivery_note_no',
                    render: function(data) {
                        return `<span class="font-weight-700 text-primary">${data}</span>`;
                    }
                },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        window.resetFilter = function() {
            $('#delivery_type').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            table.draw();
        };
    });

    function deleteDeliveryNote(id) {
        if (confirm('Apakah Anda yakin ingin menghapus Surat Jalan ini? Stok barang akan dikembalikan.')) {
            $.ajax({
                url: "{{ url('admin/sales/delivery-notes') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $('#dn-table').DataTable().ajax.reload();
                        iziToast.success({
                            title: 'Berhasil',
                            message: 'Surat Jalan berhasil dihapus',
                            position: 'topRight'
                        });
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                }
            });
        }
    }
</script>
@endpush
