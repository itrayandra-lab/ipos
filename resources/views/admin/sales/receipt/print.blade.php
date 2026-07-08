<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran #{{ $transaction->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Birthstone&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; font-size: 14px; margin: 0; padding: 20px; color: #333; }
        .receipt-container { 
            border: 2px solid #555; 
            padding: 30px; 
            background-color: #fff;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; text-decoration: underline; letter-spacing: 2px; color: #000; }
        
        .top-info { 
            display: flex; 
            flex-direction: column;
            align-items: flex-end; 
            margin-bottom: 25px; 
            gap: 5px;
        }
        .top-info p { margin: 0; font-weight: 700; font-size: 13px; }
        
        .content { margin-bottom: 35px; }
        .row { display: flex; align-items: flex-start; margin-bottom: 18px; }
        .label { width: 160px; font-weight: 600; }
        .value { flex: 1; border-bottom: 1px dotted #000; padding-bottom: 2px; font-weight: 500; }
        .terbilang-value { 
            font-family: 'Birthstone', cursive; 
            font-size: 24px; 
            font-style: italic;
            padding-bottom: 0;
            line-height: 1;
            color: #000;
        }
        
        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; }
        .amount-box { 
            border: 3px solid #000; 
            padding: 12px 20px; 
            font-size: 20px; 
            font-weight: 800; 
            min-width: 220px;
            transform: skew(-15deg);
            background: #fff;
        }
        .amount-box span {
            display: block;
            transform: skew(15deg);
        }
        
        .signatures { display: flex; gap: 60px; }
        .signature-box { text-align: center; width: 160px; }
        .signature-line { border-bottom: 1px solid #000; margin-bottom: 8px; height: 70px; }
        .signature-box p { margin: 0; font-size: 12px; font-weight: 600; }
        
        .logo-placeholder { position: absolute; top: 30px; left: 30px; }
        .logo-placeholder img { max-height: 70px; width: auto; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .receipt-container { border: 2px solid #000; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt-container">
        @if(isset($setting) && $setting->logo_path)
        <div class="logo-placeholder">
            <img src="{{ asset($setting->logo_path) }}" alt="Logo">
        </div>
        @elseif(file_exists(public_path('assets/img/logo-black.png')))
        <div class="logo-placeholder">
            <img src="{{ asset('assets/img/logo-black.png') }}" alt="Logo">
        </div>
        @endif

        <div class="header">
            <h1>KWITANSI PEMBAYARAN</h1>
        </div>
        
        <div class="top-info">
            <p>No: {{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p>Tanggal: {{ $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : '-' }}</p>
        </div>

        <div class="content">
            <div class="row">
                <div class="label">Terima Dari</div>
                <div class="value">: {{ $transaction->customer_name ?? ($transaction->customer ? $transaction->customer->name : 'Umum') }}</div>
            </div>
            <div class="row">
                <div class="label">Terbilang</div>
                <div class="value terbilang-value">: {{ ucwords(\App\Helpers\Terbilang::make($transaction->total_amount)) }} Rupiah</div>
            </div>
            
            @php
                $produkList = [];
                // Handle different relations depending on where this is called from, fallback to items
                $items = $transaction->items ?? [];
                
                foreach($items as $item) {
                     $merek = trim($item->product->merek->name ?? '');
                     $name = trim($item->product->name ?? '');
                     $variant = trim($item->batch->variant->variant_name ?? '');
                     
                     $parts = array_filter([$merek, $name, $variant]);
                     $finalParts = [];
                     foreach($parts as $p1) {
                         $isSub = false;
                         foreach($parts as $p2) {
                             if ($p1 !== $p2 && stripos($p2, $p1) !== false && strlen($p2) > strlen($p1)) {
                                 $isSub = true; break;
                             }
                         }
                         if(!$isSub) $finalParts[] = $p1;
                     }
                     $displayLabel = implode(' ', array_unique($finalParts));
                     if ($item->qty > 1) {
                         $displayLabel .= ' (' . $item->qty . 'x)';
                     }
                     $produkList[] = $displayLabel;
                }
                $produkString = implode(', ', $produkList);
            @endphp
            
            <div class="row" style="align-items: flex-start;">
                <div class="label">Untuk Pembayaran</div>
                <div class="value" style="border-bottom:none;">
                    : <span style="display:inline-block; border-bottom: 1px dotted #000; width: calc(100% - 10px);">{{ $produkString }}</span>
                    <!-- Additional fake dotted lines to match the image spacing -->
                    <div style="border-bottom: 1px dotted #000; width: 100%; height: 24px;"></div>
                    <div style="border-bottom: 1px dotted #000; width: 100%; height: 24px;"></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="amount-box">
                <span>RP. {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Tanda tangan Penerima</p>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p>Tanda tangan Penyetor</p>
                </div>
            </div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; margin-right: 10px;">Print</button>
        <button onclick="window.close()" style="padding: 8px 16px;">Close</button>
    </div>
</body>
</html>
