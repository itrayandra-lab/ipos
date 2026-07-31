@extends('master')
@section('title', 'Stock Opname')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Stock Opname</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/stock') }}">Stok</a></div>
                <div class="breadcrumb-item active">Stock Opname</div>
            </div>
            <div class="section-header-button">
                @if(auth()->user()->hasPermission('access_stock_opname'))
                <a href="{{ url('admin/manage-master/stock-opname/create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Mulai Opname Baru
                </a>
                @endif
            </div>
        </div>

        <div class="section-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Riwayat Stock Opname</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-opname">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Ref Number</th>
                                            <th>Gudang</th>
                                            <th>Status</th>
                                            <th>Dibuat Oleh</th>
                                            <th class="text-right">Jml Item</th>
                                            <th class="text-right">Selisih</th>
                                            <th>Tgl Dibuat</th>
                                            <th>Selesai</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($opnames as $opname)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $opname->reference_number }}</strong></td>
                                            <td>{{ $opname->warehouse->name ?? '-' }}</td>
                                            <td>
                                                @if($opname->status == 'completed')
                                                <span class="badge badge-success">Selesai</span>
                                                @elseif($opname->status == 'draft')
                                                <span class="badge badge-warning">Draft</span>
                                                @elseif($opname->status == 'cancelled')
                                                <span class="badge badge-secondary">Ditolak</span>
                                                @else
                                                <span class="badge badge-secondary">{{ $opname->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $opname->creator->name ?? '-' }}</td>
                                            <td class="text-right">{{ number_format($opname->items->count()) }}</td>
                                            <td class="text-right">{{ number_format($opname->items->sum('difference')) }}</td>
                                            <td>{{ $opname->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $opname->completed_at ? $opname->completed_at->format('d/m/Y H:i') : '-' }}</td>
                                            <td>
                                                @if($opname->status == 'draft' && auth()->user()->hasPermission('approve_stock_opname'))
                                                <button class="btn btn-sm btn-success btn-approve" data-id="{{ $opname->id }}" data-ref="{{ $opname->reference_number }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-reject" data-id="{{ $opname->id }}" data-ref="{{ $opname->reference_number }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                @endif
                                                <a href="{{ url('admin/manage-master/stock-opname/' . $opname->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">Belum ada opname stok</td>
                                        </tr>
                                        @endforelse
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

<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Opname</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reject-id">
                <div class="form-group">
                    <label>Alasan Ditolak</label>
                    <textarea class="form-control" id="reject-alasan" rows="3" placeholder="Alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-reject">Tolak Opname</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#table-opname').DataTable({
        order: [[7, 'desc']],
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
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    });

    $(document).on('click', '.btn-reject', function() {
        const id = $(this).data('id');
        const ref = $(this).data('ref');
        $('#reject-id').val(id);
        $('#rejectModal').modal('show');
    });

    $('#btn-confirm-reject').on('click', function() {
        const id = $('#reject-id').val();
        const alasan = $('#reject-alasan').val();

        $.ajax({
            url: '{{ url("admin/manage-master/stock-opname") }}/' + id + '/reject',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', alasan: alasan },
            success: function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    });
});
</script>
@endpush
