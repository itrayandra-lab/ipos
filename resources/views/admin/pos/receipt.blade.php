<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian - {{ $transaction->midtrans_order_id }}</title>
    <style>
        @page { size: 58mm 210mm; margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm;
            margin: 0;
            padding: 5mm;
            font-size: 12px;
            line-height: 1.2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        .price-col { text-align: right; width: 40%; }
        .qty-col { width: 15%; text-align: center; }
        .footer { margin-top: 20px; font-size: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #eee; padding: 10px; margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()">Cetak Sekarang</button>
    </div>

    @php
        $store = \App\Models\StoreSetting::getActiveSetting();
    @endphp
    <div class="text-center">
        @if($store->logo_path && file_exists(public_path($store->logo_path)))
            <img src="{{ asset($store->logo_path) }}" alt="Logo" style="max-height: 50px; margin-bottom: 5px;">
        @else
            <h3 style="margin: 0;">{{ $store->store_name }}</h3>
        @endif
        
        <p style="margin: 0; font-size: 10px;">
            {!! nl2br(e($store->address)) !!}<br>
            @if($store->whatsapp) WhatsApp: {{ $store->whatsapp }} @endif
        </p>
    </div>

    <div class="divider"></div>

    <table style="font-size: 10px;">
        <tr>
            <td>Tgl: {{ $transaction->created_at->format('d/m/y H:i') }}</td>
        </tr>
        <tr>
            <td>Ref: {{ $transaction->midtrans_order_id }}</td>
        </tr>
        <tr>
            <td>Kasir: {{ $transaction->user->name }}</td>
        </tr>
        @if($transaction->customer_name)
        <tr>
            <td>Cust: {{ $transaction->customer_name }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table>
        @foreach($transaction->items->groupBy('product_id') as $productId => $group)
            @php
                $firstItem = $group->first();
                $totalQty = $group->sum('qty');
                $totalSubtotal = $group->sum('subtotal');
            @endphp
            <tr>
                <td colspan="3">{{ $firstItem->product->name }}</td>
            </tr>
            <tr>
                <td class="qty-col">{{ $totalQty }}x</td>
                <td style="font-size: 10px;">@ Rp {{ number_format($firstItem->price, 0, ',', '.') }}</td>
                <td class="price-col">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="bold">Subtotal</td>
            <td class="price-col bold">Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
        </tr>
        @if($transaction->discount > 0)
        <tr>
            <td>Diskon (@if($transaction->voucher_code) {{ $transaction->voucher_code }} @else Manual @endif)</td>
            <td class="price-col">-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="bold h3" style="font-size: 14px;">TOTAL</td>
            <td class="price-col bold h3" style="font-size: 14px;">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table style="font-size: 10px;">
        <tr>
            @php
                $methodLabels = [
                    'cash' => 'CASH',
                    'qris' => 'QR / QRIS',
                    'transfer' => 'TRANSFER',
                    'debit' => 'DEBIT (EDC BCA)'
                ];
                $method = $methodLabels[$transaction->payment_method] ?? strtoupper($transaction->payment_method);
            @endphp
            <td>Metode: {{ $method }}</td>
            <td class="text-right">Status: {{ strtoupper($transaction->payment_status) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer">
        <p class="font-weight-bold">TERIMA KASIH</p>
        <p class="small">{{ $store->footer_text }}</p>
        
        @if($store->instagram || $store->tiktok || $store->website)
        <div style="margin-top: 10px; font-size: 10px;">
            @if($store->instagram) IG: {{ basename($store->instagram) }}<br> @endif
            @if($store->tiktok) TikTok: {{ basename($store->tiktok) }}<br> @endif
            @if($store->website) {{ $store->website }}<br> @endif
        </div>
        @endif
    </div>

    <script>
        window.onload = function() {
            // Optional: Auto print
            // window.print();
        }
    </script>
</body>
</html>
