@extends('master')

@section('title', 'Riwayat Penjualan Marketplace')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Riwayat Penjualan Marketplace</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.online_sale.index') }}">Online Sale</a></div>
                <div class="breadcrumb-item active">Riwayat</div>
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
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-history">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Platform</th>
                                            <th>Order ID / Catatan</th>
                                            <th>Total Produk</th>
                                            <th>Total Harga</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $trx)
                                        <tr>
                                            <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($trx->source == 'shopee')
                                                    <span class="badge badge-warning">Shopee</span>
                                                @elseif($trx->source == 'tokopedia')
                                                    <span class="badge badge-success">Tokopedia</span>
                                                @elseif($trx->source == 'tiktok')
                                                    <span class="badge badge-dark">TikTok</span>
                                                @endif
                                            </td>
                                            <td>{{ $trx->notes ?? '-' }}</td>
                                            <td>{{ $trx->items->sum('qty') }} Item</td>
                                            <td>Rp{{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                            <td>
                                                <div class="dropdown d-inline">
                                                    <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Aksi
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item has-icon show-detail" href="#" data-items="{{ json_encode($trx->items) }}">
                                                            <i class="fas fa-eye text-info"></i> Detail
                                                        </a>
                                                        <a class="dropdown-item has-icon" href="{{ route('admin.online_sale.edit', $trx->id) }}">
                                                            <i class="fas fa-edit text-primary"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('admin.online_sale.destroy', $trx->id) }}" method="POST" id="delete-form-{{ $trx->id }}" class="d-none">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                        <a class="dropdown-item has-icon text-danger" href="#" onclick="if(confirm('Hapus transaksi ini dan kembalikan stok?')) document.getElementById('delete-form-{{ $trx->id }}').submit(); return false;">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
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
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('.show-detail').click(function() {
        let items = $(this).data('items');
        let html = '';
        items.forEach(function(item) {
            html += `
                <tr>
                    <td>${item.product.name}</td>
                    <td>${item.batch ? item.batch.batch_no : '-'}</td>
                    <td>${item.qty}</td>
                    <td>Rp${item.price.toLocaleString('id-ID')}</td>
                    <td>Rp${item.subtotal.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });
        $('#detail-body').html(html);
        $('#detailModal').modal('show');
    });
</script>
@endpush
