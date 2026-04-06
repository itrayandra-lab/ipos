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
                <div class="card-header">
                    <form id="filter-form" class="w-100">
                        <div class="row align-items-end">
                            <div class="col-md-4 col-sm-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" style="height: 40px;">
                            </div>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" style="height: 40px;">
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3">
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
        var table = $('#receipt-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.sales.receipts.all') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'created_at', name: 'created_at' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        window.resetFilter = function() {
            $('#start_date').val('');
            $('#end_date').val('');
            table.draw();
        };
    });
</script>
@endpush
