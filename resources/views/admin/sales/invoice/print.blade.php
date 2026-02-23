<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction->invoice_number ?? '#'.$transaction->id }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .info { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info td { vertical-align: top; padding: 2px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border-bottom: 1px dashed #ccc; padding: 8px; text-align: left; }
        .table th { border-top: 1px dashed #ccc; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; }
        .signatures { width: 100%; margin-top: 50px; }
        .signatures td { text-align: center; width: 33%; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>INVOICE PENJUALAN</h1>
        <p><strong>IPOS SYSTEM</strong></p>
    </div>

    <table class="info">
        <tr>
            <td style="width: 15%;">No. Invoice</td>
            <td style="width: 35%;">: {{ $transaction->invoice_number ?? 'INV-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td style="width: 15%;">Kepada</td>
            <td style="width: 35%;">: {{ $transaction->customer_name ?? ($transaction->customer ? $transaction->customer->name : 'Umum') }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($transaction->created_at)->format('d F Y') }}</td>
            <td>Telepon</td>
            <td>: {{ $transaction->customer_phone ?? ($transaction->customer ? $transaction->customer->phone : '-') }}</td>
        </tr>
        @if($transaction->due_date)
        <tr>
            <td>Jatuh Tempo</td>
            <td>: {{ \Carbon\Carbon::parse($transaction->due_date)->format('d F Y') }}</td>
            <td>Tipe / Status</td>
            <td>: {{ strtoupper($transaction->transaction_type) }} / {{ strtoupper($transaction->payment_status) }}</td>
        </tr>
        @else
        <tr>
            <td>Kasir</td>
            <td>: {{ $transaction->user->name }}</td>
            <td>Tipe / Status</td>
            <td>: {{ strtoupper($transaction->transaction_type) }} / {{ strtoupper($transaction->payment_status) }}</td>
        </tr>
        @endif
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->product->merek->merek_name ?? '' }} {{ $item->product->name }}</strong>
                    @if($item->batch && $item->batch->variant)
                        <br><small>Varian: {{ $item->batch->variant->variant_name }}</small>
                    @endif
                    <br><small>Batch: {{ $item->batch->batch_no ?? '-' }}</small>
                </td>
                <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="text-right">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Subtotal:</th>
                <td class="text-right font-weight-bold">{{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
            </tr>
            @if($transaction->tax_amount > 0)
            <tr>
                <th colspan="3" class="text-right">Pajak ({{ strtoupper($transaction->tax_type) }} 11%):</th>
                <td class="text-right">{{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($transaction->discount > 0)
            <tr>
                <th colspan="3" class="text-right">Diskon:</th>
                <td class="text-right text-danger">- {{ number_format($transaction->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <th colspan="3" class="text-right">Grand Total:</th>
                <td class="text-right font-weight-bold" style="font-size: 1.1em; color: #007bff;">
                    Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                </td>
            </tr>
            @if($transaction->is_dp)
            <tr>
                <th colspan="3" class="text-right" style="color: #17a2b8;">DP (Sudah Dibayar):</th>
                <td class="text-right" style="color: #17a2b8;">{{ number_format($transaction->down_payment, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th colspan="3" class="text-right" style="color: #fd7e14;">Sisa Pelunasan:</th>
                <td class="text-right" style="color: #fd7e14; font-weight: bold;">{{ number_format($transaction->total_amount - $transaction->down_payment, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    <div class="footer">
        <p><em>* Terbilang: {{ \App\Helpers\Terbilang::make($transaction->total_amount) }} rupiah</em></p>
    </div>

    <table class="signatures">
        <tr>
            <td>Hormat Kami,</td>
            <td>Gudang,</td>
            <td>Penerima,</td>
        </tr>
        <tr style="height: 60px;">
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>( {{ auth()->user()->name }} )</td>
            <td>( ............ )</td>
            <td>( ............ )</td>
        </tr>
    </table>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
