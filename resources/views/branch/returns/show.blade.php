@extends('master')
@section('title', 'Detail Return Barang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('branch.returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Return <code>{{ $return->reference_number }}</code></h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h4>Status & Info</h4></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Status</span> {!! $return->status_label !!}
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">No. Ref</span><code>{{ $return->reference_number }}</code>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Gudang</span><strong>{{ $return->warehouse->name ?? '-' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Dibuat oleh</span><span>{{ $return->requester->name ?? '-' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Tanggal</span><span>{{ $return->created_at->format('d M Y H:i') }}</span>
                                </li>
                                @if($return->reason)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Alasan Return</div>
                                    <div>{{ $return->reason }}</div>
                                </li>
                                @endif
                                @if($return->rejection_reason)
                                <li class="list-group-item px-0">
                                    <div class="text-danger small mb-1">Alasan Penolakan</div>
                                    <div class="text-danger">{{ $return->rejection_reason }}</div>
                                </li>
                                @endif
                                @if($return->approval_notes)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Catatan Pusat</div>
                                    <div>{{ $return->approval_notes }}</div>
                                </li>
                                @endif
                            </ul>
                        </div>

                        @if($return->status === 'approved')
                        <div class="card-footer">
                            <button type="button" class="btn btn-primary btn-block" id="btn-confirm-ship">
                                <i class="fas fa-truck mr-1"></i> Konfirmasi Kirim ke Pusat
                            </button>
                        </div>
                        @endif
                        @if($return->status === 'pending')
                        <div class="card-footer">
                            <button type="button" class="btn btn-outline-danger btn-block" id="btn-cancel">
                                <i class="fas fa-times mr-1"></i> Batalkan Return
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- Timeline --}}
                    <div class="card">
                        <div class="card-header"><h4>Timeline Proses</h4></div>
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
                                        <h6 class="timeline-title text-info">Disetujui Pusat</h6>
                                        <div class="text-muted small">{{ $return->approver->name ?? '-' }} — {{ $return->approved_at?->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($return->status === 'rejected')
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-danger"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-danger">Ditolak</h6>
                                        <div class="text-muted small">{{ $return->rejection_reason }}</div>
                                    </div>
                                </div>
                                @endif
                                @if(in_array($return->status, ['shipped','received']))
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-primary"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-primary">Dikirim ke Pusat</h6>
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
                                        <h6 class="timeline-title text-success">Diterima Pusat</h6>
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

{{-- Modal Konfirmasi Ship --}}
@if($return->status === 'approved')
<div class="modal fade" id="modalConfirmShip" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Konfirmasi Pengiriman ke Pusat</h5></div>
            <form id="form-confirm-ship" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Stok di gudang cabang Anda akan dikurangi setelah konfirmasi pengiriman.
                    </div>
                    <div class="form-group">
                        <label>Foto Bukti Pengiriman <small class="text-muted">(opsional)</small></label>
                        <input type="file" name="receipt_photo" class="form-control-file" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-truck mr-1"></i> Konfirmasi Kirim</button>
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
    $('#btn-confirm-ship').on('click', () => $('#modalConfirmShip').modal('show'));

    $('#form-confirm-ship').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type=submit]');
        const ori = btn.html();
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        const fd = new FormData(this);
        $.ajax({
            url: "{{ route('branch.returns.confirm_ship', $return->id) }}",
            method: 'POST', data: fd, contentType: false, processData: false,
            success: res => {
                if (res.status === 'success') {
                    $('#modalConfirmShip').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    btn.attr('disabled', false).html(ori);
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: err => {
                btn.attr('disabled', false).html(ori);
                Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Terjadi kesalahan' });
            }
        });
    });

    $('#btn-cancel').on('click', function() {
        Swal.fire({
            title: 'Batalkan Return?', icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan', cancelButtonText: 'Tidak', confirmButtonColor: '#dc3545',
        }).then(res => {
            if (res.isConfirmed) {
                $.post("{{ route('branch.returns.cancel', $return->id) }}", { _token: '{{ csrf_token() }}' }, r => {
                    if (r.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Dibatalkan', timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: r.message });
                    }
                });
            }
        });
    });
});
</script>
@endpush
