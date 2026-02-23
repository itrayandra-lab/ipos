<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Lab #{{ $transaction->id }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px; color: #333; line-height: 1.6; margin: 0; padding: 40px; }
        .invoice-box { 
            max-width: 800px; 
            margin: auto; 
            padding: 30px; 
            border: 1px solid #eee; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); 
            background: #fff;
        }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #33b5e5; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #33b5e5; font-size: 28px; }
        .company-info p { margin: 2px 0; }
        .client-info { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .info-col { width: 45%; }
        .info-col h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px; font-size: 16px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #eee; }
        .table td { padding: 12px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .total-section { float: right; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 0; }
        .total-row.grand-total { border-top: 2px solid #33b5e5; font-weight: bold; font-size: 18px; margin-top: 10px; }
        .footer { margin-top: 100px; border-top: 1px solid #eee; padding-top: 10px; font-size: 11px; color: #777; clear: both; }
        .signatures { margin-top: 60px; display: flex; justify-content: space-between; text-align: center; }
        .sig-box { width: 200px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <div class="header">
            <div>
                <h1>INVOICE</h1>
                <p><strong>KELAS FORMULASI LAB</strong></p>
            </div>
            <div class="company-info text-right">
                <p><strong>IPOS SYSTEM LAB</strong></p>
                <p>Jl. Contoh No. 123, Jakarta</p>
                <p>Telp: (021) 12345678</p>
            </div>
        </div>

        <div class="client-info">
            <div class="info-col">
                <h3>DITAGIHKAN KEPADA</h3>
                <p><strong>{{ $transaction->customer_name ?? ($transaction->customer ? $transaction->customer->name : 'Umum') }}</strong></p>
                <p>{{ $transaction->customer_phone ?? ($transaction->customer ? $transaction->customer->phone : '-') }}</p>
            </div>
            <div class="info-col text-right">
                <p><strong>No. Invoice:</strong> #LAB-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->created_at)->format('d F Y') }}</p>
                <p><strong>Status:</strong> <span style="color: green;">LUNAS</span></p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Deskripsi Layanan / Kelas</th>
                    <th class="text-right">Biaya</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ str_replace('Kelas Formulasi Lab: ', '', $transaction->delivery_desc) }}</strong><br>
                        <small>{{ $transaction->notes }}</small>
                    </td>
                    <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div style="margin-top: 40px; clear: both;">
            <p><em>Terbilang: # {{ \App\Helpers\Terbilang::make($transaction->total_amount) }} Rupiah #</em></p>
        </div>

        <div class="signatures">
            <div class="sig-box">
                <p>Penerima,</p>
                <div style="height: 80px;"></div>
                <p>( ........................ )</p>
            </div>
            <div class="sig-box">
                <p>Hormat Kami,</p>
                <div style="height: 80px;"></div>
                <p>( {{ $transaction->user->name }} )</p>
            </div>
        </div>

        <div class="footer">
            <p>Terima kasih atas partisipasi Anda dalam kelas formulasi kami. Harap simpan invoice ini sebagai bukti keikutsertaan yang sah.</p>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
