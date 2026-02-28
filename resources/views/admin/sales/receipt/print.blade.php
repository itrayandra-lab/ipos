<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran #{{ $transaction->id }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 14px; margin: 0; padding: 20px; }
        .receipt-container { 
            border: 2px solid #555; 
            padding: 20px; 
            background-color: #fff;
            max-width: 800px;
            margin: 0 auto;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: bold; text-decoration: underline; letter-spacing: 1px;}
        .top-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .top-info p { margin: 0; font-weight: bold;}
        
        .content { margin-bottom: 30px; }
        .row { display: flex; align-items: flex-start; margin-bottom: 15px; }
        .label { width: 150px; }
        .value { flex: 1; border-bottom: 1px dotted #000; padding-bottom: 2px; }
        .value-multi-line { flex: 1; border-bottom: 1px dotted #000; line-height: 24px; min-height: 48px; 
            background-image: linear-gradient(to right, black 33%, rgba(255,255,255,0) 0%);
            background-position: bottom;
            background-size: 3px 1px;
            background-repeat: repeat-x;
            border-bottom: none;
        }
        .value-multi-line span { 
            display: inline;
            line-height: 24px;
            background: linear-gradient(transparent 23px, #000 23px, #000 24px, transparent 24px);
            background-size: 100% 24px;
        }
        
        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px; }
        .amount-box { 
            border: 2px solid #000; 
            padding: 10px 15px; 
            font-size: 18px; 
            font-weight: bold; 
            min-width: 200px;
            transform: skew(-15deg);
            background: #fff;
        }
        .amount-box span {
            display: block;
            transform: skew(15deg);
        }
        
        .signatures { display: flex; gap: 50px; }
        .signature-box { text-align: center; width: 150px; }
        .signature-line { border-bottom: 1px solid #000; margin-bottom: 5px; height: 60px; }
        .signature-box p { margin: 0; font-size: 12px; }
        
        .logo-placeholder { position: absolute; top: 20px; left: 20px; max-width: 100px; }
        .logo-placeholder img { max-width: 100%; height: auto;}

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<!-- Removed onload="window.print()" to allow user to inspect first if needed -->
<body>
    <div class="receipt-container" style="position: relative;">
        @if(file_exists(public_path('assets/img/logo-black.png')))
        <div class="logo-placeholder">
            <img src="{{ asset('assets/img/logo-black.png') }}" alt="Logo">
        </div>
        @endif

        <div class="header">
            <h1>KWITANSI PEMBAYARAN</h1>
        </div>
        
        <div class="top-info">
            <p>No: {{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p>Tanggal: {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</p>
        </div>

        <div class="content">
            <div class="row">
                <div class="label">Terima Dari</div>
                <div class="value">: {{ $transaction->customer_name ?? ($transaction->customer ? $transaction->customer->name : 'Umum') }}</div>
            </div>
            <div class="row">
                <div class="label">Terbilang</div>
                <div class="value">: # {{ \App\Helpers\Terbilang::make($transaction->total_amount) }} Rupiah #</div>
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
