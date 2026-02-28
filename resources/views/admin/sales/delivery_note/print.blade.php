<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan #{{ $deliveryNote->delivery_note_no }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 20px; }
        .header-container { display: table; width: 100%; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-left { display: table-cell; width: 40%; vertical-align: top; }
        .header-left img { max-width: 150px; height: auto; }
        .header-center { display: table-cell; width: 20%; text-align: center; vertical-align: middle; }
        .header-center h1 { margin: 0; font-size: 18px; font-weight: bold; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .header-right p { margin: 2px 0; font-size: 11px; line-height: 1.4; }
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
    <div class="header-container">
        <div class="header-left">
            @if(file_exists(public_path('assets/img/logo-black.png')))
                <img src="{{ asset('assets/img/logo-black.png') }}" alt="Logo">
            @endif
        </div>
        <div class="header-center">
            <h1>SURAT JALAN</h1>
        </div>
        <div class="header-right">
            @if($storeSetting)
                <p><strong>{{ $storeSetting->store_name }}</strong></p>
                <p>{{ $storeSetting->address }}</p>
                @if($storeSetting->whatsapp)
                    <p>WA: {{ $storeSetting->whatsapp }}</p>
                @endif
            @endif
        </div>
    </div>

    <table class="info">
        <tr>
            <td style="width: 15%;">No. Dokumen</td>
            <td style="width: 35%;">: {{ $deliveryNote->delivery_note_no }}</td>
            <td style="width: 15%;">Kepada</td>
            <td style="width: 35%;">: {{ $deliveryNote->customer_name ?? ($deliveryNote->customer ? $deliveryNote->customer->name : 'Umum') }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($deliveryNote->transaction_date)->format('d F Y') }}</td>
            <td>Telepon</td>
            <td>: {{ $deliveryNote->customer_phone ?? ($deliveryNote->customer ? $deliveryNote->customer->phone : '-') }}</td>
        </tr>
        <tr>
            <td>Ref Dokumen</td>
            <td>: #{{ $deliveryNote->id }}</td>
            <td>Pengiriman</td>
            <td>: {{ strtoupper($deliveryNote->delivery_type) }}</td>
        </tr>
        @if($deliveryNote->notes)
        <tr>
            <td>Catatan</td>
            <td colspan="3">: {{ $deliveryNote->notes }}</td>
        </tr>
        @endif
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 10%;">No</th>
                <th>Nama Barang</th>
                <th style="width: 20%;">Batch</th>
                <th style="width: 15%; text-align: right;">Jumlah</th>
                <th style="width: 15%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryNote->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->batch ? $item->batch->batch_no : '-' }}</td>
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
            <td>( {{ $deliveryNote->user ? $deliveryNote->user->name : auth()->user()->name }} )</td>
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
