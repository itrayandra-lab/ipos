@extends('master')

@section('title', 'Detail Settlement #' . $settlement->settlement_no)

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.settlements.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Settlement {{ $settlement->settlement_no }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header"><h4>Informasi</h4></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">No. Settlement</span>
                                    <strong>{{ $settlement->settlement_no }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Gudang</span>
                                    <strong class="text-primary">{{ $settlement->warehouse->name }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Periode</span>
                                    <span>{{ $settlement->period_start->format('d/m/Y') }} – {{ $settlement->period_end->format('d/m/Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Status</span>
                                    @php
                                        $badgeMap = ['draft'=>'badge-secondary','pending'=>'badge-warning','verified'=>'badge-info','paid'=>'badge-success','rejected'=>'badge-danger'];
                                    @endphp
                                    <span class="badge {{ $badgeMap[$settlement->status] ?? 'badge-secondary' }}">{{ strtoupper($settlement->status) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Total Tagihan</span>
                                    <strong class="text-success h5 mb-0">Rp {{ number_format($settlement->total_amount, 0, ',', '.') }}</strong>
                                </li>
                            </ul>
                        </div>
                        @if(in_array($settlement->status, ['draft', 'rejected', 'pending']))
                        <div class="card-footer">
                            @if(in_array($settlement->status, ['draft', 'rejected']))
                                <button class="btn btn-primary btn-block" id="btn-submit">
                                    <i class="fas fa-paper-plane mr-1"></i> Submit untuk Verifikasi
                                </button>
                            @endif
                            @if($settlement->status === 'pending')
                                <button class="btn btn-success btn-block mb-2" id="btn-verify">
                                    <i class="fas fa-check-double mr-1"></i> Verifikasi & Setujui
                                </button>
                                <button class="btn btn-danger btn-block" id="btn-reject">
                                    <i class="fas fa-times mr-1"></i> Tolak
                                </button>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="card">
                        <div class="card-header"><h4>Log</h4></div>
                        <div class="card-body">
                            <div class="small">
                                <div class="mb-2"><i class="fas fa-user text-muted mr-1"></i> Dibuat oleh: <strong>{{ $settlement->creator->name }}</strong></div>
                                <div class="text-muted">{{ $settlement->created_at->format('d M Y H:i') }}</div>
                            </div>
                            @if($settlement->verifier)
                            <hr>
                            <div class="small">
                                <div class="mb-2"><i class="fas fa-check text-success mr-1"></i> Diverifikasi: <strong>{{ $settlement->verifier->name }}</strong></div>
                                <div class="text-muted">{{ $settlement->verified_at?->format('d M Y H:i') }}</div>
                            </div>
                            @endif
                            @if($settlement->notes)
                            <hr>
                            <div class="small text-muted">{{ $settlement->notes }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-header"><h4>Rincian Item Terjual</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Produk</th>
                                            <th>Variant</th>
                                            <th class="text-right">Qty Terjual</th>
                                            <th class="text-right">Harga Satuan</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($settlement->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="font-weight-bold">{{ $item->product->name ?? '-' }}</td>
                                            <td>{{ $item->variant->variant_name ?? '-' }}</td>
                                            <td class="text-right">{{ number_format($item->quantity_sold, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada item</td></tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="5" class="text-right font-weight-bold">TOTAL</td>
                                            <td class="text-right font-weight-bold text-success">Rp {{ number_format($settlement->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $('#btn-submit').on('click', function() {
        swal({ title: 'Submit Settlement?', text: 'Settlement akan dikirim untuk diverifikasi Beautylatory.', icon: 'info', buttons: true })
            .then(ok => {
                if (ok) $.post("{{ route('admin.settlements.submit', $settlement->id) }}", { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.status === 'success') { swal('Berhasil', res.message, 'success').then(() => location.reload()); }
                    else swal('Error', res.message, 'error');
                });
            });
    });

    $('#btn-verify').on('click', function() {
        swal({ title: 'Verifikasi & Setujui?', icon: 'warning', buttons: true })
            .then(ok => {
                if (ok) $.post("{{ route('admin.settlements.verify', $settlement->id) }}", { _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.status === 'success') { swal('Diverifikasi!', res.message, 'success').then(() => location.reload()); }
                    else swal('Error', res.message, 'error');
                });
            });
    });

    $('#btn-reject').on('click', function() {
        swal({ title: 'Tolak Settlement?', content: { element: 'input', attributes: { placeholder: 'Alasan penolakan...' } }, icon: 'warning', buttons: true, dangerMode: true })
            .then(reason => {
                if (reason !== null) {
                    $.post("{{ route('admin.settlements.reject', $settlement->id) }}", { _token: '{{ csrf_token() }}', reason: reason }, function(res) {
                        if (res.status === 'success') { swal('Ditolak', res.message, 'success').then(() => location.reload()); }
                        else swal('Error', res.message, 'error');
                    });
                }
            });
    });
</script>
@endpush
