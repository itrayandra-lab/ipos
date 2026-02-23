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
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $transaction->invoice_number ?? 'Invoice #' . $transaction->id }}</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.sales.invoices.print', $transaction->id) }}"
                                   target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-print"></i> Cetak
                                </a>
                                <button onclick="deleteInvoice({{ $transaction->id }})"
                                        class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Produk & Batch</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaction->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->product->merek->merek_name ?? '' }} {{ $item->product->name ?? '-' }}</strong>
                                            @if($item->batch && $item->batch->variant)
                                                <br><small class="text-muted">Varian: {{ $item->batch->variant->variant_name }}</small>
                                            @endif
                                            <br><small class="text-primary">Batch: {{ $item->batch->batch_no ?? '-' }}</small>
                                        </td>
                                        <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ $item->qty }}</td>
                                        <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                                        <td class="text-right">
                                            {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @if($transaction->tax_amount > 0)
                                    <tr>
                                        <td colspan="5" class="text-right"><strong>Pajak ({{ strtoupper($transaction->tax_type) }} 11%):</strong></td>
                                        <td class="text-right text-primary">
                                            {{ number_format($transaction->tax_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endif
                                    @if($transaction->discount > 0)
                                    <tr>
                                        <td colspan="5" class="text-right"><strong>Diskon:</strong></td>
                                        <td class="text-right text-danger">
                                            - {{ number_format($transaction->discount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                                        <td class="text-right">
                                            <h5 class="mb-0 text-primary">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</h5>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header"><h4>Informasi</h4></div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td>No. Invoice</td>
                                    <td><strong>{{ $transaction->invoice_number ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Tanggal</td>
                                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($transaction->due_date)
                                <tr>
                                    <td>Jatuh Tempo</td>
                                    <td class="text-danger"><strong>{{ \Carbon\Carbon::parse($transaction->due_date)->format('d/m/Y') }}</strong></td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Customer</td>
                                    <td>
                                        {{ $transaction->customer_name
                                            ?? ($transaction->customer ? $transaction->customer->name : 'Umum') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Telepon</td>
                                    <td>
                                        {{ $transaction->customer_phone
                                            ?? ($transaction->customer ? $transaction->customer->phone : '-') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Kasir</td>
                                    <td>{{ $transaction->user->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Tipe Transaksi</td>
                                    <td><span class="badge badge-info">{{ strtoupper($transaction->transaction_type) }}</span></td>
                                </tr>
                                <tr>
                                    <td>Metode Bayar</td>
                                    <td>{{ strtoupper($transaction->payment_method ?? '-') }}</td>
                                </tr>
                                @if($transaction->is_dp)
                                <tr>
                                    <td>Nominal DP</td>
                                    <td class="text-primary"><strong>Rp {{ number_format($transaction->down_payment, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Sisa Pelunasan</td>
                                    <td class="text-warning"><strong>Rp {{ number_format($transaction->total_amount - $transaction->down_payment, 0, ',', '.') }}</strong></td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Status</td>
                                    <td>
                                        @php
                                            $badge = [
                                                'paid'     => 'success',
                                                'unpaid'   => 'danger',
                                                'credit'   => 'info text-dark',
                                                'pending'  => 'warning',
                                                'draft'    => 'secondary',
                                                'canceled' => 'danger',
                                            ];
                                            $status = $transaction->payment_status;
                                        @endphp
                                        <span class="badge badge-{{ $badge[$status] ?? 'secondary' }}">
                                            {{ strtoupper($status === 'credit' ? 'DP (Credit)' : $status) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($transaction->notes)
                                <tr>
                                    <td>Catatan</td>
                                    <td>{{ $transaction->notes }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.sales.invoices.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                            </a>
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
function deleteInvoice(id) {
    if (!confirm('Hapus invoice ini? Stok akan dikembalikan jika status paid.')) return;
    $.ajax({
        url: '/admin/sales/invoices/' + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                alert(res.message);
                window.location.href = '{{ route("admin.sales.invoices.index") }}';
            } else {
                alert(res.message);
            }
        },
        error: function(xhr) {
            alert('Gagal: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
        }
    });
}
</script>
@endpush
