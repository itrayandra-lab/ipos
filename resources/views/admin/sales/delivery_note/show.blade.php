@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Detail Surat Jalan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></div>
                <div class="breadcrumb-item active">{{ $deliveryNote->delivery_note_no }}</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('message'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ session('message') }}
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Rincian Barang</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sales.delivery_notes.print', $deliveryNote->id) }}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-print"></i> Cetak Surat Jalan
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">#</th>
                                            <th>Item & Deskripsi</th>
                                            <th class="text-center">Qty</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deliveryNote->items as $i => $item)
                                        @php
                                            $merek = trim($item->product->merek->name ?? '');
                                            $name = trim($item->product->name ?? '');
                                            $variant = trim($item->batch->variant->variant_name ?? '');
                                            
                                            // Deduplicate logic
                                            $parts = array_filter([$merek, $name, $variant]);
                                            $finalParts = [];
                                            foreach($parts as $p1) {
                                                $isSub = false;
                                                foreach($parts as $p2) {
                                                    if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                                                        $isSub = true; break;
                                                    }
                                                }
                                                if(!$isSub) $finalParts[] = $p1;
                                            }
                                            $displayLabel = implode(' ', array_unique($finalParts));
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>
                                                <div class="font-weight-600 text-primary">{{ $displayLabel }}</div>
                                                <div class="text-muted small">
                                                    Batch: <span class="text-info">{{ $item->batch->batch_no ?? '-' }}</span>
                                                    @if($item->batch && $item->batch->variant && $item->batch->variant->netto)
                                                        | Netto: {{ $item->batch->variant->netto->netto_value }} {{ $item->batch->variant->netto->satuan }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td>{{ $item->description ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-whitesmoke">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="font-weight-bold text-muted small text-uppercase">Catatan</label>
                                    <p>{{ $deliveryNote->notes ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h4>Informasi Pengiriman</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4 text-center">
                                <label class="font-weight-bold text-muted small text-uppercase d-block">Nomor Surat Jalan</label>
                                <div class="h5 font-weight-bold text-primary">{{ $deliveryNote->delivery_note_no }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase mb-0">Tipe</label>
                                    <div class="font-weight-bold">
                                        <span class="badge badge-{{ $deliveryNote->delivery_type === 'pickup' ? 'info' : 'primary' }}">
                                            {{ strtoupper($deliveryNote->delivery_type) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase mb-0">Tanggal</label>
                                    <div class="font-weight-bold">{{ \Carbon\Carbon::parse($deliveryNote->transaction_date)->format('d/m/Y') }}</div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase">Penerima / Customer</label>
                                <div class="h6 mb-0">{{ $deliveryNote->customer_name ?? ($deliveryNote->customer->name ?? 'Pelanggan Umum') }}</div>
                                <div class="text-muted small">{{ $deliveryNote->customer_phone ?? ($deliveryNote->customer->phone ?? '-') }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase">Alamat Pengiriman</label>
                                <div class="text-muted small">{{ $deliveryNote->delivery_address ?? '-' }}</div>
                            </div>

                            <div class="mb-0 border-top pt-3 text-muted small">
                                <div>Dibuat Oleh: <strong>{{ $deliveryNote->user->name ?? '-' }}</strong></div>
                                <div class="mt-1">Waktu: {{ \Carbon\Carbon::parse($deliveryNote->created_at)->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="card-footer border-top text-center">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('admin.sales.delivery_notes.edit', $deliveryNote->id) }}" class="btn btn-warning btn-sm btn-block">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                                <div class="col-6">
                                    <button onclick="deleteDeliveryNote({{ $deliveryNote->id }})" class="btn btn-outline-danger btn-sm btn-block">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
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
function deleteDeliveryNote(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus Surat Jalan ini? Stok barang akan dikembalikan.')) return;
    $.ajax({
        url: '{{ url("admin/sales/delivery-notes") }}/' + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                iziToast.success({ title: 'Berhasil', message: 'Surat Jalan telah dihapus', position: 'topRight' });
                setTimeout(() => window.location.href = '{{ route("admin.sales.delivery_notes.index") }}', 1000);
            } else {
                iziToast.error({ title: 'Gagal', message: res.message, position: 'topRight' });
            }
        },
        error: function(xhr) {
            iziToast.error({ title: 'Error', message: xhr.responseJSON?.message || 'Gagal menghapus surat jalan', position: 'topRight' });
        }
    });
}
</script>
@endpush
