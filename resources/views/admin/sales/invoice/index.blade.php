@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Invoice Penjualan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Invoice</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Daftar Invoice</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.sales.invoices.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                            <i class="fas fa-plus"></i> Buat Invoice Manual
                        </a>
                    </div>
                </div>
                <div class="card-header">
                    <form id="filter-form" class="w-100">
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label for="payment_status" class="form-label">Status Pembayaran</label>
                                <select class="form-control form-control-sm" id="payment_status" name="payment_status">
                                    <option value="">Semua</option>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="credit">Credit (DP)</option>
                                    <option value="failed">Failed</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" style="height: 40px;">
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" style="height: 40px;">
                            </div>
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="d-flex align-items-end justify-content-start">
                                    <button type="submit" class="btn btn-primary btn-sm mr-2" style="height: 38px;">Terapkan Filter</button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilter()" style="height: 38px;">Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                { data: 'invoice_number', name: 'invoice_number' },
                { data: 'created_at', name: 'created_at' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'total_amount', name: 'total_amount' },
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
