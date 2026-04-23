@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Detail Invoice</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.invoices.index') }}">Invoice</a></div>
                <div class="breadcrumb-item active">{{ $transaction->invoice_number }}</div>
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

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-warning alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Rincian Produk</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sales.invoices.print', $transaction->id) }}" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-print"></i> Cetak Invoice
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
                                            <th class="text-right">Harga Satuan</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transaction->items->where('parent_item_id', null) as $i => $item)
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
                                                </div>
                                            </td>
                                            <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td class="text-right font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-whitesmoke text-right">
                            <div class="d-flex justify-content-end align-items-center">
                                <div class="mr-5 text-muted">Subtotal</div>
                                <div class="h6 mb-0">Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</div>
                            </div>
                            @if($transaction->tax_amount > 0)
                            <div class="d-flex justify-content-end align-items-center mt-1">
                                <div class="mr-5 text-muted">Pajak ({{ strtoupper($transaction->tax_type) }})</div>
                                <div class="h6 mb-0 text-info">+ Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</div>
                            </div>
                            @endif
                            @if($transaction->discount > 0)
                            <div class="d-flex justify-content-end align-items-center mt-1">
                                <div class="mr-5 text-muted">Diskon</div>
                                <div class="h6 mb-0 text-danger">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</div>
                            </div>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-end align-items-center">
                                <div class="mr-5 h5 mb-0">Grand Total</div>
                                <div class="h4 mb-0 text-primary font-weight-700">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h4>Status & Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="font-weight-bold text-muted small text-uppercase">Status Invoice</label>
                                <div>
                                    @php
                                        $totalPaid = $transaction->payments->sum('amount');
                                        $statusLabel = $transaction->payment_status;
                                        $badgeClass = 'secondary';

                                        if ($transaction->payment_status === 'paid') {
                                            $statusLabel = 'Lunas';
                                            $badgeClass = 'success';
                                        } elseif ($transaction->payment_status === 'credit') {
                                            if ($totalPaid > 0) {
                                                $statusLabel = 'DP Terbayar';
                                                $badgeClass = 'info';
                                            } else {
                                                $statusLabel = 'Menunggu DP';
                                                $badgeClass = 'warning';
                                            }
                                        } elseif ($transaction->payment_status === 'unpaid') {
                                            $statusLabel = 'Belum Bayar';
                                            $badgeClass = 'danger';
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }} px-3 py-2">
                                        {{ strtoupper($statusLabel) }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase mb-0">Metode Bayar</label>
                                    <div class="font-weight-bold">{{ strtoupper($transaction->payment_method) }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase mb-0">Tanggal</label>
                                    <div class="font-weight-bold">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</div>
                                </div>
                            </div>

                            @php
                                $balance = $transaction->total_amount - $totalPaid;
                            @endphp

                            @if($transaction->payment_status === 'credit' && $totalPaid == 0)
                            <div class="alert alert-warning py-2 px-3 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small font-weight-bold">Down Payment (DP) Tagihan</span>
                                    <span class="font-weight-bold text-dark">Rp {{ number_format($transaction->down_payment, 0, ',', '.') }}</span>
                                </div>
                                <hr class="my-1 border-white opacity-2" style="border-top: 1px solid rgba(0,0,0,0.1)">
                                <div class="small text-muted italic">Silakan upload bukti bayar untuk memulai proses.</div>
                            </div>
                            @endif

                            <div class="alert alert-{{ $balance <= 0 ? 'success' : 'info' }} py-2 px-3 mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">Total Terbayar</span>
                                    <span class="font-weight-bold">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                                </div>
                                <hr class="my-1 border-white opacity-2" style="border-top: 1px solid rgba(255,255,255,0.3)">
                                <div class="d-flex justify-content-between">
                                    <span class="small">Sisa Tagihan</span>
                                    <span class="font-weight-bold">Rp {{ number_format($balance > 0 ? $balance : 0, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="font-weight-bold text-muted small text-uppercase mb-0">Riwayat Pembayaran</label>
                                @if($balance > 0)
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#receiptModal">
                                        <i class="fas fa-plus mr-1"></i> Tambah
                                    </button>
                                @endif
                            </div>

                            @if($transaction->payments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless small">
                                        <thead>
                                            <tr class="border-bottom">
                                                <th>Tgl</th>
                                                <th class="text-right">Nominal</th>
                                                <th class="text-center">Bukti</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transaction->payments as $payment)
                                            <tr class="border-bottom">
                                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/y') }}</td>
                                                <td class="text-right font-weight-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if($payment->payment_receipt)
                                                        <a href="{{ asset($payment->payment_receipt) }}" target="_blank" class="text-info">
                                                            <i class="fas fa-image"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3 border rounded mb-3 bg-light">
                                    <p class="small text-muted mb-0">Belum ada riwayat pembayaran</p>
                                </div>
                            @endif

                            @if($transaction->payment_status === 'credit' && $balance > 0)
                                <button type="button" class="btn btn-warning btn-block font-weight-bold py-2 mt-3 shadow-sm" data-toggle="modal" data-target="#settleModal">
                                    <i class="fas fa-check-circle mr-1"></i> PROSES PELUNASAN
                                </button>
                            @endif

                            @if($transaction->payment_status === 'paid')
                                <a href="{{ route('admin.sales.receipts.print', $transaction->id) }}" target="_blank" class="btn btn-outline-primary btn-block font-weight-bold py-2 mt-3 shadow-sm">
                                    <i class="fas fa-print mr-1"></i> CETAK KUITANSI
                                </a>
                            @endif

                            <div class="mb-4 border-top pt-3 mt-3">
                                <label class="font-weight-bold text-muted small text-uppercase">Customer</label>
                                <div class="h6 mb-0">{{ $transaction->customer_name ?? ($transaction->customer->name ?? 'Pelanggan Umum') }}</div>
                                <div class="text-muted small">{{ $transaction->customer_phone ?? ($transaction->customer->phone ?? '-') }}</div>
                            </div>

                            <div class="mb-0 border-top pt-3 text-muted small">
                                <div>Dibuat Oleh: <strong>{{ $transaction->user->name ?? '-' }}</strong></div>
                                @if($transaction->notes)
                                <div class="mt-2">
                                    <strong>Catatan:</strong><br>
                                    {{ $transaction->notes }}
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer border-top text-center">
                            <button onclick="deleteInvoice({{ $transaction->id }})" class="btn btn-outline-danger btn-sm btn-block">
                                <i class="fas fa-trash"></i> Hapus Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Tambah Pembayaran -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.sales.invoices.upload-receipt', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Tambah Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nominal Pembayaran (Rp)</label>
                        <input type="number" name="amount" class="form-control" value="{{ $balance }}" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Bayar</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Pilih Bukti (PNG, JPG, JPEG, PDF) - Opsional</label>
                        <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                        <small class="text-muted">Maksimal 2MB.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label>Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Pembayaran DP, Cicilan #1, dll"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pelunasan -->
<div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-labelledby="settleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.sales.invoices.settle', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="settleModalLabel">Konfirmasi Pelunasan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin memproses pelunasan untuk invoice <strong>{{ $transaction->invoice_number }}</strong>?</p>
                    <div class="alert alert-warning py-2 mb-3 shadow-sm border-0">
                        <div class="d-flex justify-content-between">
                            <span>Sisa yang harus dibayar:</span>
                            <span class="h5 mb-0 font-weight-bold text-dark">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Upload Bukti Pelunasan (Opsional)</label>
                        <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                        <small class="text-muted">Bukti ini akan dicatat sebagai pembayaran pelunasan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold px-4">PROSES LUNAS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteInvoice(id) {
    if (!confirm('Hapus invoice ini? Tindakan ini akan mengembalikan stok jika status invoice Lunas atau DP.')) return;
    $.ajax({
        url: '/admin/sales/invoices/' + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                iziToast.success({ title: 'Berhasil', message: 'Invoice telah dihapus', position: 'topRight' });
                setTimeout(() => window.location.href = '{{ route("admin.sales.invoices.index") }}', 1000);
            } else {
                iziToast.error({ title: 'Gagal', message: res.message, position: 'topRight' });
            }
        },
        error: function(xhr) {
            iziToast.error({ title: 'Error', message: xhr.responseJSON?.message || 'Gagal menghapus invoice', position: 'topRight' });
        }
    });
}
</script>
@endpush
