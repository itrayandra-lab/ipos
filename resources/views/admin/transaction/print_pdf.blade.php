<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cetak Data Transaksi</title>
    <style>
        @page {
            margin: 1cm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: sans-serif;
            line-height: 1.2;
            color: #333;
        }

        .container {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .header h1 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            color: #7f8c8d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }

        th, td {
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #ccc;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-amount {
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 9px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Transaksi</h1>
            <p>Dicetak pada {{ date('d-m-Y H:i') }}</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>Customer</th>
                    <th>Produk (Merek + Produk)</th>
                    <th width="70">Subtotal</th>
                    <th width="60">Diskon</th>
                    <th width="70">Total Bayar</th>
                    <th width="60">Status</th>
                    <th width="70">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $index => $transaction)
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>{{ $transaction->customer->name ?? ($transaction->customer_name ?? '-') }}</td>
                        <td>
                            @php
                                $mainItems = $transaction->items->whereNull('parent_item_id');
                            @endphp
                            @foreach ($mainItems as $item)
                                {{ $item->product->merek->name ?? '' }} {{ $item->product->name }} ({{ $item->qty }})@if(!$loop->last), <br> @endif
                            @endforeach
                        </td>
                        <td align="right">{{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
                        <td align="right">{{ number_format($transaction->discount, 0, ',', '.') }}</td>
                        <td align="right" class="total-amount">{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                        <td align="center">{{ ucfirst($transaction->payment_status) }}</td>
                        <td align="center">{{ $transaction->created_at->format('d-m-Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="footer">
            <p>&copy; {{ date('Y') }} RAYCORP INDONESIA - IPOS</p>
        </div>
    </div>
</body>
</html>
