@extends('master')

@section('content')
<div class="main-content">
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
                    <h4>Daftar Surat Jalan</h4>
                    <div class="card-header-action">
                        <a href="{{ route('admin.sales.delivery_notes.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                            <i class="fas fa-plus"></i> Tambah Surat Jalan
                        </a>
                    </div>
                </div>
                <div class="card-header">
                    <form id="filter-form" class="w-100">
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label for="delivery_type" class="form-label">Tipe Pengiriman</label>
                                <select class="form-control form-control-sm" id="delivery_type" name="delivery_type">
                                    <option value="">Semua</option>
                                    <option value="pickup">Pickup</option>
                                    <option value="delivery">Delivery</option>
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
                { data: 'delivery_note_no', name: 'delivery_note_no' },
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
