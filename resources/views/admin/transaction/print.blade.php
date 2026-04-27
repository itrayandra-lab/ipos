<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Transaksi</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 30px;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .header img {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            color: #7f8c8d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #3498db;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e8f4f8;
        }

        td {
            color: #34495e;
        }

        .total-amount {
            font-weight: bold;
            color: #e74c3c;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #7f8c8d;
        }

        @media print {
            body {
                margin: 0;
                background-color: #fff;
            }

            .container {
                box-shadow: none;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .header img {
                max-width: 100px;
            }

            th {
                background-color: #3498db !important;
                -webkit-print-color-adjust: exact;
            }

            tr:nth-child(even) {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h1>Data Transaksi</h1>
            <p>Laporan Transaksi - Dicetak pada {{ date('d-m-Y H:i') }}</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th>Produk (Merek + Produk)</th>
                    <th>Subtotal</th>
                    <th>Diskon</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transaction->customer->name ?? ($transaction->customer_name ?? '-') }}</td>
                        <td>
                            @php
                                $mainItems = $transaction->items->whereNull('parent_item_id');
                            @endphp
                            @foreach ($mainItems as $item)
                                {{ $item->product->merek->name ?? '' }} {{ $item->product->name }} ({{ $item->qty }})@if(!$loop->last), <br> @endif
                            @endforeach
                        </td>
                        <td>Rp. {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
                        <td>Rp. {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                        <td class="total-amount">Rp. {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($transaction->payment_status == 'paid')
                                <span style="color: green; font-weight: bold;">Lunas</span>
                            @elseif($transaction->payment_status == 'unpaid')
                                <span style="color: red; font-weight: bold;">Belum Bayar</span>
                            @elseif($transaction->payment_status == 'credit')
                                <span style="color: orange; font-weight: bold;">Kredit/DP</span>
                            @else
                                {{ ucfirst($transaction->payment_status) }}
                            @endif
                        </td>
                        <td>{{ $transaction->created_at->format('d-m-Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="footer">
            <p>&copy; {{ date('Y') }} RAYCORP INDONESIA Semua hak dilindungi.</p>
            <p>Dicetak oleh: IPOS</p>
        </div>
    </div>
</body>
</html>