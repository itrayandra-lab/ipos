<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi #{{ $transaction->id }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 13px; margin: 0; padding: 40px; }
        .receipt-container { 
            border: 2px solid #333; 
            padding: 20px; 
            position: relative;
            background-color: #fff;
        }
        .header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; overflow: hidden; }
        .header-left { float: left; width: 60%; }
        .header-right { float: right; width: 35%; text-align: right; }
        .header h1 { margin: 0; font-size: 24px; color: #333; }
        .content { margin-bottom: 30px; }
        .row { display: flex; margin-bottom: 12px; border-bottom: 1px dotted #999; padding-bottom: 5px; }
        .label { width: 180px; font-weight: bold; }
        .value { flex: 1; }
        .amount-box { 
            background: #f0f0f0; 
            border: 1px solid #333; 
            padding: 10px 20px; 
            font-size: 18px; 
            font-weight: bold; 
            display: inline-block;
            margin-top: 20px;
        }
        .footer { margin-top: 40px; overflow: hidden; }
        .signatures { width: 100%; }
        .signatures td { text-align: center; width: 33%; }
        .watermark { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%) rotate(-30deg); 
            font-size: 80px; 
            color: rgba(0,0,0,0.05); 
            z-index: -1; 
            pointer-events: none;
            white-space: nowrap;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .receipt-container { border: 2px solid #000; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt-container">
        <div class="watermark">KUITANSI RESMI</div>
        
        <div class="header">
            <div class="header-left">
                <h1>KUITANSI</h1>
                <p><strong>IPOS SYSTEM - TOKO RETAIL</strong></p>
            </div>
            <div class="header-right">
                <p>No. {{ str_pad($transaction->id, 8, '0', STR_PAD_LEFT) }}</p>
                <p>Tgl: {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="content">
            <div class="row">
                <div class="label">Sudah Terima Dari</div>
                <div class="value">: {{ $transaction->customer_name ?? ($transaction->customer ? $transaction->customer->name : 'Umum') }}</div>
            </div>
            <div class="row">
                <div class="label">Banyaknya Uang</div>
                <div class="value">: <em># {{ \App\Helpers\Terbilang::make($transaction->total_amount) }} Rupiah #</em></div>
            </div>
            <div class="row" style="border: none;">
                <div class="label">Untuk Pembayaran</div>
                <div class="value">: Pembayaran Transaksi Penjualan #{{ $transaction->id }} @if($transaction->delivery_type) via {{ strtoupper($transaction->delivery_type) }} @endif</div>
            </div>
        </div>

        <div class="amount-box">
            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }},-
        </div>

        <div class="footer">
            <table class="signatures">
                <tr>
                    <td style="width: 60%;"></td>
                    <td>
                        <p>{{ date('d F Y') }}</p>
                        <p>Kasir,</p>
                        <div style="height: 60px;"></div>
                        <p><strong>( {{ $transaction->user->name }} )</strong></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
