@extends('master')
@section('title', 'Detail Pengajuan Cabang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.branch.stock_requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Pengajuan <code>{{ $request->reference_number }}</code></h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h4>Info Pengajuan</h4></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Status</span> {!! $request->status_label !!}
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Gudang Cabang</span><strong>{{ $request->warehouse->name ?? '-' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Diajukan oleh</span><span>{{ $request->requester->name ?? '-' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Tgl Pengajuan</span><span>{{ $request->created_at->format('d M Y H:i') }}</span>
                                </li>
                                @if($mainWarehouse)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Gudang Pusat</span><strong class="text-primary">{{ $mainWarehouse->name }}</strong>
                                </li>
                                @endif
                                @if($request->notes)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Catatan dari Cabang</div>
                                    <div>{{ $request->notes }}</div>
                                </li>
                                @endif
                                @if($request->rejection_reason)
                                <li class="list-group-item px-0">
                                    <div class="text-danger small mb-1">Alasan Penolakan</div>
                                    <div class="text-danger">{{ $request->rejection_reason }}</div>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @if($request->status === 'pending')
                        <div class="card-footer">
                            <button class="btn btn-success btn-block mb-2" id="btn-approve">
                                <i class="fas fa-check mr-1"></i> Setujui & Set Qty
                            </button>
                            <button class="btn btn-outline-danger btn-block" id="btn-reject">
                                <i class="fas fa-times mr-1"></i> Tolak Pengajuan
                            </button>
                        </div>
                        @endif
                        @if($request->status === 'approved')
                        <div class="card-footer">
                            <button class="btn btn-primary btn-block" id="btn-ship">
                                <i class="fas fa-truck mr-1"></i> Proses Pengiriman
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- Timeline --}}
                    <div class="card">
                        <div class="card-header"><h4>Timeline</h4></div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-primary"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title">Diajukan</h6>
                                        <div class="text-muted small">{{ $request->requester->name ?? '-' }} — {{ $request->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @if(in_array($request->status, ['approved','shipped','received']))
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-info"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-info">Disetujui</h6>
                                        <div class="text-muted small">{{ $request->approver->name ?? '-' }} — {{ $request->approved_at?->format('d M Y H:i') }}</div>
                                        @if($request->approval_notes)<div class="small">{{ $request->approval_notes }}</div>@endif
                                    </div>
                                </div>
                                @endif
                                @if($request->status === 'rejected')
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-danger"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-danger">Ditolak</h6>
                                        <div class="text-muted small">{{ $request->rejection_reason }}</div>
                                    </div>
                                </div>
                                @endif
                                @if(in_array($request->status, ['shipped','received']))
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-primary"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-primary">Dikirim</h6>
                                        <div class="text-muted small">{{ $request->shipper->name ?? '-' }} — {{ $request->shipped_at?->format('d M Y H:i') }}</div>
                                        @if($request->shipping_notes)<div class="small">{{ $request->shipping_notes }}</div>@endif
                                    </div>
                                </div>
                                @endif
                                @if($request->status === 'received')
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-success"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-success">Diterima Cabang</h6>
                                        <div class="text-muted small">{{ $request->receiver->name ?? '-' }} — {{ $request->received_at?->format('d M Y H:i') }}</div>
                                        @if($request->receipt_notes)<div class="small">{{ $request->receipt_notes }}</div>@endif
                                        @if($request->receipt_photo)
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($request->receipt_photo) }}" target="_blank">
                                                <img src="{{ Storage::url($request->receipt_photo) }}" class="img-thumbnail" style="max-height:100px">
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h4>Item Barang yang Diminta</h4></div>
                        <div class="card-body">
                            @if($request->status === 'pending')
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> Saat menyetujui, Anda dapat mengubah <strong>Qty Disetujui</strong> sesuai stok yang tersedia di gudang pusat.
                            </div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th><th>Produk</th>
                                            <th class="text-center">Stok Pusat</th>
                                            <th class="text-center">Qty Diminta</th>
                                            <th class="text-center">Qty Disetujui</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-body">
                                        @foreach($request->items as $item)
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @php
                                                    $merek = $item->product->merek->name ?? '';
                                                    $nama = $item->product->name ?? '';
                                                    $netto = $item->variant->netto ?? null;
                                                    $nettoLabel = $netto ? trim(($netto->netto_value ?? '') . ' ' . ($netto->satuan ?? '')) : '';
                                                @endphp
                                                {{ trim("$merek $nama $nettoLabel") ?: '-' }}
                                            </td>
                                            <td class="text-center">
                                                @php $avail = $stockAvailability[$item->id] ?? 0; @endphp
                                                <span class="{{ $avail < $item->qty_requested ? 'text-danger font-weight-bold' : 'text-success' }}">
                                                    {{ number_format($avail, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ number_format($item->qty_requested, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($request->status === 'pending')
                                                <input type="number" class="form-control form-control-sm input-qty-approved text-center"
                                                    value="{{ $item->qty_requested }}" min="0" max="{{ $stockAvailability[$item->id] ?? 9999 }}" style="width:80px;margin:auto">
                                                @else
                                                    @if($item->qty_approved !== null)
                                                    <strong class="{{ $item->qty_approved < $item->qty_requested ? 'text-warning' : 'text-success' }}">
                                                        {{ number_format($item->qty_approved, 0, ',', '.') }}
                                                    </strong>
                                                    @else
                                                    <span class="text-muted">—</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>{{ $item->notes ?: '-' }}</td>
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

{{-- Modal Approve --}}
@if($request->status === 'pending')
<div class="modal fade" id="modalApprove" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Konfirmasi Persetujuan</h5></div>
            <form id="form-approve">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Catatan Persetujuan</label>
                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="Catatan untuk cabang (opsional)..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <small>Pastikan qty yang disetujui sudah sesuai dengan stok yang tersedia di gudang pusat.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tolak Pengajuan</h5></div>
            <form id="form-reject">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times mr-1"></i> Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal Ship --}}
@if($request->status === 'approved')
<div class="modal fade" id="modalShip" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Proses Pengiriman</h5></div>
            <form id="form-ship">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> Stok di gudang pusat akan dikurangi secara FIFO (First In, First Out).
                    </div>
                    <div class="form-group">
                        <label>Catatan Pengiriman</label>
                        <textarea name="shipping_notes" class="form-control" rows="2" placeholder="Catatan pengiriman..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-truck mr-1"></i> Kirim Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function showAlert(icon, title, text, cb) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon, title, text, timer: 2000, showConfirmButton: false }).then(() => { if (cb) cb(); });
        } else {
            alert(title + ': ' + text);
            if (cb) cb();
        }
    }

    // Approve
    $('#btn-approve').on('click', () => $('#modalApprove').modal('show'));
    $('#form-approve').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type=submit]');
        const ori = btn.html();
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>');

        const items = [];
        $('#items-body tr').each(function() {
            items.push({ id: $(this).data('item-id'), qty_approved: $(this).find('.input-qty-approved').val() });
        });

        $.ajax({
            url: "{{ route('admin.branch.stock_requests.approve', $request->id) }}",
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', approval_notes: $('[name=approval_notes]').val(), items: items },
            success: res => {
                $('#modalApprove').modal('hide');
                if (res.status === 'success') {
                    showAlert('success', 'Disetujui!', res.message, () => location.reload());
                } else {
                    btn.attr('disabled', false).html(ori);
                    showAlert('error', 'Gagal', res.message);
                }
            },
            error: err => {
                btn.attr('disabled', false).html(ori);
                showAlert('error', 'Error', err.responseJSON?.message || 'Terjadi kesalahan');
            }
        });
    });

    // Reject
    $('#btn-reject').on('click', () => $('#modalReject').modal('show'));
    $('#form-reject').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type=submit]');
        const ori = btn.html();
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>');
        $.ajax({
            url: "{{ route('admin.branch.stock_requests.reject', $request->id) }}",
            method: 'POST', data: $(this).serialize(),
            success: res => {
                $('#modalReject').modal('hide');
                if (res.status === 'success') {
                    showAlert('success', 'Ditolak', res.message, () => location.reload());
                } else {
                    btn.attr('disabled', false).html(ori);
                    showAlert('error', 'Gagal', res.message);
                }
            },
            error: err => {
                btn.attr('disabled', false).html(ori);
                showAlert('error', 'Error', err.responseJSON?.message);
            }
        });
    });

    // Ship
    $('#btn-ship').on('click', () => $('#modalShip').modal('show'));
    $('#form-ship').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type=submit]');
        const ori = btn.html();
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');
        $.ajax({
            url: "{{ route('admin.branch.stock_requests.ship', $request->id) }}",
            method: 'POST', data: $(this).serialize(),
            success: res => {
                $('#modalShip').modal('hide');
                if (res.status === 'success') {
                    showAlert('success', 'Dikirim!', res.message, () => location.reload());
                } else {
                    btn.attr('disabled', false).html(ori);
                    showAlert('error', 'Gagal', res.message);
                }
            },
            error: err => {
                btn.attr('disabled', false).html(ori);
                showAlert('error', 'Error', err.responseJSON?.message);
            }
        });
    });
});
</script>
@endpush
