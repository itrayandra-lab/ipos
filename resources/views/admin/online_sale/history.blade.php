@extends('master')

@section('title', 'Riwayat Penjualan Marketplace')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Riwayat Penjualan Marketplace</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Marketplace</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Daftar Transaksi Online</h2>
            <p class="section-lead">Berikut adalah semua transaksi yang dilakukan melalui platform marketplace.</p>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Semua Pesanan Online</h4>
                            <div class="card-header-form">
                                <a href="{{ route('admin.online_sale.create') }}" class="btn btn-primary btn-sm">
                                    Tambah Penjualan Online
                                </a>
                            </div>
                        </div>
                        <div class="card-header">
                            <form id="filter-form" class="w-100">
                                <div class="row align-items-end">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <label for="source" class="form-label">Platform</label>
                                        <select class="form-control form-control-sm" id="source" name="source">
                                            <option value="">Semua</option>
                                            <option value="shopee">Shopee</option>
                                            <option value="tokopedia">Tokopedia</option>
                                            <option value="tiktok">TikTok</option>
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
                                <table class="table table-striped" id="table-history">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Waktu</th>
                                            <th>Platform</th>
                                            <th>Order ID / Catatan</th>
                                            <th>Total Produk</th>
                                            <th>Total Harga</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Batch</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detail-body"></tbody>
                </table>
                <div id="receipt-detail" class="p-3 border-top" style="display:none;">
                    <h6>Bukti Pembayaran:</h6>
                    <div id="receipt-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#table-history').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('admin/online-sale/all') }}",
                data: function(d) {
                    d.source = $('#source').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'source', name: 'source' },
                { data: 'notes_with_receipt', name: 'notes_with_receipt' },
                { data: 'total_items', name: 'total_items' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        window.resetFilter = function() {
            $('#source').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            table.draw();
        };

        // Event delegation for dynamically created buttons
        $(document).on('click', '.show-detail', function(e) {
            e.preventDefault();
            let items = $(this).data('items');
            let receipt = $(this).data('receipt');
            let html = '';
            
            // Handle if items is a string (due to data attribute)
            if (typeof items === 'string') {
                items = JSON.parse(items);
            }

            items.forEach(function(item) {
                let productName = item.product ? item.product.name : 'Unknown Product';
                let batchNo = item.batch ? item.batch.batch_no : '-';
                html += `
                    <tr>
                        <td>${productName}</td>
                        <td>${batchNo}</td>
                        <td>${item.qty}</td>
                        <td>Rp${item.price.toLocaleString('id-ID')}</td>
                        <td>Rp${item.subtotal.toLocaleString('id-ID')}</td>
                    </tr>
                `;
            });
            $('#detail-body').html(html);

            if (receipt) {
                let receiptHtml = '';
                if (receipt.toLowerCase().endsWith('.pdf')) {
                    receiptHtml = `<a href="${receipt}" target="_blank" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Lihat PDF</a>`;
                } else {
                    receiptHtml = `<img src="${receipt}" class="img-fluid rounded border" alt="Bukti Pembayaran">`;
                }
                $('#receipt-content').html(receiptHtml);
                $('#receipt-detail').show();
            } else {
                $('#receipt-detail').hide();
            }

            $('#detailModal').modal('show');
        });

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            if (confirm('Hapus transaksi ini dan kembalikan stok?')) {
                // Since the original code used a hidden form for each row, 
                // but now we have dynamic rows, we can either use AJAX or 
                // create a dynamic form post. Let's use AJAX for consistency.
                $.ajax({
                    url: "{{ url('admin/online-sale') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        table.draw();
                        iziToast.success({
                            title: 'Berhasil',
                            message: 'Transaksi berhasil dihapus',
                            position: 'topRight'
                        });
                    },
                    error: function(xhr) {
                        alert('Gagal menghapus transaksi');
                    }
                });
            }
        });
    });
</script>
@endpush
