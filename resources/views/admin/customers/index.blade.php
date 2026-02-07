@extends('master')
@section('title', 'Customer Analytics')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Customer Analytics</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Customer Analytics</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Customer</h4>
                        <div class="card-header-form">
                            <select id="filter-status" class="form-control form-control-sm">
                                <option value="all">Semua</option>
                                <option value="frequent">Paling Sering Belanja</option>
                                <option value="newest">Terbaru</option>
                                <option value="inactive">Tidak Aktif (>30 hari)</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="customer-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama</th>
                                        <th>WA/Phone</th>
                                        <th>Total Transaksi</th>
                                        <th>Total Belanja</th>
                                        <th>Last Transaksi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
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

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#customer-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.customers.all') }}",
                    data: function(d) {
                        d.filter = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'customer_phone', name: 'customer_phone' },
                    { data: 'total_transactions', name: 'total_transactions' },
                    { data: 'total_spending_formatted', name: 'total_spending' },
                    { data: 'last_transaction_formatted', name: 'last_transaction' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            $('#filter-status').on('change', function() {
                table.draw();
            });
        });
    </script>
    @endpush
@endsection
