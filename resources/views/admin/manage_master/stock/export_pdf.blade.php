<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok</title>
    <style>
        @page { margin: 1cm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; line-height: 1.3; color: #333; font-size: 10px; }
        .header { text-align: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0; }
        .header h1 { font-size: 17px; color: #2c3e50; margin-bottom: 4px; }
        .header p { font-size: 10px; color: #7f8c8d; }
        .meta { margin: 10px 0; font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9px; }
        th, td { padding: 5px 4px; text-align: left; border: 1px solid #ccc; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 16px; font-size: 9px; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Stok</h1>
        <p>Dicetak pada {{ $meta['printed_at'] ?? date('d-m-Y H:i') }} &bull; Gudang: {{ $meta['warehouse'] ?? 'Semua Gudang' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="28">No</th>
                <th>Produk</th>
                <th width="60">Netto</th>
                <th width="80">Gudang</th>
                <th width="40" class="text-center">Batch</th>
                <th width="45" class="text-center">Stok</th>
                <th width="55" class="text-center">Status</th>
                <th width="75" class="text-right">Nilai Stok</th>
                <th width="65" class="text-center">Kadaluarsa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row['product_name'] }}</td>
                    <td>{{ $row['netto'] }}</td>
                    <td>{{ $row['warehouse_name'] }}</td>
                    <td class="text-center">{{ $row['batch_count'] }}</td>
                    <td class="text-center">{{ $row['total_current_stock'] }}</td>
                    <td class="text-center">{{ ucfirst($row['stock_status']) }}</td>
                    <td class="text-right">Rp {{ number_format($row['stock_value'], 0, ',', '.') }}</td>
                    <td class="text-center">
                        {{ !empty($row['nearest_expiry']) ? \Carbon\Carbon::parse($row['nearest_expiry'])->format('d-m-Y') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data stok</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>&copy; {{ date('Y') }} RAYCORP INDONESIA - IPOS</p>
    </div>
</body>
</html>
