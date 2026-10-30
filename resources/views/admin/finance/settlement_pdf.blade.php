<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pelunasan Supplier</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Pelunasan Supplier</h2>
        <p>Tanggal Cetak: {{ date('d-m-Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30px">#</th>
                <th>Kode Transaksi</th>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Nama Produk</th>
                <th>Supplier</th>
                <th class="text-center" width="90px">Jumlah Transaksi</th>
                <th class="text-right" width="120px">Total Nilai Transaksi</th>
                <th class="text-center" width="80px">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGrand = 0; @endphp
            @foreach($items as $index => $row)
                @php
                    $merekName = trim($row->merek_name ?? '');
                    $productName = trim($row->product_name ?? '');

                    $originalParts = array_filter([$merekName, $productName]);
                    $finalParts = [];
                    foreach ($originalParts as $p1) {
                        $isSubPart = false;
                        foreach ($originalParts as $p2) {
                            if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                                $isSubPart = true;
                                break;
                            }
                        }
                        if (!$isSubPart) {
                            $finalParts[] = $p1;
                        }
                    }
                    $finalName = implode(' ', array_unique($finalParts));

                    $nettoValue = trim((string) ($row->netto_value ?? ''));
                    $satuan = trim((string) ($row->satuan ?? ''));
                    $size = trim(implode(' ', array_filter([$nettoValue, $satuan])));
                    $finalName = trim($finalName . ' ' . $size);

                    $statusLabel = match ($row->payment_status ?? 'belum') {
                        'lunas' => 'Lunas',
                        'sebagian' => 'Sebagian',
                        default => 'Belum Bayar',
                    };

                    $totalGrand += $row->total_cost;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->transaction_code ?? '-' }}</td>
                    <td>{{ $row->invoice_number ?? '-' }}</td>
                    <td>{{ $row->transaction_date ?? '-' }}</td>
                    <td>{{ $finalName }}</td>
                    <td>{{ $row->supplier_name ?? '-' }}</td>
                    <td class="text-center">{{ $row->total_qty }}</td>
                    <td class="text-right">Rp {{ number_format($row->total_cost, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $statusLabel }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="7" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-right">Rp {{ number_format($totalGrand, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
