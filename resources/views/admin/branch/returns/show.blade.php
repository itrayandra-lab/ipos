@extends('master')
@section('title', 'Detail Return Cabang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.branch.returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Return <code>{{ $return->reference_number }}</code></h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h4>Info Return</h4></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Status</span> {!! $return->status_label !!}
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Gudang Cabang</span><strong>{{ $return->warehouse->name ?? '-' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Diajukan oleh</span><span>{{ $return->requester->name ?? '-' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Tgl Pengajuan</span><span>{{ $return->created_at->format('d M Y H:i') }}</span>
                                </li>
                                @if($return->reason)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Alasan Return</div>
                                    <div>{{ $return->reason }}</div>
                                </li>
                                @endif
                                @if($return->rejection_reason)
                                <li class="list-group-item px-0">
                                    <div class="text-danger small">Alasan Penolakan</div>
                                    <div class="text-danger">{{ $return->rejection_reason }}</div>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @if($return->status === 'pending')
                        <div class="card-footer">
                            <button class="btn btn-success btn-block mb-2" id="btn-approve">
                                <i class="fas fa-check mr-1"></i> Setujui Return
                            </button>
                            <button class="btn btn-outline-danger btn-block" id="btn-reject">
                                <i class="fas fa-times mr-1"></i> Tolak Return
                            </button>
                        </div>
                        @endif
                        @if($return->status === 'shipped')
                        <div class="card-footer">
                            <button class="btn btn-success btn-block" id="btn-confirm-receive">
                                <i class="fas fa-check-double mr-1"></i> Konfirmasi Terima di Pusat
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
                                        <h6 class="timeline-title">Return Diajukan</h6>
                                        <div class="text-muted small">{{ $return->requester->name ?? '-' }} — {{ $return->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @if(in_array($return->status, ['approved','shipped','received']))
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-info"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-info">Disetujui</h6>
                                        <div class="text-muted small">{{ $return->approver->name ?? '-' }} — {{ $return->approved_at?->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @endif
                                @if(in_array($return->status, ['shipped','received']))
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-primary"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-primary">Dikirim dari Cabang</h6>
                                        <div class="text-muted small">{{ $return->shipped_at?->format('d M Y H:i') }}</div>
                                        @if($return->receipt_photo)
                                        <div class="mt-1">
                                            <a href="{{ Storage::url($return->receipt_photo) }}" target="_blank">
                                                <img src="{{ Storage::url($return->receipt_photo) }}" class="img-thumbnail" style="max-height:70px">
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if($return->status === 'received')
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-success"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-success">Diterima di Pusat</h6>
                                        <div class="text-muted small">{{ $return->receiver->name ?? '-' }} — {{ $return->received_at?->format('d M Y H:i') }}</div>
                                        @if($return->receipt_notes)<div class="small">{{ $return->receipt_notes }}</div>@endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h4>Item yang Di-Return</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr><th>#</th><th>Produk</th><th>Variant</th><th>Batch</th><th class="text-center">Qty</th><th>Alasan</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($return->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->product->name ?? '-' }}</td>
                                            <td>{{ $item->variant->variant_name ?? '-' }}</td>
                                            <td><code>{{ $item->batch->batch_no ?? '-' }}</code></td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td>{{ $item->reason ?: '-' }}</td>
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

{{-- Modals --}}
@if($return->status === 'pending')
<div class="modal fade" id="modalApprove" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Setujui Return</h5></div>
        <form id="form-approve">@csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="approval_notes" class="form-control" rows="3" placeholder="Catatan (opsional)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Setujui</button>
            </div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Tolak Return</h5></div>
        <form id="form-reject">@csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak</button>
            </div>
        </form>
    </div></div>
</div>
@endif

@if($return->status === 'shipped')
<div class="modal fade" id="modalReceive" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Konfirmasi Terima di Pusat</h5></div>
        <form id="form-receive" enctype="multipart/form-data">@csrf
            <div class="modal-body">
                <div class="alert alert-info">Stok gudang pusat akan ditambah kembali setelah konfirmasi penerimaan.</div>
                <div class="form-group">
                    <label>Catatan Penerimaan</label>
                    <textarea name="receipt_notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Foto Bukti Terima <small class="text-muted">(opsional)</small></label>
                    <input type="file" name="receipt_photo" class="form-control-file" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check-double mr-1"></i> Konfirmasi</button>
            </div>
        </form>
    </div></div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function postAndReload(url, data, modal) {
        $.ajax({
            url, method: 'POST', data,
            success: res => {
                if (modal) $(modal).modal('hide');
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: err => Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message })
        });
    }

    $('#btn-approve').on('click', () => $('#modalApprove').modal('show'));
    $('#form-approve').on('submit', e => {
        e.preventDefault();
        postAndReload("{{ route('admin.branch.returns.approve', $return->id) }}", $(e.target).serialize(), '#modalApprove');
    });
    $('#btn-reject').on('click', () => $('#modalReject').modal('show'));
    $('#form-reject').on('submit', e => {
        e.preventDefault();
        postAndReload("{{ route('admin.branch.returns.reject', $return->id) }}", $(e.target).serialize(), '#modalReject');
    });
    $('#btn-confirm-receive').on('click', () => $('#modalReceive').modal('show'));
    $('#form-receive').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        $.ajax({
            url: "{{ route('admin.branch.returns.confirm_receive', $return->id) }}",
            method: 'POST', data: fd, contentType: false, processData: false,
            success: res => {
                $('#modalReceive').modal('hide');
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: err => Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message })
        });
    });
});
</script>
@endpush
