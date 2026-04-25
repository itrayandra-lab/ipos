<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Per Produk</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; text-align: right; }
        @media print {
            .no-print { display: none; }
            body { margin: 20px; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Laporan Penjualan Per Produk</h2>
        <p>Aplikasi POS Beautylatory</p>
    </div>

    <div class="info">
        <strong>Periode:</strong> {{ request('start_date') ?? '-' }} s/d {{ request('end_date') ?? '-' }}<br>
        <strong>Dicetak Pada:</strong> {{ date('d-m-Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">#</th>
                <th>Nama Produk</th>
                <th>Variant</th>
                <th class="text-center">Qty Terjual</th>
                <th class="text-right">Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; $totalQty = 0; @endphp
            @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->merek_name }} {{ $item->product_name }}</strong>
                    </td>
                    <td>{{ $item->variant_name ?? '-' }}</td>
                    <td class="text-center">{{ $item->total_qty }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                </tr>
                @php 
                    $grandTotal += $item->total_amount;
                    $totalQty += $item->total_qty;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ $totalQty }}</td>
                <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak secara otomatis oleh sistem.</p>
    </div>
</body>
</html>
