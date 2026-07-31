@extends('master')
@section('title', 'Stock Opname - ' . $opname->reference_number)

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Detail Stock Opname</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/stock') }}">Stok</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/stock-opname') }}">Stock Opname</a></div>
                <div class="breadcrumb-item active">{{ $opname->reference_number }}</div>
            </div>
            <div class="section-header-button">
                <a href="{{ url('admin/manage-master/stock-opname') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Informasi Opname</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="text-muted">Ref Number</small>
                                    <p class="font-weight-bold">{{ $opname->reference_number }}</p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Gudang</small>
                                    <p class="font-weight-bold">{{ $opname->warehouse->name ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Status</small>
                                    <p>
                                        @if($opname->status == 'completed')
                                        <span class="badge badge-success">Selesai</span>
                                        @elseif($opname->status == 'draft')
                                        <span class="badge badge-warning">Draft (Menunggu Persetujuan)</span>
                                        @elseif($opname->status == 'cancelled')
                                        <span class="badge badge-secondary">Ditolak</span>
                                        @else
                                        <span class="badge badge-secondary">{{ $opname->status }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Dibuat Oleh</small>
                                    <p class="font-weight-bold">{{ $opname->creator->name ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Tanggal Dibuat</small>
                                    <p>{{ $opname->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Selesai</small>
                                    <p>{{ $opname->completed_at ? $opname->completed_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Disetujui Oleh</small>
                                    <p class="font-weight-bold">{{ $opname->approver->name ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Tgl Persetujuan</small>
                                    <p>{{ $opname->approved_at ? $opname->approved_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                                @if($opname->notes)
                                <div class="col-12">
                                    <small class="text-muted">Catatan</small>
                                    <p style="white-space: pre-wrap;">{{ $opname->notes }}</p>
                                </div>
                                @endif
                                <div class="col-md-3">
                                    <small class="text-muted">Total Item</small>
                                    <p class="font-weight-bold">{{ number_format($opname->items->count()) }}</p>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Total Selisih</small>
                                    <p class="font-weight-bold">{{ number_format($opname->items->sum('difference')) }}</p>
                                </div>
                            </div>

                            @if($opname->status == 'draft' && auth()->user()->hasPermission('approve_stock_opname'))
                            <hr>
                            <div class="text-right">
                                <button class="btn btn-success btn-approve" data-id="{{ $opname->id }}" data-ref="{{ $opname->reference_number }}">
                                    <i class="fas fa-check mr-1"></i> Setujui & Terapkan
                                </button>
                                <button class="btn btn-danger btn-reject-from-detail" data-id="{{ $opname->id }}">
                                    <i class="fas fa-times mr-1"></i> Tolak
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Detail Item</h4>
                        </div>
                        <div class="card-body">
                            @if($opname->items->isEmpty())
                            <div class="text-center text-muted py-3">Tidak ada item</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-items">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Produk</th>
                                            <th>Variant</th>
                                            <th class="text-right">Stok Sistem</th>
                                            <th class="text-right">Stok Fisik</th>
                                            <th class="text-right">Selisih</th>
                                            <th>Indikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($opname->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $item->product->name ?? 'Produk dihapus' }}</strong></td>
                                            <td>{{ $item->variant->variant_name ?? '-' }}</td>
                                            <td class="text-right">{{ number_format($item->system_qty) }}</td>
                                            <td class="text-right">{{ number_format($item->physical_qty) }}</td>
                                            <td class="text-right @if($item->difference > 0) text-danger @elseif($item->difference < 0) text-success @endif font-weight-bold">
                                                {{ $item->difference > 0 ? '+' . $item->difference : $item->difference }}
                                            </td>
                                            <td>
                                                @if($item->difference > 0)
                                                <span class="text-danger">Kelebihan Stok</span>
                                                @elseif($item->difference < 0)
                                                <span class="text-success">Kekurangan Stok</span>
                                                @else
                                                -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<form id="approve-form" method="POST" style="display:none;">
    @csrf
</form>

<div class="modal fade" id="rejectModalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Opname</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reject-id-detail">
                <div class="form-group">
                    <label>Alasan Ditolak</label>
                    <textarea class="form-control" id="reject-alasan-detail" rows="3" placeholder="Alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-reject-detail">Tolak Opname</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#table-items').DataTable({
        pageLength: 50,
        order: [[1, 'asc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });

    $(document).on('click', '.btn-approve', function() {
        const id = $(this).data('id');
        const ref = $(this).data('ref');
        if (!confirm('Setujui opname ' + ref + '? Stok akan disesuaikan secara permanen.')) return;

        $.ajax({
            url: '{{ url("admin/manage-master/stock-opname") }}/' + id + '/approve',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) location.reload();
                else alert(res.message);
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    });

    $(document).on('click', '.btn-reject-from-detail', function() {
        const id = $(this).data('id');
        $('#reject-id-detail').val(id);
        $('#rejectModalDetail').modal('show');
    });

    $('#btn-confirm-reject-detail').on('click', function() {
        const id = $('#reject-id-detail').val();
        const alasan = $('#reject-alasan-detail').val();

        $.ajax({
            url: '{{ url("admin/manage-master/stock-opname") }}/' + id + '/reject',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', alasan: alasan },
            success: function(res) {
                if (res.success) location.reload();
                else alert(res.message);
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    });
});
</script>
@endpush
