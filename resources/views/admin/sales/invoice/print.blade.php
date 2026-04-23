<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $transaction->invoice_number ?? '#'.$transaction->id }}</title>
    <style>
        @page { size: A4; margin: 0; }
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 12px; 
            color: #333; 
            line-height: 1.5; 
            margin: 0; 
            padding: 30px; 
        }
        .invoice-container { max-width: 800px; margin: auto; }
        
        /* Header Styles */
        .header { 
            display: flex; 
            align-items: flex-start;
            margin-bottom: 40px; 
            border-bottom: 3px solid #2c3e50; 
            padding-bottom: 20px; 
        }
        .company-section { 
            flex: 0 0 40%; 
            text-align: left;
        }
        .header-center { 
            flex: 0 0 25%; 
        }
        .invoice-section { 
            flex: 0 0 35%; 
            text-align: right; 
        }
        .company-logo { 
            width: 100px; 
            height: 100px; 
            margin-bottom: 15px;
        }
        .company-logo img { 
            width: 100%; 
            height: 100%; 
            object-fit: contain; 
        }
        .company-contact h2 { 
            margin: 0 0 10px 0; 
            font-size: 16px; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        .company-contact p { 
            margin: 3px 0; 
            font-size: 11px; 
            color: #555; 
        }
        
        
        .invoice-section { text-align: right; }
        .invoice-section h1 { 
            margin: 0 0 15px 0; 
            font-size: 36px; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        .invoice-details p { 
            margin: 5px 0; 
            font-size: 12px; 
            font-weight: 600; 
        }
        
        /* Information Section */
        .info-section { 
            display: flex; 
            margin-bottom: 30px; 
        }
        .bill-to { 
            flex: 0 0 40%; 
            text-align: left;
        }
        .info-center { 
            flex: 0 0 25%; 
        }
        .payment-info { 
            flex: 0 0 35%; 
            text-align: right;
        }
        .section-title { 
            font-size: 14px; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 10px; 
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        .info-content p { 
            margin: 5px 0; 
            font-size: 12px; 
        }
        .payment-amount { 
            font-size: 18px; 
            font-weight: bold; 
            color: #27ae60; 
            margin-top: 10px;
        }
        
        /* Items Table */
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
            border: 2px solid #2c3e50;
        }
        .items-table th { 
            background: #34495e; 
            color: white; 
            padding: 12px 8px; 
            text-align: left; 
            font-size: 11px; 
            font-weight: bold;
        }
        .items-table td { 
            padding: 10px 8px; 
            border-bottom: 1px solid #bdc3c7; 
            font-size: 11px;
        }
        .items-table tr:nth-child(even) { 
            background: #f8f9fa; 
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Bottom Section */
        .bottom-section { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 40px; 
        }
        .payment-method, .summary { width: 48%; }
        .payment-method h3, .summary h3 { 
            font-size: 14px; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 5px;
        }
        .bank-info { 
            background: #ecf0f1; 
            padding: 15px; 
            border-radius: 8px; 
            border-left: 4px solid #2c3e50;
        }
        .bank-info table { width: 100%; }
        .bank-info td { 
            padding: 5px 0; 
            font-size: 11px; 
        }
        .bank-info td:first-child { 
            color: #7f8c8d; 
            width: 100px; 
        }
        .bank-info td:last-child { 
            font-weight: 600; 
        }
        
        .summary-table { width: 100%; }
        .summary-table td { 
            padding: 8px 0; 
            font-size: 12px; 
        }
        .summary-table .total-row td { 
            border-top: 2px solid #2c3e50; 
            padding-top: 15px; 
            font-size: 16px; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        .summary-table .paid-row td { 
            color: #27ae60; 
            font-weight: 600; 
        }
        
        /* Signatures */
        .signatures { 
            width: 100%; 
            margin-top: 60px; 
        }
        .signatures td { 
            width: 50%; 
            vertical-align: top;
        }
        .signatures .left-signature {
            text-align: left;
        }
        .signatures .right-signature {
            text-align: center;
        }
        .sig-title { 
            font-size: 12px; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 60px;
        }
        .sig-name { 
            font-size: 12px; 
            font-weight: bold; 
            border-top: 1px solid #333; 
            display: inline-block; 
            min-width: 150px; 
            padding-top: 5px; 
        }
        
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="header">
            <div class="company-section">
                <div class="company-logo">
                    @if($setting && $setting->logo_path)
                        <img src="{{ asset($setting->logo_path) }}" alt="Logo" onerror="this.src='{{ asset('assets/img/logo.png') }}';">
                    @elseif(file_exists(public_path('assets/img/logo.png')))
                        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo">
                    @else
                        <div style="width: 100%; height: 100%; background: #ecf0f1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #bdc3c7; font-weight: bold;">LOGO</div>
                    @endif
                </div>
                <div class="company-contact">
                    <h2>KONTAK</h2>
                    <p><strong>{{ $setting->address ?? 'Alamat Perusahaan' }}</strong></p>
                    <p>{{ $setting->email ?? 'email@perusahaan.com' }}</p>
                    <p>{{ $setting->phone ?? '021-1234567' }}</p>
                </div>
            </div>
            
            <div class="header-center">
                <!-- Space for future content if needed -->
            </div>
            
            <div class="invoice-section">
                <h1>INVOICE</h1>
                <div class="invoice-details">
                    <p><strong>No. Invoice:</strong> {{ $transaction->invoice_number ?? 'INV-'.$transaction->id }}</p>
                    <p><strong>Tanggal Invoice:</strong> {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</p>
                    @if($transaction->due_date)
                    <p><strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($transaction->due_date)->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Information Section -->
        <div class="info-section">
            <div class="bill-to">
                <div class="section-title">KEPADA</div>
                <div class="info-content">
                    <p><strong>{{ $transaction->customer_name ?? ($transaction->customer->name ?? 'Pelanggan Umum') }}</strong></p>
                    @if($transaction->customer_phone || ($transaction->customer->phone ?? false))
                    <p>{{ $transaction->customer_phone ?? $transaction->customer->phone }}</p>
                    @endif
                    @if($transaction->customer_address)
                    <p>{{ $transaction->customer_address }}</p>
                    @endif
                </div>
            </div>
            
            <div class="info-center">
                <!-- Space for future content if needed -->
            </div>
            
            <div class="payment-info">
                @php
                    $totalPaid = $transaction->payments->sum('amount');
                    $displayAmount = $totalPaid > 0 ? $totalPaid : ($transaction->down_payment ?? 0);
                @endphp
                <h3>TOTAL DIBAYAR</h3>
                <div class="payment-amount">Rp {{ number_format($displayAmount, 0, ',', '.') }}</div>
                
                <!-- Lunas Badge -->
                @if($transaction->payment_status === 'paid')
                <div style="margin-top: 15px;">
                    <img src="{{ asset('assets/img/lunas.png') }}" alt="Lunas" style="width: 100px; height: auto;" onerror="this.style.display='none';">
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>DESKRIPSI PRODUK</th>
                    <th class="text-right" style="width: 100px;">HARGA</th>
                    <th class="text-center" style="width: 60px;">QTY</th>
                    <th class="text-right" style="width: 120px;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items->where('parent_item_id', null) as $i => $item)
                @php
                    $product = $item->product;
                    $variant = $item->batch ? $item->batch->variant : null;
                    $netto = $variant ? $variant->netto : null;
                    
                    $merekName = ($product && $product->merek) ? trim($product->merek->name) : '';
                    $productName = trim($product->name ?? '');
                    $nettoValue = $netto ? trim($netto->netto_value ?? '') : '';
                    $satuan = $netto ? trim($netto->satuan ?? '') : '';
                    
                    // Format: Merek + Produk + Netto + Satuan (tanpa batch)
                    $parts = array_filter([$merekName, $productName, $nettoValue, $satuan]);
                    $displayLabel = implode(' ', $parts);
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight: 600;">{{ $displayLabel }}</div>
                    </td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <div class="payment-method">
                <h3>METODE PEMBAYARAN</h3>
                @if($transaction->bankAccount)
                <div class="bank-info">
                    <table>
                        <tr>
                            <td>Bank:</td>
                            <td>{{ $transaction->bankAccount->bank_name }}</td>
                        </tr>
                        <tr>
                            <td>No. Rekening:</td>
                            <td>{{ $transaction->bankAccount->account_number }}</td>
                        </tr>
                        <tr>
                            <td>Atas Nama:</td>
                            <td>{{ $transaction->bankAccount->account_holder }}</td>
                        </tr>
                    </table>
                </div>
                @else
                <div class="bank-info">
                    <p style="text-align: center; color: #7f8c8d; font-style: italic;">{{ strtoupper($transaction->payment_method) }}</p>
                </div>
                @endif
            </div>
            
            <div class="summary">
                <table class="summary-table">
                    <tr>
                        <td class="text-right">Sub Total:</td>
                        <td class="text-right" style="width: 120px;">Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                    @if($transaction->tax_amount > 0)
                    <tr>
                        <td class="text-right">Pajak ({{ strtoupper($transaction->tax_type) }}):</td>
                        <td class="text-right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($transaction->discount > 0)
                    <tr>
                        <td class="text-right">Diskon:</td>
                        <td class="text-right" style="color: #e74c3c;">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td class="text-right">GRAND TOTAL:</td>
                        <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $totalPaid = $transaction->payments->sum('amount');
                    @endphp
                    @if($totalPaid > 0)
                    <tr class="paid-row">
                        <td class="text-right">Total Terbayar:</td>
                        <td class="text-right">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Signatures -->
        <table class="signatures">
            <tr>
                <td style="text-align: left; width: 100%;">
                    <div class="sig-title">Hormat kami</div>
                    <div class="sig-name">{{ $transaction->user->name ?? 'Admin' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="no-print" style="margin-top: 40px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
        <button onclick="window.print()" style="padding: 12px 24px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-right: 10px; font-size: 14px;">
            🖨️ Print Invoice
        </button>
        <button onclick="window.close()" style="padding: 12px 24px; background: #95a5a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">Tutup</button>
    </div>
</body>
</html>