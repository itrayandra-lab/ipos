@extends('master')

@section('title', 'Detail Stock Movement')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.stock_movements.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Movement {{ $movement->reference_number }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.stock_movements.index') }}">Stock Movements</a></div>
                <div class="breadcrumb-item active">Detail</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header"><h4>Informasi Pengiriman</h4></div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">No. Referensi</span>
                                    <strong>{{ $movement->reference_number }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Tanggal</span>
                                    <span>{{ $movement->created_at->format('d/m/Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Status</span>
                                    @php
                                        $badges = ['pending'=>'badge-warning','transit'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'];
                                    @endphp
                                    <span class="badge {{ $badges[$movement->status] ?? 'badge-secondary' }}">{{ strtoupper($movement->status) }}</span>
                                </li>
                                <li class="list-group-item px-0">
                                    <div class="text-muted mb-1">Dari Gudang</div>
                                    <div class="font-weight-bold text-primary">{{ $movement->fromWarehouse->name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $movement->fromWarehouse->address ?? '' }}</div>
                                </li>
                                <li class="list-group-item px-0">
                                    <div class="text-muted mb-1">Ke Gudang</div>
                                    <div class="font-weight-bold text-success">{{ $movement->toWarehouse->name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $movement->toWarehouse->address ?? '' }}</div>
                                </li>
                            </ul>
                        </div>
                        @if(in_array($movement->status, ['pending', 'transit']))
                        <div class="card-footer bg-whitesmoke text-right">
                            @if($movement->status === 'pending')
                                <button type="button" class="btn btn-primary btn-block" id="btn-process-ship">
                                    <i class="fas fa-truck mr-1"></i> Kirim Barang (Ship)
                                </button>
                            @elseif($movement->status === 'transit')
                                <button type="button" class="btn btn-success btn-block" id="btn-process-receive">
                                    <i class="fas fa-check mr-1"></i> Konfirmasi Terima
                                </button>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="card">
                        <div class="card-header"><h4>Catatan</h4></div>
                        <div class="card-body">{{ $movement->notes ?: '-' }}</div>
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-header"><h4>Item Barang</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Produk</th>
                                            <th>Variant</th>
                                            <th>Batch No</th>
                                            <th class="text-center" width="100">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($movement->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="font-weight-bold">{{ $item->product->name ?? '-' }}</td>
                                            <td>{{ $item->variant->variant_name ?? '-' }}</td>
                                            <td><code>{{ $item->batch->batch_no ?? '-' }}</code></td>
                                            <td class="text-center">{{ number_format($item->qty, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h4>Log Aktivitas</h4></div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-primary"></div>
                                    <div class="timeline-event">
                                        <div class="timeline-heading">
                                            <h6 class="timeline-title">Dibuat</h6>
                                        </div>
                                        <div class="text-muted small">oleh <strong>{{ $movement->user->name ?? '-' }}</strong> — {{ $movement->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @if($movement->shipped_at)
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-info"></div>
                                    <div class="timeline-event">
                                        <div class="timeline-heading">
                                            <h6 class="timeline-title">Dikirim (Transit)</h6>
                                        </div>
                                        <div class="text-muted small">{{ $movement->shipped_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @endif
                                @if($movement->received_at)
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-success"></div>
                                    <div class="timeline-event">
                                        <div class="timeline-heading">
                                            <h6 class="timeline-title text-success">Diterima (Completed)</h6>
                                        </div>
                                        <div class="text-muted small">oleh <strong>{{ $movement->receiver->name ?? '-' }}</strong> — {{ $movement->received_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @endif
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
    $('#btn-process-ship').on('click', function() {
        swal({
            title: 'Proses Pengiriman?',
            text: 'Stok di gudang asal akan langsung dikurangi.',
            icon: 'warning', buttons: true, dangerMode: true,
        }).then(ok => {
            if (ok) $.ajax({
                url: "{{ route('admin.stock_movements.ship', $movement->id) }}",
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: res => res.status === 'success'
                    ? swal('Berhasil', res.message, 'success').then(() => location.reload())
                    : swal('Error', res.message, 'error')
            });
        });
    });

    $('#btn-process-receive').on('click', function() {
        swal({
            title: 'Konfirmasi Penerimaan?',
            text: 'Stok akan ditambahkan ke gudang tujuan.',
            icon: 'info', buttons: true,
        }).then(ok => {
            if (ok) $.ajax({
                url: "{{ route('admin.stock_movements.receive', $movement->id) }}",
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: res => res.status === 'success'
                    ? swal('Berhasil', res.message, 'success').then(() => location.reload())
                    : swal('Error', res.message, 'error')
            });
        });
    });
</script>
@endpush
