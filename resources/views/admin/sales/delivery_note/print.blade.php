<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan #{{ $transaction->id }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .info { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info td { vertical-align: top; padding: 2px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border-bottom: 1px dashed #ccc; padding: 8px; text-align: left; }
        .table th { border-top: 1px dashed #ccc; }
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
        <h1>SURAT JALAN</h1>
        <p><strong>IPOS SYSTEM</strong></p>
    </div>

    <table class="info">
        <tr>
            <td style="width: 15%;">No. Dokumen</td>
            <td style="width: 35%;">: SJ-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td style="width: 15%;">Kepada</td>
            <td style="width: 35%;">: {{ $transaction->customer_name ?? ($transaction->customer ? $transaction->customer->name : 'Umum') }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($transaction->created_at)->format('d F Y') }}</td>
            <td>Telepon</td>
            <td>: {{ $transaction->customer_phone ?? ($transaction->customer ? $transaction->customer->phone : '-') }}</td>
        </tr>
        <tr>
            <td>Ref Transaksi</td>
            <td>: #{{ $transaction->id }}</td>
            <td>Pengiriman</td>
            <td>: {{ strtoupper($transaction->delivery_type) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 10%;">No</th>
                <th>Nama Barang</th>
                <th style="width: 15%; text-align: right;">Jumlah</th>
                <th style="width: 15%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td style="text-align: right;">{{ $item->qty }}</td>
                <td>{{ $item->product->pieces ?? 'pcs' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Mohon barang diperiksa dengan baik. Barang yang sudah dibeli tidak dapat dikembalikan tanpa perjanjian sebelumnya.</p>
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
