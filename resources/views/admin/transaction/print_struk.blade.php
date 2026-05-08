<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaction->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
            color: #333;
            background: #fff;
        }

        .struk-container {
            max-width: 300px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 15px;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
        }

        .info-section {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .info-label {
            font-weight: bold;
            min-width: 80px;
        }

        .info-value {
            text-align: right;
            flex: 1;
        }

        .items-section {
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }

        .item-row {
            margin-bottom: 8px;
            font-size: 11px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }

        .summary-section {
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 11px;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .footer p {
            margin: 3px 0;
        }

        @media print {
            body {
                padding: 0;
            }
            .struk-container {
                border: none;
                max-width: 80mm;
            }
            .no-print {
                display: none;
            }
        }

        .no-print {
            text-align: center;
            margin-top: 20px;
        }

        .no-print button {
            padding: 10px 30px;
            margin: 0 5px;
            font-size: 12px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background: #3498db;
            color: white;
        }

        .no-print button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="struk-container">
        <div class="header">
            @if($storeSetting)
                <h1>{{ $storeSetting->store_name }}</h1>
                <p>{{ $storeSetting->address }}</p>
                @if($storeSetting->phone)
                    <p>Telp: {{ $storeSetting->phone }}</p>
                @endif
            @else
                <h1>BEAUTYLATORY</h1>
            @endif
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">No. Struk</span>
                <span class="info-value">#{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir</span>
                <span class="info-value">{{ $transaction->user ? $transaction->user->name : '-' }}</span>
            </div>
            @if($transaction->customer)
            <div class="info-row">
                <span class="info-label">Customer</span>
                <span class="info-value">{{ $transaction->customer->name }}</span>
            </div>
            @endif
        </div>

        <div class="items-section">
            @foreach($transaction->items->where('parent_item_id', null) as $item)
            <div class="item-row">
                <div class="item-name">{{ $item->product->name }}</div>
                <div class="item-detail">
                    <span>{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    <span style="text-align: right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="summary-section">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            @if($transaction->discount > 0)
            <div class="summary-row">
                <span>Diskon</span>
                <span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="summary-row total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->total_amount - ($transaction->discount ?? 0), 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Metode Bayar</span>
                <span>{{ strtoupper($transaction->payment_method ?? 'CASH') }}</span>
            </div>
            <div class="summary-row">
                <span>Status</span>
                <span>{{ strtoupper($transaction->payment_status) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Terima kasih atas pembelian Anda</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar</p>
            <p style="margin-top: 10px; font-size: 9px;">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
