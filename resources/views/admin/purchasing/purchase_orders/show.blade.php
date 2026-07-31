@extends('master')
@section('title', 'Detail Purchase Order - ')

@push('styles')
<style>
    :root {
        --teal-600: #0d9488;
        --teal-700: #0f766e;
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-400: #94a3b8;
        --slate-600: #64748b;
        --slate-800: #1e293b;
        --green-600: #16a34a;
        --orange-500: #f97316;
        --red-500: #ef4444;
        --blue-500: #3b82f6;
    }
    body { background: var(--slate-50); }

    /* ===== ZONA 1 — HEADER SUMMARY ===== */
    .po-summary-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        padding: 28px 32px;
        margin-bottom: 28px;
    }
    .po-number-lg { font-size: 22px; font-weight: 800; color: var(--teal-600); letter-spacing: .5px; }
    .po-supplier { font-size: 15px; font-weight: 600; color: var(--slate-800); }
    .po-meta { font-size: 13px; color: var(--slate-400); }
    .badge-semantic {
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px;
        padding: 5px 14px; border-radius: 20px;
    }
    .badge-success { background: #dcfce7; color: #16a34a; }
    .badge-pending { background: #fef3c7; color: #d97706; }
    .badge-danger  { background: #fee2e2; color: #dc2626; }
    .badge-info    { background: #dbeafe; color: #2563eb; }
    .badge-neutral { background: #f1f5f9; color: #64748b; }

    .progress-sm { height: 8px; border-radius: 4px; background: var(--slate-100); }

    /* ===== ZONA 2 — ITEM TABLE ===== */
    .section-title {
        font-size: 16px; font-weight: 700; color: var(--slate-800);
        margin-bottom: 16px; padding-bottom: 8px;
        border-bottom: 2px solid var(--teal-600);
        display: inline-block;
    }
    .item-table thead th {
        background: var(--slate-50); color: var(--slate-600);
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        padding: 12px 14px !important; border-top: none; border-bottom: 2px solid var(--slate-200);
    }
    .item-table tbody td {
        padding: 16px 14px !important; vertical-align: middle;
        border-bottom: 1px solid var(--slate-100);
    }
    .progress-cell .progress { margin-bottom: 2px; }
    .progress-cell .progress-label { font-size: 11px; color: var(--slate-400); }

    /* ===== ZONA 3 — TIMELINE ===== */
    .timeline { position: relative; padding-left: 32px; }
    .timeline::before {
        content: ''; position: absolute; left: 11px; top: 0; bottom: 0;
        width: 2px; background: var(--slate-200);
    }
    .timeline-item { position: relative; margin-bottom: 24px; }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-dot {
        position: absolute; left: -32px; top: 4px;
        width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; color: #fff; border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }
    .dot-receipt { background: var(--green-600); }
    .dot-payment { background: var(--blue-500); }
    .timeline-date { font-size: 11px; color: var(--slate-400); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
    .timeline-label { font-size: 14px; font-weight: 700; color: var(--slate-800); }
    .timeline-ref { font-size: 12px; color: var(--teal-600); font-weight: 600; }
    .timeline-detail { font-size: 13px; color: var(--slate-600); margin-top: 4px; }

    .fin-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .fin-label { font-size: 13px; color: var(--slate-400); }
    .fin-value { font-size: 13px; font-weight: 600; color: var(--slate-800); }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header" style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:28px!important;border-left:5px solid var(--teal-600);padding:18px 24px!important;display:flex;justify-content:space-between;align-items:center;">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.purchasing.purchase_orders.index') }}" class="btn btn-icon mr-3"><i class="fas fa-arrow-left"></i></a>
                <h1 style="font-weight:800!important;color:var(--slate-800)!important;font-size:20px;margin-bottom:0;">Purchase Order Monitoring</h1>
            </div>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.purchase_orders.index') }}">PO</a></div>
                <div class="breadcrumb-item active">{{ $po->po_number }}</div>
            </div>
        </div>

        <div class="section-body">
            {{-- ============================================================
                 ZONA 1 — HEADER SUMMARY
                 ============================================================ --}}
            @php
                $statusLabels = [
                    'draft' => 'Draft',
                    'submitted' => 'Dikirim',
                    'approved' => 'Disetujui',
                    'received' => 'Selesai',
                    'cancelled' => 'Dibatalkan'
                ];
                $statusClasses = [
                    'draft' => 'badge-neutral',
                    'submitted' => 'badge-info',
                    'approved' => 'badge-success',
                    'received' => 'badge-success',
                    'cancelled' => 'badge-danger'
                ];
                $paymentLabels = [
                    'unpaid' => 'Belum Dibayar',
                    'partial' => 'Sebagian',
                    'paid' => 'Lunas'
                ];
                $paymentStatusClasses = [
                    'unpaid' => 'badge-pending',
                    'partial' => 'badge-info',
                    'paid' => 'badge-success'
                ];
                $allReceived = $po->items->every(fn($i) => $i->goodsReceiptItems->sum('quantity_received') >= $i->quantity);
                $anyReceived = $po->items->sum(fn($i) => $i->goodsReceiptItems->sum('quantity_received')) > 0;
                $receiptStatus = $allReceived ? 'received' : ($anyReceived ? 'partial' : 'pending');
                $receiptLabels = ['received' => 'Sudah Diterima', 'partial' => 'Sebagian', 'pending' => 'Belum Diterima'];
                $receiptClasses = ['received' => 'badge-success', 'partial' => 'badge-info', 'pending' => 'badge-neutral'];
                $totalQtyOrdered = $po->items->sum('quantity');
                $totalQtyReceived = $po->items->sum(fn($i) => $i->goodsReceiptItems->sum('quantity_received'));
                $totalQtyPaid = $po->items->sum('paid_qty');
            @endphp

            <div class="po-summary-card" style="margin-bottom:28px;padding:20px 24px;">
                <div class="row">
                    <div class="col-md-5">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--slate-400);margin-bottom:8px;">
                            <i class="fas fa-shopping-cart mr-1" style="color:var(--teal-600);"></i> Ringkasan Pembelian
                        </div>
                        <div class="po-number-lg" style="font-size:18px;">{{ $po->po_number }}</div>
                        <div class="po-supplier" style="font-size:14px;"><i class="fas fa-building mr-1" style="color:var(--slate-400);width:14px;"></i>{{ $po->supplier->name }}</div>
                        <div class="po-meta"><i class="fas fa-warehouse mr-1" style="width:14px;"></i>Gudang: {{ $po->warehouse->name ?? '-' }}</div>
                        <div class="mt-1 small text-muted">
                            {{ $po->po_date->format('d M Y') }} &mdash; Estimasi: {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d M Y') : '-' }}
                        </div>
                        <div class="d-flex mt-2" style="gap:6px;">
                            <span class="badge-semantic {{ $statusClasses[$po->status] ?? 'badge-neutral' }}" style="font-size:10px;padding:3px 10px;">{{ $statusLabels[$po->status] ?? $po->status }}</span>
                            <span class="badge-semantic {{ $receiptClasses[$receiptStatus] }}" style="font-size:10px;padding:3px 10px;">{{ $receiptLabels[$receiptStatus] }}</span>
                            @if($po->payment_status)
                            <span class="badge-semantic {{ $paymentStatusClasses[$po->payment_status] ?? 'badge-neutral' }}" style="font-size:10px;padding:3px 10px;">{{ $paymentLabels[$po->payment_status] ?? $po->payment_status }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--slate-400);margin-bottom:8px;">
                            <i class="fas fa-calculator mr-1" style="color:var(--teal-600);"></i> Ringkasan Keuangan
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="fin-row" style="margin-bottom:4px;">
                                    <span class="fin-label" style="font-size:12px;">Subtotal</span>
                                </div>
                                <div class="fin-row" style="margin-bottom:4px;">
                                    <span class="fin-label" style="font-size:12px;">Diskon {{ $po->discount_type == 'percentage' ? '('.$po->discount_value.'%)' : '' }}</span>
                                </div>
                                <div class="fin-row" style="margin-bottom:4px;">
                                    <span class="fin-label" style="font-size:12px;">Pajak (PPN {{ $po->tax_percentage }}%)</span>
                                </div>
                                <div style="border-top:1px dashed var(--slate-200);margin:4px 0;"></div>
                                <div class="fin-row" style="margin-bottom:4px;">
                                    <span class="fin-label" style="font-size:12px;font-weight:700;">Grand Total</span>
                                </div>
                                <div style="border-top:1px dashed var(--slate-200);margin:4px 0;"></div>
                                <div class="fin-row" style="margin-bottom:4px;">
                                    <span class="fin-label" style="font-size:12px;">Dibayar</span>
                                </div>
                                <div class="fin-row" style="margin-bottom:4px;">
                                    <span class="fin-label" style="font-size:12px;">Sisa Tagihan</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="fin-value" style="font-size:12px;margin-bottom:4px;">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</div>
                                <div class="fin-value" style="font-size:12px;color:var(--red-500);margin-bottom:4px;">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</div>
                                <div class="fin-value" style="font-size:12px;margin-bottom:4px;">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</div>
                                <div style="border-top:1px dashed var(--slate-200);margin:4px 0;"></div>
                                <div class="fin-value" style="font-size:16px;font-weight:800;color:var(--teal-600);margin-bottom:4px;">Rp {{ number_format($po->total, 0, ',', '.') }}</div>
                                <div style="border-top:1px dashed var(--slate-200);margin:4px 0;"></div>
                                <div class="fin-value" style="font-size:12px;color:var(--green-600);margin-bottom:4px;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                                <div class="fin-value" style="font-size:12px;color:var(--red-500);margin-bottom:4px;">Rp {{ number_format($outstanding, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span style="font-size:11px;color:var(--slate-400);font-weight:600;">Progress</span>
                                        <span style="font-size:11px;font-weight:700;">{{ $progressPct }}%</span>
                                    </div>
                                    <div class="progress progress-sm" style="height:6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width:{{ $progressPct }}%;" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between small mt-1">
                                        <span style="font-size:10px;color:var(--green-600);font-weight:600;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                                        <span style="font-size:10px;color:var(--slate-400);">Rp {{ number_format($outstanding, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="mt-2 d-flex" style="gap:4px;">
                                    @if(!auth()->user()->isFinance())
                                    <a href="{{ route('admin.purchasing.purchase_orders.edit', $po->id) }}" class="btn btn-outline-warning btn-sm" style="border-radius:6px;font-weight:700;font-size:11px;padding:4px 10px;flex:1;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.purchasing.purchase_orders.print', $po->id) }}" target="_blank" class="btn btn-outline-info btn-sm" style="border-radius:6px;font-weight:700;font-size:11px;padding:4px 10px;flex:1;">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @php
                                        $totalAp = $po->approvals->count();
                                        $approvedAp = $po->approvals->where('status', 'approved')->count();
                                        $isVerified = $totalAp > 0 && $approvedAp === $totalAp;
                                    @endphp
                                    @if(!$isVerified)
                                    <script>
                                        document.currentScript.parentElement.querySelector('a[href*="print"]').addEventListener('click', function(e) {
                                            e.preventDefault();
                                            iziToast.warning({title:'Tidak bisa',message:'PO harus terverifikasi semua persetujuan sebelum print',position:'topRight'});
                                        });
                                    </script>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 ZONA 2 — ITEM PURCHASE ORDER (FULL WIDTH)
                 ============================================================ --}}
                    <div class="card" style="border-radius:16px;border:none;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:28px;">
                        <div class="card-body p-0">
                            <div style="padding:20px 24px 0;">
                                <h5 class="section-title"><i class="fas fa-list mr-2" style="color:var(--teal-600);"></i>Item Purchase Order</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table item-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th class="text-center" style="width:70px;">Order</th>
                                            <th style="width:160px;">Penerimaan</th>
                                            <th style="width:160px;">Pembayaran</th>
                                            <th class="text-center" style="width:80px;">Outstanding</th>
                                            <th class="text-right" style="width:110px;">Harga</th>
                                            <th class="text-right" style="width:110px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($po->items as $item)
                                        @php
                                            $qtyOrdered = (float) $item->quantity;
                                            $qtyReceived = (float) $item->goodsReceiptItems->sum('quantity_received');
                                            $qtyPaid = (float) ($item->paid_qty ?? 0);
                                            $rcvPct = $qtyOrdered > 0 ? min(100, round(($qtyReceived / $qtyOrdered) * 100)) : 0;
                                            $payPct = $qtyOrdered > 0 ? min(100, round(($qtyPaid / $qtyOrdered) * 100)) : 0;
                                            $outstandingQty = max(0, $qtyOrdered - $qtyPaid);
                                            $displayName = str_replace(' - ', ' ', $item->product_name);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div style="font-weight:600;color:var(--slate-800);">{{ $displayName }}</div>
                                            </td>
                                            <td class="text-center font-weight-bold">{{ number_format($qtyOrdered, 0) }}</td>
                                            <td class="progress-cell">
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar {{ $rcvPct >= 100 ? 'bg-success' : 'bg-info' }}" role="progressbar" style="width:{{ $rcvPct }}%;" aria-valuenow="{{ $rcvPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div class="progress-label">{{ number_format($qtyReceived, 0) }}/{{ number_format($qtyOrdered, 0) }} ({{ $rcvPct }}%)</div>
                                            </td>
                                            <td class="progress-cell">
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar {{ $payPct >= 100 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width:{{ $payPct }}%;" aria-valuenow="{{ $payPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div class="progress-label">{{ number_format($qtyPaid, 0) }}/{{ number_format($qtyOrdered, 0) }} ({{ $payPct }}%)</div>
                                            </td>
                                            <td class="text-center font-weight-bold" style="color:{{ $outstandingQty > 0 ? 'var(--red-500)' : 'var(--green-600)' }};">{{ number_format($outstandingQty, 0) }}</td>
                                            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ====================================================
                         APPROVAL STATUS
                         ==================================================== --}}
                    @if($po->approvals->count() > 0)
                    <div class="card" style="border-radius:16px;border:none;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:28px;">
                        <div class="card-body" style="padding:24px;">
                            <h5 class="section-title"><i class="fas fa-user-check mr-2" style="color:var(--teal-600);"></i>Persetujuan</h5>
                            <div class="row">
                                @foreach($po->approvals as $app)
                                @php
                                    $appColors = match($app->status) {
                                        'approved' => ['border' => '#16a34a', 'bg' => '#f0fdf4', 'icon' => 'fa-check-circle', 'iconColor' => '#16a34a'],
                                        'rejected' => ['border' => '#dc2626', 'bg' => '#fef2f2', 'icon' => 'fa-times-circle', 'iconColor' => '#dc2626'],
                                        default => ['border' => '#d97706', 'bg' => '#fffbeb', 'icon' => 'fa-clock', 'iconColor' => '#d97706']
                                    };
                                    $appLabel = match($app->status) {
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        default => 'Menunggu'
                                    };
                                @endphp
                                <div class="col-md-4 mb-3">
                                    <div style="background:#fff;border-radius:12px;padding:16px;border-left:4px solid {{ $appColors['border'] }};box-shadow:0 1px 4px rgba(0,0,0,.06);">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div style="font-weight:700;color:var(--slate-800);font-size:14px;">{{ $app->user->name }}</div>
                                                <div style="font-size:11px;color:var(--slate-400);text-transform:capitalize;">{{ str_replace('_', ' ', $app->user->role) }}</div>
                                            </div>
                                            <div style="width:40px;height:40px;border-radius:50%;background:{{ $appColors['bg'] }};display:flex;align-items:center;justify-content:center;">
                                                <i class="fas {{ $appColors['icon'] }}" style="font-size:18px;color:{{ $appColors['iconColor'] }};"></i>
                                            </div>
                                        </div>
                                        <div class="mt-3 d-flex align-items-center" style="gap:8px;">
                                            <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;background:{{ $appColors['bg'] }};color:{{ $appColors['border'] }};">
                                                <i class="fas {{ $appColors['icon'] }} mr-1"></i> {{ $appLabel }}
                                            </span>
                                            @if($app->approved_at)
                                            <span style="font-size:11px;color:var(--slate-400);">
                                                {{ $app->approved_at->format('d/m/Y H:i') }}
                                            </span>
                                            @endif
                                        </div>
                                        @if($app->status === 'rejected' && $app->rejected_reason)
                                        <div style="font-size:12px;color:#991b1b;margin-top:10px;padding:10px 12px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca;">
                                            <i class="fas fa-comment mr-1"></i><strong>Alasan:</strong> {{ $app->rejected_reason }}
                                        </div>
                                        @endif
                                        @if($app->status === 'pending')
                                        <div class="mt-3">
                                            @php $link = route('purchase-order.approval.show', $app->token); @endphp
                                            <div class="input-group input-group-sm" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                                                <input type="text" class="form-control" value="{{ $link }}" readonly style="border:none;background:#f8fafc;font-size:11px;padding:6px 10px;border-radius:8px 0 0 8px;cursor:text;">
                                                <div class="input-group-append">
                                                    <button class="btn btn-sm" style="background:var(--teal-600);color:#fff;border:none;border-radius:0 8px 8px 0;padding:6px 12px;font-size:12px;font-weight:600;" onclick="copyLink(this,'{{ $link }}')">
                                                        <i class="fas fa-copy mr-1"></i> Salin
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ====================================================
                         ZONA 3 — ACTIVITY TIMELINE
                         ==================================================== --}}
                    <div class="card" style="border-radius:16px;border:none;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:28px;">
                        <div class="card-body" style="padding:24px;">
                            <h5 class="section-title"><i class="fas fa-clock mr-2" style="color:var(--teal-600);"></i>Aktivitas PO</h5>
                            @if($timeline->count() > 0)
                            <div class="timeline">
                                @foreach($timeline as $event)
                                <div class="timeline-item">
                                    <div class="timeline-dot {{ $event['type'] === 'receipt' ? 'dot-receipt' : 'dot-payment' }}">
                                        <i class="fas {{ $event['type'] === 'receipt' ? 'fa-truck' : 'fa-credit-card' }}"></i>
                                    </div>
                                    <div class="timeline-date">{{ \Carbon\Carbon::parse($event['date'])->format('d M Y') }}</div>
                                    <div class="timeline-label">{{ $event['label'] }}</div>
                                    <div class="timeline-ref">{{ $event['reference'] }}</div>
                                    <div class="timeline-detail">{{ $event['details'] }}</div>
                                    <div style="font-size:11px;color:var(--slate-400);margin-top:2px;">
                                        <i class="fas fa-user mr-1"></i>{{ $event['actor'] }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div style="padding:40px 0;text-align:center;color:var(--slate-400);">
                                <i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;"></i>
                                Belum ada aktivitas untuk PO ini.
                            </div>
                            @endif
                        </div>
                    </div>

        </div>{{-- /section-body --}}
    </section>
</div>
@push('scripts')
<script>
function copyLink(btn, url) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            $(btn).html('<i class="fas fa-check mr-1"></i> Tersalin');
            setTimeout(function() { $(btn).html('<i class="fas fa-copy mr-1"></i> Salin'); }, 2000);
            iziToast.success({ title: 'Berhasil', message: 'Link tersalin', position: 'topRight' });
        }).catch(function() { fallbackCopy(btn, url); });
    } else {
        fallbackCopy(btn, url);
    }
}
function fallbackCopy(btn, url) {
    var $input = $(btn).closest('.input-group').find('input');
    $input.focus().select();
    try {
        document.execCommand('copy');
        $(btn).html('<i class="fas fa-check mr-1"></i> Tersalin');
        setTimeout(function() { $(btn).html('<i class="fas fa-copy mr-1"></i> Salin'); }, 2000);
        iziToast.success({ title: 'Berhasil', message: 'Link tersalin', position: 'topRight' });
    } catch (e) {
        iziToast.info({ title: 'Salin Manual', message: 'Sorot link lalu copy manual (Ctrl+C)', position: 'topRight' });
    }
}
</script>
@endpush
@endsection
