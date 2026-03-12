<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan #{{ $deliveryNote->delivery_note_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Open Sans', Courier, monospace; font-size: 11px; padding: 20px; }
        .header { display: flex; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; gap: 20px; align-items: flex-start; }
        .header-left { flex: 0 0 40%; }
        .header-left h2 { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .header-left p { margin: 2px 0; font-size: 10px; line-height: 1.3; }
        .header-center { flex: 1; }
        .header-right { flex: 0 0 auto; text-align: right; display: flex; align-items: flex-start; justify-content: flex-end; }
        .header-right img { max-height: 100px; width: auto; max-width: 150px; object-fit: contain; }
        .info-row { display: flex; gap: 40px; margin-bottom: 15px; font-size: 10px; }
        .info-col { flex: 1; }
        .info-col p { margin: 2px 0; display: flex; align-items: flex-start; }
        .info-col strong { display: inline-block; width: 100px; flex-shrink: 0; }
        .info-col .address-text { word-wrap: break-word; flex: 1; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 6px; text-align: left; font-weight: bold; background-color: #141414ff; color: white; font-size: 10px; }
        .table td { border-bottom: 1px solid #000; padding: 6px; font-size: 10px; }
        .table tbody tr:nth-child(odd) td { background-color: rgba(0, 0, 0, 0.1); }
        .table tbody tr:nth-child(even) td { background-color: rgba(0, 0, 0, 0.2); }
        .table td.text-right { text-align: right; }
        .table td.text-center { text-align: center; }
        .footer { margin-top: 30px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-box { text-align: center; width: 30%; }
        .signature-box p { margin: 2px 0; font-size: 10px; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h2>SURAT JALAN</h2>
            @if($storeSetting)
                <p><strong>{{ $storeSetting->store_name }}</strong></p>
                <p>{{ $storeSetting->address }}</p>
                @if($storeSetting->email)
                    <p>Email: {{ $storeSetting->email }}</p>
                @endif
                @if($storeSetting->whatsapp)
                    <p>WA: {{ $storeSetting->whatsapp }}</p>
                @endif
            @endif
        </div>
        <div class="header-center"></div>
        <div class="header-right">
            @if($storeSetting && $storeSetting->logo_path && file_exists(public_path($storeSetting->logo_path)))
                <img src="{{ asset($storeSetting->logo_path) }}" alt="Logo">
            @endif
        </div>
    </div>

    <!-- Info Section (No boxes) -->
    <div class="info-row">
        <div class="info-col">
            <p><strong>Penerima :</strong> <span>{{ $deliveryNote->customer_name ?? ($deliveryNote->customer ? $deliveryNote->customer->name : 'Umum') }}</span></p>
            <p><strong>No Telpon :</strong> <span>{{ $deliveryNote->customer_phone ?? ($deliveryNote->customer ? $deliveryNote->customer->phone : '-') }}</span></p>
            <p><strong>Alamat :</strong> <span class="address-text">{{ $deliveryNote->delivery_address ?? ($deliveryNote->customer ? $deliveryNote->customer->address : '-') }}</span></p>
        </div>
        <div class="info-col">
            <p><strong>No Surat Jalan :</strong> <span>{{ $deliveryNote->delivery_note_no }}</span></p>
            <p><strong>Tanggal :</strong> <span>{{ \Carbon\Carbon::parse($deliveryNote->transaction_date)->format('d/m/Y') }}</span></p>
        </div>
    </div>

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Nama Barang</th>
                <th style="width: 12%;">Qty</th>
                <th style="width: 12%;">Satuan</th>
                <th style="width: 31%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryNote->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    @if($item->product)
                        @if($item->product->merek)
                            <strong>{{ $item->product->merek->name }}</strong> - 
                        @endif
                        {{ $item->product->name }}
                        @if($item->batch && $item->batch->variant && $item->batch->variant->netto)
                            @php
                                $netto = $item->batch->variant->netto;
                            @endphp
                            <br><small>Netto: {{ $netto->netto_value }} {{ $netto->satuan }} | Batch: {{ $item->batch->batch_no }}</small>
                        @elseif($item->batch)
                            <br><small>Batch: {{ $item->batch->batch_no }}</small>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ $item->qty }}</td>
                <td>{{ $item->satuan ?? '-' }}</td>
                <td>{{ $item->description ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p style="font-size: 9px;">Mohon barang diperiksa dengan baik. Barang yang sudah dibeli tidak dapat dikembalikan tanpa perjanjian sebelumnya.</p>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <p>Hormat Kami,</p>
            <div class="signature-line"></div>
            <p>( {{ $deliveryNote->user ? $deliveryNote->user->name : auth()->user()->name }} )</p>
        </div>
        <div class="signature-box">
            <p>Gudang,</p>
            <div class="signature-line"></div>
            <p>( ............ )</p>
        </div>
        <div class="signature-box">
            <p>Penerima,</p>
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
