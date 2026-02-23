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
                        <a href="{{ route('admin.sales.invoices.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Invoice Manual
                        </a>
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
        $('#invoice-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.sales.invoices.all') }}",
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
