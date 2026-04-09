@extends('master')
@section('title', 'Data Transaksi')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Transaksi</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Data Transaksi</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Transaksi</h2>
                <p class="section-lead">Berikut adalah Data Transaksi.</p>
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>Data Seluruh Transaksi</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-success btn-sm" onclick="printData()" aria-label="Cetak data">Cetak Data</button>
                        </div>
                    </div>
                    <div class="card-header">
                        <form id="filter-form">
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
                                    <label for="payment_status" class="form-label">Status Pembayaran</label>
                                    <select class="form-control form-control-sm" id="payment_status" name="payment_status">
                                        <option value="">Semua</option>
                                        <option value="pending">Pending</option>
                                        <option value="completed">Completed</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-6 mb-3">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" style="height: 40px;">
                                </div>
                                <div class="col-md-2 col-sm-6 mb-3">
                                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" style="height: 40px;">
                                </div>
                                <div class="col-md-12 col-sm-12 mb-3 d-flex align-items-end">
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm mr-2" aria-label="Terapkan filter">Terapkan Filter</button>
                                        <button type="button" class="btn btn-secondary btn-sm mr-2" onclick="resetFilter()" aria-label="Reset filter">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5" id="transaction-table">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>User</th>
                                    <th>Total Amount</th>
                                    <th>Payment Status</th>
                                    <th>Delivery Type</th>
                                    <th>Tanggal</th>
                                    <th width="10px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            // DataTable initialization
            var table = $('#transaction-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/transactions/all') }}",
                    type: "GET",
                    data: function(d) {
                        d.delivery_type = $('#delivery_type').val();
                        d.payment_status = $('#payment_status').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'user.name', name: 'user.name' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'delivery_type', name: 'delivery_type' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Handle filter form submission
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Reset filter
            window.resetFilter = function() {
                $('#delivery_type').val('');
                $('#payment_status').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                table.draw();
            };

            // Print data
            window.printData = function() {
                var delivery_type = $('#delivery_type').val();
                var payment_status = $('#payment_status').val();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var url = "{{ url('admin/transactions/print') }}?delivery_type=" + delivery_type +
                          "&payment_status=" + payment_status +
                          "&start_date=" + start_date +
                          "&end_date=" + end_date;
                window.open(url, '_blank');
            };

            // Delete transaction
            window.deleteTransaction = function(id) {
                if (confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.')) {
                    $.ajax({
                        url: "{{ url('admin/transactions') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                if (typeof iziToast !== "undefined") {
                                    iziToast.success({
                                        title: 'Berhasil',
                                        message: 'Transaksi berhasil dihapus',
                                        position: 'topRight'
                                    });
                                } else {
                                    alert('Transaksi berhasil dihapus');
                                }
                            } else {
                                if (typeof iziToast !== "undefined") {
                                    iziToast.error({
                                        title: 'Gagal',
                                        message: response.message || 'Terjadi kesalahan saat menghapus transaksi',
                                        position: 'topRight'
                                    });
                                } else {
                                    alert('Gagal: ' + (response.message || 'Terjadi kesalahan'));
                                }
                            }
                        },
                        error: function() {
                            if (typeof iziToast !== "undefined") {
                                iziToast.error({
                                    title: 'Gagal',
                                    message: 'Terjadi kesalahan sistem',
                                    position: 'topRight'
                                });
                            } else {
                                alert('Terjadi kesalahan sistem');
                            }
                        }
                    });
                }
            };

            // Generate Invoice
            window.generateInvoice = function(id) {
                if (confirm('Apakah Anda yakin ingin membuat nomor invoice formal untuk transaksi ini?')) {
                    $.ajax({
                        url: "{{ url('admin/transactions/generate-invoice') }}/" + id,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend: function() {
                            $.LoadingOverlay("show");
                        },
                        complete: function() {
                            $.LoadingOverlay("hide");
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                iziToast.success({
                                    title: 'Berhasil',
                                    message: response.message,
                                    position: 'topRight'
                                });
                                // Automatically open print view for the new invoice
                                var printUrl = "{{ route('admin.sales.invoices.print', ':id') }}".replace(':id', id);
                                window.open(printUrl, '_blank');
                            } else {
                                iziToast.error({
                                    title: 'Gagal',
                                    message: response.message,
                                    position: 'topRight'
                                });
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                            iziToast.error({
                                title: 'Gagal',
                                message: msg,
                                position: 'topRight'
                            });
                        }
                    });
                }
            };
        });
    </script>
@endsection