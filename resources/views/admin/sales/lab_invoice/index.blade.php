@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Invoice Kelas Formulasi</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Invoice Lab</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Invoice Kelas Formulasi Lab</h4>
                    <div class="card-header-form">
                        <a href="{{ route('admin.sales.lab_invoices.create') }}" class="btn btn-primary btn-sm">
                            Buat Invoice Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="lab-invoice-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Nama Peserta</th>
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
        $('#lab-invoice-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.sales.lab_invoices.all') }}",
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
