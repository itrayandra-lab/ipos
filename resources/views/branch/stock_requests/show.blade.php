@extends('master')
@section('title', 'Detail Pengajuan Barang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('branch.stock_requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Pengajuan <code>{{ $request->reference_number }}</code></h1>
        </div>
        <div class="section-body">
            <div class="row">
                {{-- Info Panel --}}
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header"><h4>Status & Info</h4></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Status</span>
                                    {!! $request->status_label !!}
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">No. Referensi</span>
                                    <code>{{ $request->reference_number }}</code>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Gudang Cabang</span>
                                    <strong>{{ $request->warehouse->name ?? '-' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Dibuat oleh</span>
                                    <span>{{ $request->requester->name ?? '-' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Tgl Pengajuan</span>
                                    <span>{{ $request->created_at->format('d M Y H:i') }}</span>
                                </li>
                                @if($request->notes)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Catatan Pengajuan</div>
                                    <div>{{ $request->notes }}</div>
                                </li>
                                @endif
                                @if($request->rejection_reason)
                                <li class="list-group-item px-0">
                                    <div class="text-danger small mb-1"><i class="fas fa-times-circle mr-1"></i>Alasan Penolakan</div>
                                    <div class="text-danger">{{ $request->rejection_reason }}</div>
                                </li>
                                @endif
                                @if($request->approval_notes)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Catatan Pusat</div>
                                    <div>{{ $request->approval_notes }}</div>
                                </li>
                                @endif
                                @if($request->shipping_notes)
                                <li class="list-group-item px-0">
                                    <div class="text-muted small mb-1">Catatan Pengiriman</div>
                                    <div>{{ $request->shipping_notes }}</div>
                                </li>
                                @endif
                            </ul>
                        </div>

                        {{-- Action Buttons --}}
                        @if($request->status === 'shipped')
                        <div class="card-footer">
                            <button type="button" class="btn btn-success btn-block" id="btn-confirm-receive">
                                <i class="fas fa-check mr-1"></i> Konfirmasi Terima Barang
                            </button>
                        </div>
                        @endif
                        @if($request->status === 'pending')
                        <div class="card-footer">
                            <button type="button" class="btn btn-outline-danger btn-block" id="btn-cancel">
                                <i class="fas fa-times mr-1"></i> Batalkan Pengajuan
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
                                        <h6 class="timeline-title">Pengajuan Dibuat</h6>
                                        <div class="text-muted small">{{ $request->requester->name ?? '-' }} — {{ $request->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @if(in_array($request->status, ['approved','shipped','received']))
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-info"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-info">Disetujui Pusat</h6>
                                        <div class="text-muted small">{{ $request->approver->name ?? '-' }} — {{ $request->approved_at?->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($request->status === 'rejected')
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-danger"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-danger">Ditolak Pusat</h6>
                                        <div class="text-muted small">{{ $request->approver->name ?? '-' }} — {{ $request->approved_at?->format('d M Y H:i') }}</div>
                                        <div class="text-danger">{{ $request->rejection_reason }}</div>
                                    </div>
                                </div>
                                @endif
                                @if(in_array($request->status, ['shipped','received']))
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-primary"></div>
                                    <div class="timeline-event">
                                        <h6 class="timeline-title text-primary">Barang Dikirim</h6>
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
                                                <img src="{{ Storage::url($request->receipt_photo) }}" class="img-thumbnail" style="max-height:80px">
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

                {{-- Items Table --}}
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-header"><h4>Item Barang yang Diminta</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th><th>Produk</th>
                                            <th class="text-center">Qty Diminta</th>
                                            <th class="text-center">Qty Disetujui</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($request->items as $item)
                                        <tr>
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
                                            <td class="text-center">{{ number_format($item->qty_requested, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($item->qty_approved !== null)
                                                    <strong class="{{ $item->qty_approved < $item->qty_requested ? 'text-warning' : 'text-success' }}">
                                                        {{ number_format($item->qty_approved, 0, ',', '.') }}
                                                    </strong>
                                                @else
                                                    <span class="text-muted">—</span>
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

{{-- Modal Konfirmasi Terima --}}
@if($request->status === 'shipped')
<div class="modal fade" id="modalConfirmReceive" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Konfirmasi Terima Barang</h5></div>
            <form id="form-confirm-receive" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Dengan mengkonfirmasi, stok cabang Anda akan otomatis bertambah sesuai qty yang disetujui pusat.
                    </div>
                    <div class="form-group">
                        <label>Catatan Penerimaan</label>
                        <textarea name="receipt_notes" class="form-control" rows="3" placeholder="Kondisi barang, catatan penerimaan..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Foto Bukti Terima <small class="text-muted">(opsional)</small></label>
                        <input type="file" name="receipt_photo" class="form-control-file" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Konfirmasi Terima</button>
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
    $('#btn-confirm-receive').on('click', () => $('#modalConfirmReceive').modal('show'));

    $('#form-confirm-receive').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type=submit]');
        const ori = btn.html();
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        const fd = new FormData(this);
        $.ajax({
            url: "{{ route('branch.stock_requests.confirm_receive', $request->id) }}",
            method: 'POST', data: fd, contentType: false, processData: false,
            success: res => {
                if (res.status === 'success') {
                    $('#modalConfirmReceive').modal('hide');
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
            title: 'Batalkan Pengajuan?', text: 'Pengajuan ini akan dibatalkan dan tidak dapat diproses lagi.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan', cancelButtonText: 'Tidak',
            confirmButtonColor: '#dc3545',
        }).then(res => {
            if (res.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.post("{{ route('branch.stock_requests.cancel', $request->id) }}", { _token: '{{ csrf_token() }}' }, res => {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Dibatalkan', text: res.message, timer: 1800, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                });
            }
        });
    });
});
</script>
@endpush
