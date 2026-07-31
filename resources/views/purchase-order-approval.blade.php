<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Purchase Order - {{ $po->po_number }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        body { background: #f5f7fa; padding: 40px 0; }
        .paper {
            max-width: 800px; margin: 0 auto; background: #fff;
            border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.08);
            padding: 40px 48px;
        }
        .header { text-align: center; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 2px solid #0d9488; }
        .header h1 { font-size: 24px; font-weight: 800; color: #0d9488; margin-bottom: 4px; }
        .header .po-number { font-size: 14px; color: #64748b; font-weight: 600; }
        .header .date { font-size: 12px; color: #94a3b8; }

        .info-grid { display: flex; gap: 24px; margin-bottom: 32px; }
        .info-box { flex: 1; }
        .info-box .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 4px; }
        .info-box .value { font-size: 14px; font-weight: 600; color: #1e293b; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        table.items th {
            background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .5px; padding: 10px 12px;
            border-top: none; border-bottom: 2px solid #e2e8f0; text-align: left;
        }
        table.items td {
            padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #1e293b;
        }
        table.items td:last-child, table.items th:last-child { text-align: right; }
        table.items td:nth-child(2), table.items th:nth-child(2) { text-align: center; width: 80px; }
        table.items td:nth-child(3), table.items th:nth-child(3) { text-align: right; width: 120px; }
        table.items td:nth-child(4), table.items th:nth-child(4) { text-align: right; width: 120px; }

        .summary { margin-left: auto; width: 300px; margin-bottom: 32px; }
        .summary .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
        .summary .row .label { color: #64748b; }
        .summary .row .value { font-weight: 600; color: #1e293b; }
        .summary .divider { border-top: 1px dashed #e2e8f0; margin: 8px 0; }
        .summary .grand .value { font-size: 20px; font-weight: 800; color: #0d9488; }

        .approver-info { text-align: center; padding: 20px; background: #f8fafc; border-radius: 12px; margin-bottom: 24px; }
        .approver-info .name { font-weight: 700; font-size: 16px; color: #1e293b; }
        .approver-info .hint { font-size: 12px; color: #94a3b8; margin-top: 4px; }

        .actions { display: flex; gap: 12px; justify-content: center; }
        .actions .btn { border-radius: 10px; font-weight: 700; font-size: 14px; padding: 12px 32px; min-width: 160px; border: none; cursor: pointer; }
        .btn-approve { background: #16a34a; color: #fff; }
        .btn-approve:hover { background: #15803d; }
        .btn-reject { background: #ef4444; color: #fff; }
        .btn-reject:hover { background: #dc2626; }

        .status-badge { display: inline-block; padding: 6px 18px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .status-approved { background: #dcfce7; color: #16a34a; }
        .status-rejected { background: #fee2e2; color: #dc2626; }
        .status-pending { background: #fef3c7; color: #d97706; }

        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #dcfce7; color: #16a34a; }
        .alert-error { background: #fee2e2; color: #dc2626; }

        .reject-reason { margin-top: 12px; padding: 12px; background: #fef2f2; border-radius: 8px; border: 1px solid #fecaca; font-size: 13px; color: #991b1b; text-align: center; }

        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,.4); z-index: 999; align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box { background: #fff; border-radius: 16px; padding: 24px; width: 400px; max-width: 90%; }
        .modal-box h6 { font-size: 16px; font-weight: 700; margin-bottom: 12px; }
        .modal-box textarea { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; font-size: 13px; resize: vertical; min-height: 80px; }
        .modal-box .modal-actions { display: flex; gap: 8px; margin-top: 12px; justify-content: flex-end; }

        .notes { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px; font-size: 13px; color: #92400e; margin-bottom: 24px; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .paper { box-shadow: none; padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="paper">
        <div class="header">
            @if($storeSetting && $storeSetting->nama_toko)
                <h1>{{ $storeSetting->nama_toko }}</h1>
            @else
                <h1>PURCHASE ORDER</h1>
            @endif
            <div class="po-number">{{ $po->po_number }}</div>
            <div class="date">{{ $po->po_date->format('d M Y') }}</div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="info-grid">
            <div class="info-box">
                <div class="label">Supplier</div>
                <div class="value">{{ $po->supplier->name }}</div>
            </div>
            <div class="info-box">
                <div class="label">Tanggal</div>
                <div class="value">{{ $po->po_date->format('d M Y') }}</div>
            </div>
            <div class="info-box">
                <div class="label">Estimasi Terima</div>
                <div class="value">{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d M Y') : '-' }}</div>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($po->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ number_format($item->quantity, 0) }} {{ $item->satuan ?? 'pcs' }}</td>
                    <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="row">
                <span class="label">Subtotal</span>
                <span class="value">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($po->discount_amount > 0)
            <div class="row">
                <span class="label">Diskon {{ $po->discount_type == 'percentage' ? '('.$po->discount_value.'%)' : '' }}</span>
                <span class="value" style="color:#ef4444;">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="row">
                <span class="label">Pajak (PPN {{ $po->tax_percentage }}%)</span>
                <span class="value">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</span>
            </div>
            <div class="divider"></div>
            <div class="row grand">
                <span class="label" style="font-weight:700;">Grand Total</span>
                <span class="value">Rp {{ number_format($po->total, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($po->notes)
        <div class="notes">
            <strong>Catatan:</strong><br>{{ $po->notes }}
        </div>
        @endif

        <div class="approver-info">
            <div class="d-flex align-items-center" style="gap:14px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user" style="font-size:24px;color:#94a3b8;"></i>
                </div>
                <div style="text-align:left;">
                    <div class="name">{{ $approval->user->name }}</div>
                    <div style="font-size:12px;color:#94a3b8;text-transform:capitalize;">{{ str_replace('_', ' ', $approval->user->role) }}</div>
                </div>
            </div>
            <div class="hint" style="margin-top:12px;">Diminta untuk memberikan persetujuan Purchase Order ini</div>
            <div class="mt-3">
                @if($approval->status === 'approved')
                    <div style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:12px;background:#f0fdf4;border:2px solid #16a34a;">
                        <i class="fas fa-check-circle" style="font-size:24px;color:#16a34a;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700;font-size:16px;color:#16a34a;">Disetujui</div>
                            <div style="font-size:12px;color:#64748b;">{{ $approval->approved_at ? $approval->approved_at->format('d M Y H:i') : '' }}</div>
                        </div>
                    </div>
                @elseif($approval->status === 'rejected')
                    <div style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:12px;background:#fef2f2;border:2px solid #dc2626;">
                        <i class="fas fa-times-circle" style="font-size:24px;color:#dc2626;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700;font-size:16px;color:#dc2626;">Tidak Disetujui</div>
                            <div style="font-size:12px;color:#64748b;">{{ $approval->approved_at ? $approval->approved_at->format('d M Y H:i') : '' }}</div>
                        </div>
                    </div>
                    @if($approval->rejected_reason)
                    <div class="reject-reason"><i class="fas fa-comment mr-1"></i> <strong>Alasan:</strong> {{ $approval->rejected_reason }}</div>
                    @endif
                @else
                    <div style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:12px;background:#fffbeb;border:2px solid #d97706;">
                        <i class="fas fa-clock" style="font-size:24px;color:#d97706;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700;font-size:16px;color:#d97706;">Menunggu Persetujuan</div>
                            <div style="font-size:12px;color:#64748b;">Belum ada keputusan</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($approval->status === 'pending')
        <div class="actions no-print">
            <form action="{{ route('purchase-order.approval.approve', $approval->token) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-approve"><i class="fas fa-check-circle mr-1"></i> Setujui</button>
            </form>
            <button type="button" class="btn btn-reject" onclick="showRejectModal()"><i class="fas fa-times-circle mr-1"></i> Tolak</button>
        </div>
        @endif

        <div style="text-align:center;margin-top:28px;padding-top:20px;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;" class="no-print">
            <i class="fas fa-shield-alt mr-1"></i> Dokumen ini dibuat secara elektronik dan tidak memerlukan tanda tangan basah.
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal-overlay" id="rejectModal">
        <div class="modal-box">
            <div style="text-align:center;margin-bottom:16px;">
                <div style="width:48px;height:48px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                    <i class="fas fa-times-circle" style="font-size:24px;color:#dc2626;"></i>
                </div>
                <h6 style="font-size:16px;font-weight:700;margin:0;color:#1e293b;">Alasan Penolakan</h6>
                <p style="font-size:12px;color:#64748b;margin:4px 0 0;">Jelaskan mengapa Purchase Order ini tidak disetujui</p>
            </div>
            <form action="{{ route('purchase-order.approval.reject', $approval->token) }}" method="POST">
                @csrf
                <textarea name="rejected_reason" placeholder="Tulis alasan penolakan di sini..." required style="width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:12px;font-size:13px;resize:vertical;min-height:100px;font-family:inherit;"></textarea>
                <div class="modal-actions" style="display:flex;gap:8px;margin-top:16px;">
                    <button type="button" style="flex:1;border-radius:10px;font-weight:600;font-size:13px;padding:10px;border:1px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;" onclick="hideRejectModal()">Batal</button>
                    <button type="submit" style="flex:1;border-radius:10px;font-weight:700;font-size:13px;padding:10px;border:none;background:#dc2626;color:#fff;cursor:pointer;">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        function showRejectModal() { document.getElementById('rejectModal').classList.add('active'); }
        function hideRejectModal() { document.getElementById('rejectModal').classList.remove('active'); }
    </script>
</body>
</html>
