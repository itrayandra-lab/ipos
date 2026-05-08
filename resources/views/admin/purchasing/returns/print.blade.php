<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Barang #{{ $return->return_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Nunito', sans-serif; font-size: 11px; padding: 20px; color: #333; }
        .header { display: flex; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; gap: 20px; align-items: flex-start; }
        .header-left { flex: 0 0 40%; }
        .header-left h2 { font-size: 20px; font-weight: bold; margin-bottom: 5px; color: #000; }
        .header-left p { margin: 2px 0; font-size: 10px; line-height: 1.3; }
        .header-center { flex: 1; }
        .header-right { flex: 0 0 auto; text-align: right; display: flex; align-items: flex-start; justify-content: flex-end; }
        .header-right img { max-height: 80px; width: auto; max-width: 150px; object-fit: contain; }
        .info-row { display: flex; gap: 40px; margin-bottom: 15px; font-size: 10px; }
        .info-col { flex: 1; }
        .info-col p { margin: 2px 0; display: flex; align-items: flex-start; }
        .info-col strong { display: inline-block; width: 100px; flex-shrink: 0; }
        .info-col .address-text { word-wrap: break-word; flex: 1; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px 6px; font-weight: bold; background-color: #f8f9fa !important; color: #000 !important; font-size: 10px; text-transform: uppercase; -webkit-print-color-adjust: exact; }
        .table td { border-bottom: 1px solid #000; padding: 6px; font-size: 10px; vertical-align: top; }
        .table tbody tr:nth-child(odd) td { background-color: rgba(0, 0, 0, 0.02); }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .footer { margin-top: 30px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-box { text-align: center; width: 30%; }
        .signature-box p { margin: 2px 0; font-size: 10px; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .table th { background-color: #f8f9fa !important; color: #000 !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h2>RETURN BARANG</h2>
            @if($setting)
                <p><strong>{{ $setting->store_name }}</strong></p>
                <p>{{ $setting->address }}</p>
                @if($setting->email)
                    <p>Email: {{ $setting->email }}</p>
                @endif
                @if($setting->whatsapp)
                    <p>WA: {{ $setting->whatsapp }}</p>
                @endif
            @endif
        </div>
        <div class="header-center"></div>
        <div class="header-right">
            @if($setting && $setting->logo_path)
                <img src="{{ asset($setting->logo_path) }}" alt="Logo">
            @endif
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-row">
        <div class="info-col">
            <p><strong>Supplier :</strong> <span>{{ $return->supplier->name }}</span></p>
            <p><strong>Gudang :</strong> <span>{{ $return->warehouse->name }}</span></p>
            <p><strong>Alamat :</strong> <span class="address-text">{{ $return->supplier->address ?: '-' }}</span></p>
        </div>
        <div class="info-col">
            <p><strong>No Return :</strong> <span>{{ $return->return_number }}</span></p>
            <p><strong>Tanggal :</strong> <span>{{ \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') }}</span></p>
        </div>
    </div>

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 45%;">Nama Barang</th>
                <th class="text-center" style="width: 15%;">Batch No</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th style="width: 25%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($return->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    @php
                        $merek = $item->product->merek ? $item->product->merek->name . ' ' : '';
                        $netto = ($item->variant && $item->variant->netto) ? ' ' . $item->variant->netto->netto_value . ' ' . $item->variant->netto->satuan : '';
                    @endphp
                    <strong>{{ $merek }}{{ $item->product->name }}{{ $netto }}</strong>
                </td>
                <td class="text-center">{{ $item->batch ? $item->batch->batch_no : '-' }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td>{{ $item->reason ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p style="font-size: 9px;">Catatan: {{ $return->notes ?: 'Tidak ada catatan.' }}</p>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <p>Dibuat Oleh,</p>
            <div class="signature-line"></div>
            <p>( {{ $return->user ? $return->user->name : auth()->user()->name }} )</p>
        </div>
        <div class="signature-box">
            <p>Gudang,</p>
            <div class="signature-line"></div>
            <p>( ............ )</p>
        </div>
        <div class="signature-box">
            <p>Mengetahui,</p>
            <div class="signature-line"></div>
            <p>( ............ )</p>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
