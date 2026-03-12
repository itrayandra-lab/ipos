<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction->invoice_number ?? '#'.$transaction->id }}</title>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 40px; }
        .invoice-box { max-width: 800px; margin: auto; }
        
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #f4f4f4; padding-bottom: 20px; }
        .company-info h1 { margin: 0; font-size: 24px; color: #6777ef; letter-spacing: -1px; }
        .company-info p { margin: 2px 0; color: #777; font-size: 10px; }
        
        .invoice-title { text-align: right; }
        .invoice-title h2 { margin: 0; font-size: 20px; text-transform: uppercase; color: #333; }
        .invoice-title p { margin: 2px 0; font-weight: bold; font-size: 12px; }

        .details-container { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .bill-to, .invoice-info { width: 48%; }
        .label { font-size: 9px; text-transform: uppercase; font-weight: bold; color: #999; margin-bottom: 5px; display: block; }
        .value { font-size: 11px; font-weight: 600; margin-bottom: 10px; }

        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: #f9f9f9; border-bottom: 1px solid #eee; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #666; }
        .table td { border-bottom: 1px solid #f4f4f4; padding: 12px 10px; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .summary-container { display: flex; justify-content: flex-end; }
        .summary-table { width: 250px; }
        .summary-table td { padding: 5px 0; }
        .summary-table tr.total td { border-top: 1px solid #333; padding-top: 10px; font-weight: bold; font-size: 14px; color: #6777ef; }
        
        .terbilang { margin-top: 20px; font-style: italic; color: #666; font-size: 10px; }

        .signatures { width: 100%; margin-top: 60px; }
        .signatures td { text-align: center; width: 33.33%; }
        .sig-box { height: 70px; }
        .sig-name { font-weight: bold; border-top: 1px solid #333; display: inline-block; min-width: 150px; padding-top: 5px; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-paid { background: #e3f9eb; color: #28a745; }
        .badge-unpaid { background: #fdeaea; color: #dc3545; }
        .badge-credit { background: #e3f2fd; color: #007bff; }

        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <div class="header">
            <div class="company-logo" style="width: 80px; margin-right: 20px;">
                @if($setting && $setting->logo_path)
                    <img src="{{ asset($setting->logo_path) }}" alt="Logo" style="width: 100%; height: auto; object-fit: contain;" onerror="this.src='{{ asset('assets/img/logo.png') }}';">
                @elseif(file_exists(public_path('assets/img/logo.png')))
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" style="width: 100%; height: auto; object-fit: contain;">
                @else
                    <div style="width: 60px; height: 60px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #ccc; font-weight: bold;">LOGO</div>
                @endif
            </div>
            <div class="company-info" style="flex-grow: 1;">
                <h1>{{ $setting->store_name ?? 'IPOS SYSTEM' }}</h1>
                <p>Digital Inventory & Point of Sales</p>
                <p>{{ $setting->address ?? 'Jl. Contoh No. 123, Jakarta, Indonesia' }}</p>
            </div>
            @php
                $totalPaid = $transaction->payments->sum('amount');
                $isDPPaid = ($transaction->payment_status === 'credit' && $totalPaid > 0);
                $title = 'INVOICE';
                if ($isDPPaid || $transaction->payment_status === 'paid') {
                    $title = 'INVOICE PELUNASAN';
                }

                $statusText = $transaction->payment_status;
                if ($transaction->payment_status === 'credit') {
                    $statusText = $totalPaid > 0 ? 'DP Terbayar' : 'Menunggu DP';
                } elseif ($transaction->payment_status === 'paid') {
                    $statusText = 'Lunas';
                }
            @endphp
            <div class="invoice-title">
                <h2>{{ $title }}</h2>
                <p>{{ $transaction->invoice_number ?? 'INV-'.$transaction->id }}</p>
                <div class="badge badge-{{ $transaction->payment_status }}">
                    {{ strtoupper($statusText) }}
                </div>
            </div>
        </div>

        <div class="details-container">
            <div class="bill-to">
                <span class="label">Tagihan Kepada:</span>
                <div class="value">{{ $transaction->customer_name ?? ($transaction->customer->name ?? 'Pelanggan Umum') }}</div>
                @if($transaction->customer_phone || ($transaction->customer->phone ?? false))
                <div class="value" style="font-weight: normal; font-size: 10px;">Telp: {{ $transaction->customer_phone ?? $transaction->customer->phone }}</div>
                @endif
                @if($transaction->customer_address)
                <div class="value" style="font-weight: normal; font-size: 10px; margin-top: 5px;">Alamat: {{ $transaction->customer_address }}</div>
                @endif
            </div>
            <div class="invoice-info">
                <div style="display: flex;">
                    <div style="flex: 1;">
                        <span class="label">Tanggal:</span>
                        <div class="value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</div>
                    </div>
                    @if($transaction->due_date)
                    <div style="flex: 1;">
                        <span class="label">Jatuh Tempo:</span>
                        <div class="value text-danger">{{ \Carbon\Carbon::parse($transaction->due_date)->format('d/m/Y') }}</div>
                    </div>
                    @endif
                </div>
                <div style="display: flex;">
                    <div style="flex: 1;">
                        <span class="label">Metode Bayar:</span>
                        <div class="value">{{ strtoupper($transaction->payment_method) }}</div>
                    </div>
                    <div style="flex: 1;">
                        <span class="label">Kasir:</span>
                        <div class="value">{{ $transaction->user->name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($transaction->bankAccount)
        <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 3px solid #6777ef;">
            <span class="label" style="display: block; margin-bottom: 8px;">Informasi Rekening Transfer:</span>
            <div style="display: flex; gap: 30px;">
                <div>
                    <span style="font-size: 9px; color: #999; text-transform: uppercase;">Bank:</span>
                    <div style="font-weight: 600; font-size: 12px;">{{ $transaction->bankAccount->bank_name }}</div>
                </div>
                <div>
                    <span style="font-size: 9px; color: #999; text-transform: uppercase;">No. Rekening:</span>
                    <div style="font-weight: 600; font-size: 12px;">{{ $transaction->bankAccount->account_number }}</div>
                </div>
                <div>
                    <span style="font-size: 9px; color: #999; text-transform: uppercase;">Atas Nama:</span>
                    <div style="font-weight: 600; font-size: 12px;">{{ $transaction->bankAccount->account_holder }}</div>
                </div>
            </div>
        </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Deskripsi Produk</th>
                    <th class="text-right">Harga</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $i => $item)
                @php
                    $merek = trim($item->product->merek->name ?? '');
                    $name = trim($item->product->name ?? '');
                    $variant = trim($item->batch->variant->variant_name ?? '');
                    
                    // Deduplicate logic
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
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight: 600;">{{ $displayLabel }}</div>
                        <div style="font-size: 9px; color: #777;">Batch: {{ $item->batch->batch_no ?? '-' }}</div>
                    </td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-container">
            <div class="summary-table">
                <table style="width: 100%;">
                    <tr>
                        <td class="text-right text-muted">Subtotal:</td>
                        <td class="text-right font-weight-bold">Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                    @if($transaction->tax_amount > 0)
                    <tr>
                        <td class="text-right text-muted">Pajak ({{ strtoupper($transaction->tax_type) }}):</td>
                        <td class="text-right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($transaction->discount > 0)
                    <tr>
                        <td class="text-right text-muted">Diskon:</td>
                        <td class="text-right text-danger">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="total">
                        <td class="text-right">GRAND TOTAL:</td>
                        <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $totalPaid = $transaction->payments->sum('amount');
                        $balance = $transaction->total_amount - $totalPaid;
                    @endphp

                    @if($transaction->payment_status === 'credit' && $totalPaid == 0)
                    <tr>
                        <td class="text-right text-muted" style="font-size: 10px; font-weight: bold;">Uang Muka (DP) Harus Dibayar:</td>
                        <td class="text-right" style="font-size: 10px; font-weight: bold;">Rp {{ number_format($transaction->down_payment, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if($totalPaid > 0)
                    <tr>
                        <td class="text-right text-muted" style="font-size: 10px;">Total Terbayar:</td>
                        <td class="text-right" style="font-size: 10px;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    
                    @if($balance > 0)
                    <tr>
                        <td class="text-right" style="font-size: 11px; font-weight: bold; color: #fd7e14;">SISA TAGIHAN:</td>
                        <td class="text-right" style="font-size: 11px; font-weight: bold; color: #fd7e14;">Rp {{ number_format($balance, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="terbilang">
            Terbilang: <strong>{{ \App\Helpers\Terbilang::make($transaction->total_amount) }} Rupiah</strong>
        </div>

        @if($transaction->notes)
        <div style="margin-top: 20px; border-top: 1px dotted #ccc; padding-top: 10px;">
            <span class="label">Catatan:</span>
            <div style="font-size: 10px;">{{ $transaction->notes }}</div>
        </div>
        @endif

        <table class="signatures">
            <tr>
                <td>Hormat Kami,</td>
                <td>Gudang,</td>
                <td>Penerima,</td>
            </tr>
            <tr>
                <td class="sig-box"></td>
                <td class="sig-box"></td>
                <td class="sig-box"></td>
            </tr>
            <tr>
                <td><div class="sig-name">{{ $transaction->user->name ?? 'Admin' }}</div></td>
                <td><div class="sig-name">Staf Gudang</div></td>
                <td><div class="sig-name">Tanda Tangan & Stempel</div></td>
            </tr>
        </table>
    </div>

    <div class="no-print" style="margin-top: 40px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #6777ef; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-right: 10px;">
            <i class="fas fa-print"></i> Print Sekarang
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #eee; border: none; border-radius: 4px; cursor: pointer;">Tutup Halaman</button>
    </div>
</body>
</html>
