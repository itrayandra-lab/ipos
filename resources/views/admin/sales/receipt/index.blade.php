@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Kuitansi Pembayaran</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Kuitansi</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Transaksi (Siap Kuitansi)</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="receipt-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Total Bayar</th>
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
        $('#receipt-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.sales.receipts.all') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'created_at', name: 'created_at' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
