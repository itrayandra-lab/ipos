<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            padding: 40px;
            color: #333;
            background: #fff;
        }

        .header-container {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
        }

        .header-left img {
            max-width: 180px;
            height: auto;
        }

        .header-right {
            display: table-cell;
            width: 60%;
            text-align: right;
            vertical-align: middle;
        }

        .header-right h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
            letter-spacing: 1px;
        }

        .header-info {
            text-align: right;
            margin-top: 10px;
        }

        .header-info table {
            display: inline-block;
            text-align: left;
            border-spacing: 0;
        }

        .header-info td {
            padding: 4px 0;
            font-size: 11px;
        }

        .header-info td:first-child {
            font-weight: 600;
            padding-right: 20px;
            color: #555;
            min-width: 80px;
        }

        .header-info td:last-child {
            color: #2c3e50;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .info-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
        }

        .info-box:first-child {
            margin-right: 4%;
        }

        .info-box h3 {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box p {
            margin: 4px 0;
            line-height: 1.7;
            font-size: 10px;
            color: #555;
        }

        .info-box strong {
            color: #2c3e50;
            font-size: 11px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .items-table thead {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }

        .items-table th {
            padding: 14px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table tbody tr {
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .items-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .items-table td {
            padding: 12px 10px;
            font-size: 10px;
        }

        .items-table td strong {
            font-size: 11px;
            color: #2c3e50;
        }

        .items-table td small {
            font-size: 9px;
            color: #777;
            font-style: italic;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        .summary-section {
            width: 100%;
            margin-top: 25px;
        }

        .summary-table {
            float: right;
            width: 380px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .summary-table table {
            width: 100%;
            border-spacing: 0;
        }

        .summary-table td {
            padding: 10px 0;
            font-size: 11px;
        }

        .summary-table td:first-child {
            text-align: left;
            font-weight: 600;
            color: #555;
        }

        .summary-table td:last-child {
            text-align: right;
            font-weight: 700;
            color: #2c3e50;
        }

        .summary-table .total-row {
            border-top: 2px solid #2c3e50;
            font-size: 13px;
        }

        .summary-table .total-row td {
            padding-top: 15px;
            color: #2c3e50;
        }

        .notes-section {
            clear: both;
            margin-top: 40px;
            padding: 20px;
            background: #fffbea;
            border-left: 4px solid #f39c12;
            border-radius: 3px;
        }

        .notes-section h4 {
            font-size: 11px;
            margin-bottom: 10px;
            color: #2c3e50;
            text-transform: uppercase;
            font-weight: 700;
        }

        .notes-section p {
            color: #555;
            line-height: 1.7;
            font-size: 10px;
        }

        @media print {
            body {
                padding: 20px;
            }
            .no-print {
                display: none;
            }
            .items-table tbody tr:hover {
                background-color: inherit;
            }
        }

        .no-print {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px dashed #ddd;
        }

        .no-print button {
            padding: 12px 35px;
            margin: 0 8px;
            font-size: 13px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-print {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.4);
        }

        .btn-close {
            background: #95a5a6;
            color: white;
            box-shadow: 0 2px 5px rgba(149, 165, 166, 0.3);
        }

        .btn-close:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
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
        <div class="header-right">
            <h1>Purchase Order</h1>
            <div class="header-info">
                <table>
                    <tr>
                        <td>Nomer PO</td>
                        <td>{{ $po->po_number }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>{{ \Carbon\Carbon::parse($po->po_date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><strong>{{ strtoupper($po->status) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>Info Perusahaan</h3>
            @if($storeSetting)
                <p><strong>{{ $storeSetting->store_name }}</strong></p>
                <p>{{ $storeSetting->address }}</p>
                @if($storeSetting->phone)
                    <p>Telp: {{ $storeSetting->phone }}</p>
                @endif
                @if($storeSetting->email)
                    <p>Email: {{ $storeSetting->email }}</p>
                @endif
            @else
                <p><strong>Beautylatory Store</strong></p>
            @endif
        </div>

        <div class="info-box">
            <h3>Order Ke</h3>
            @if($po->supplier)
                <p><strong>{{ $po->supplier->name }}</strong></p>
                @if($po->supplier->address)
                    <p>{{ $po->supplier->address }}</p>
                @endif
                @if($po->supplier->phone)
                    <p>Telp: {{ $po->supplier->phone }}</p>
                @endif
                @if($po->supplier->email)
                    <p>Email: {{ $po->supplier->email }}</p>
                @endif
            @else
                <p>-</p>
            @endif
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Produk</th>
                <th style="width: 15%;" class="text-center">Kuantitas</th>
                <th style="width: 17.5%;" class="text-right">Harga</th>
                <th style="width: 17.5%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $item)
            <tr>
                <td>
                    <strong>
                        @if($item->product && $item->product->merek)
                            {{ $item->product->merek->name }} {{ $item->product_name }}
                        @else
                            {{ $item->product_name }}
                        @endif
                    </strong>
                    @if($item->description)
                        <br><small>{{ $item->description }}</small>
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->quantity, 0) }} {{ $item->satuan }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-table">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td>Rp {{ number_format($po->subtotal, 2, ',', '.') }}</td>
                </tr>
                @if($po->discount_amount > 0)
                <tr>
                    <td>Diskon</td>
                    <td>Rp {{ number_format($po->discount_amount, 2, ',', '.') }}</td>
                </tr>
                @endif
                @if($po->tax_amount > 0)
                <tr>
                    <td>Pajak</td>
                    <td>Rp {{ number_format($po->tax_amount, 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total</td>
                    <td>Rp {{ number_format($po->total, 2, ',', '.') }}</td>
                </tr>
            </table>
            <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #2c3e50;">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #555;">Jumlah Tertagih:</td>
                        <td style="text-align: right; font-size: 18px; font-weight: 700; color: #2c3e50;">Rp {{ number_format($po->total, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    @if($po->notes)
    <div class="notes-section">
        <h4>Catatan:</h4>
        <p>{{ $po->notes }}</p>
    </div>
    @endif

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>
</body>
</html>
