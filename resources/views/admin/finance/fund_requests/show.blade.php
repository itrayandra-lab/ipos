@extends('master')
@section('title', 'Detail Pengajuan Dana')

@push('styles')
<style>
    .req-card {
        background: #fff;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .req-card-header {
        background: #f8fafc;
        padding: 0.9rem 1.4rem;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .req-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: #334155;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .req-card-header h5 i { color: #0f766e; }
    .req-card-body { padding: 1.4rem; }

    /* Info rows */
    .info-row {
        display: flex;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        align-items: flex-start;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
        width: 160px;
        flex-shrink: 0;
        color: #64748b;
        font-weight: 600;
        font-size: 0.8rem;
        padding-top: 1px;
    }
    .info-value { color: #1e293b; flex: 1; }

    /* Status badge */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .status-pending    { background: #fef3c7; color: #92400e; }
    .status-approved   { background: #d1fae5; color: #065f46; }
    .status-rejected   { background: #fee2e2; color: #991b1b; }
    .status-disbursed  { background: #e0e7ff; color: #3730a3; }

    /* Amount highlight */
    .amount-big {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f766e;
        letter-spacing: -0.5px;
    }

    /* Bank card */
    .bank-box {
        background: #f0fdfa;
        border: 1px solid #ccfbf1;
        border-radius: 10px;
        padding: 1rem 1.2rem;
    }
    .bank-box .bank-name {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }
    .bank-box .bank-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f766e;
        letter-spacing: 1px;
    }
    .bank-box .bank-holder {
        font-size: 0.82rem;
        color: #475569;
        margin-top: 0.2rem;
    }

    /* Timeline */
    .timeline { position: relative; padding-left: 2rem; }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.6rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .tl-item { position: relative; margin-bottom: 1.5rem; }
    .tl-item:last-child { margin-bottom: 0; }
    .tl-dot {
        position: absolute;
        left: -1.65rem;
        top: 0.15rem;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.5rem;
        color: #fff;
        z-index: 1;
    }
    .tl-dot-primary  { background: #6366f1; }
    .tl-dot-success  { background: #10b981; }
    .tl-dot-danger   { background: #ef4444; }
    .tl-dot-info     { background: #3b82f6; }
    .tl-time { font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.2rem; }
    .tl-title { font-weight: 700; font-size: 0.85rem; color: #334155; }
    .tl-desc { font-size: 0.82rem; color: #64748b; margin-top: 0.2rem; }
    .tl-note {
        background: #f8fafc;
        border-left: 3px solid #e2e8f0;
        padding: 0.5rem 0.75rem;
        border-radius: 0 6px 6px 0;
        font-size: 0.8rem;
        color: #475569;
        margin-top: 0.5rem;
    }

    /* Action panel */
    .action-panel {
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .action-panel-header {
        padding: 0.9rem 1.4rem;
        font-weight: 700;
        font-size: 0.9rem;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .action-panel-body { background: #fff; padding: 1.4rem; }
    .btn-approve {
        background: #10b981; color: #fff; border: none;
        border-radius: 8px; padding: 0.6rem 1.2rem;
        font-weight: 700; font-size: 0.85rem; width: 100%;
        transition: background 0.2s;
    }
    .btn-approve:hover { background: #059669; color: #fff; }
    .btn-reject {
        background: #ef4444; color: #fff; border: none;
        border-radius: 8px; padding: 0.6rem 1.2rem;
        font-weight: 700; font-size: 0.85rem; width: 100%;
        transition: background 0.2s;
    }
    .btn-reject:hover { background: #dc2626; color: #fff; }
    .btn-disburse {
        background: #6366f1; color: #fff; border: none;
        border-radius: 8px; padding: 0.7rem 1.2rem;
        font-weight: 700; font-size: 0.88rem; width: 100%;
        transition: background 0.2s;
    }
    .btn-disburse:hover { background: #4f46e5; color: #fff; }
    .notes-input {
        width: 100%; border: 1px solid #e2e8f0; border-radius: 8px;
        padding: 0.55rem 0.9rem; font-size: 0.85rem; resize: vertical;
        min-height: 80px; color: #1e293b;
    }
    .notes-input:focus { border-color: #0f766e; outline: none; box-shadow: 0 0 0 3px rgba(15,118,110,0.1); }

    /* Disbursed success */
    .disbursed-box {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .disbursed-box i { font-size: 3rem; color: #059669; margin-bottom: 0.75rem; }
    .disbursed-box h5 { font-weight: 800; color: #065f46; margin-bottom: 0.5rem; }
    .disbursed-box p { font-size: 0.85rem; color: #047857; margin: 0; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.finance.fund_requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Pengajuan Dana</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item active">Detail Pengajuan</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('message'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ session('message') }}
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @php
                $statusMap = [
                    'pending'           => ['label' => 'Menunggu Persetujuan', 'class' => 'status-pending',  'icon' => 'clock'],
                    'manager_approved'  => ['label' => 'Disetujui Manager',    'class' => 'status-approved', 'icon' => 'check-circle'],
                    'manager_rejected'  => ['label' => 'Ditolak Manager',      'class' => 'status-rejected', 'icon' => 'times-circle'],
                    'finance_approved'  => ['label' => 'Disetujui Finance',    'class' => 'status-approved', 'icon' => 'check-circle'],
                    'finance_rejected'  => ['label' => 'Ditolak Finance',      'class' => 'status-rejected', 'icon' => 'times-circle'],
                    'disbursed'         => ['label' => 'Dana Cair',            'class' => 'status-disbursed','icon' => 'money-bill-wave'],
                ];
                $s    = $statusMap[$fundRequest->status] ?? ['label' => $fundRequest->status, 'class' => 'status-pending', 'icon' => 'circle'];
                $role = auth()->user()->role;
            @endphp

            <div class="row">
                {{-- Kolom Kiri --}}
                <div class="col-12 col-lg-7">

                    {{-- Header Info Card --}}
                    <div class="req-card">
                        <div class="req-card-header">
                            <h5><i class="fas fa-file-invoice-dollar"></i> Informasi Pengajuan</h5>
                            <span class="status-pill {{ $s['class'] }}">
                                <i class="fas fa-{{ $s['icon'] }}"></i> {{ $s['label'] }}
                            </span>
                        </div>
                        <div class="req-card-body">
                            {{-- Amount highlight --}}
                            <div class="text-center py-3 mb-3" style="background:#f0fdfa; border-radius:10px;">
                                <div style="font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.3rem;">Nominal Pengajuan</div>
                                <div class="amount-big">Rp {{ number_format($fundRequest->amount, 0, ',', '.') }}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Kode</div>
                                <div class="info-value"><code style="font-size:0.88rem;">{{ $fundRequest->request_code }}</code></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Pengaju</div>
                                <div class="info-value font-weight-bold">{{ $fundRequest->user->name }}</div>
                            </div>
                            @if($fundRequest->category)
                            <div class="info-row">
                                <div class="info-label">Kategori</div>
                                <div class="info-value">
                                    <span class="badge badge-light border">{{ $fundRequest->category->name }}</span>
                                </div>
                            </div>
                            @endif
                            <div class="info-row">
                                <div class="info-label">Judul Kegiatan</div>
                                <div class="info-value font-weight-bold">{{ $fundRequest->title }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Deskripsi</div>
                                <div class="info-value" style="white-space:pre-line;">{{ $fundRequest->description }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Tanggal Pengajuan</div>
                                <div class="info-value">{{ $fundRequest->created_at->format('d F Y, H:i') }}</div>
                            </div>
                            @if($fundRequest->attachment)
                            <div class="info-row">
                                <div class="info-label">Lampiran</div>
                                <div class="info-value">
                                    <a href="{{ asset($fundRequest->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:6px; font-size:0.8rem;">
                                        <i class="fas fa-paperclip mr-1"></i> Lihat Lampiran
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Bank Info --}}
                    <div class="req-card">
                        <div class="req-card-header">
                            <h5><i class="fas fa-university"></i> Rekening Tujuan Transfer</h5>
                        </div>
                        <div class="req-card-body">
                            <div class="bank-box">
                                <div class="bank-name">{{ $fundRequest->bank_name }}</div>
                                <div class="bank-number">{{ $fundRequest->bank_account_number }}</div>
                                <div class="bank-holder">a.n. {{ $fundRequest->bank_account_name }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="req-card">
                        <div class="req-card-header">
                            <h5><i class="fas fa-history"></i> Timeline Approval</h5>
                        </div>
                        <div class="req-card-body">
                            <div class="timeline">
                                <div class="tl-item">
                                    <div class="tl-dot tl-dot-primary"><i class="fas fa-plus"></i></div>
                                    <div class="tl-time">{{ $fundRequest->created_at->format('d M Y, H:i') }} &middot; {{ $fundRequest->created_at->diffForHumans() }}</div>
                                    <div class="tl-title">Pengajuan Dibuat</div>
                                    <div class="tl-desc">Oleh <strong>{{ $fundRequest->user->name }}</strong></div>
                                </div>

                                @if($fundRequest->manager_id)
                                @php $isManagerRejected = str_contains($fundRequest->status, 'manager_rejected'); @endphp
                                <div class="tl-item">
                                    <div class="tl-dot {{ $isManagerRejected ? 'tl-dot-danger' : 'tl-dot-success' }}">
                                        <i class="fas fa-{{ $isManagerRejected ? 'times' : 'check' }}"></i>
                                    </div>
                                    <div class="tl-time">{{ $fundRequest->manager_approved_at->format('d M Y, H:i') }} &middot; {{ $fundRequest->manager_approved_at->diffForHumans() }}</div>
                                    <div class="tl-title">{{ $isManagerRejected ? 'Ditolak Manager' : 'Disetujui Manager' }}</div>
                                    <div class="tl-desc">Oleh <strong>{{ $fundRequest->manager->name }}</strong></div>
                                    @if($fundRequest->manager_notes)
                                        <div class="tl-note"><i class="fas fa-comment-alt mr-1"></i> {{ $fundRequest->manager_notes }}</div>
                                    @endif
                                </div>
                                @endif

                                @if($fundRequest->finance_id)
                                @php $isFinanceRejected = str_contains($fundRequest->status, 'finance_rejected'); @endphp
                                <div class="tl-item">
                                    <div class="tl-dot {{ $isFinanceRejected ? 'tl-dot-danger' : 'tl-dot-success' }}">
                                        <i class="fas fa-{{ $isFinanceRejected ? 'times' : 'check' }}"></i>
                                    </div>
                                    <div class="tl-time">{{ $fundRequest->finance_approved_at->format('d M Y, H:i') }} &middot; {{ $fundRequest->finance_approved_at->diffForHumans() }}</div>
                                    <div class="tl-title">{{ $isFinanceRejected ? 'Ditolak Finance' : 'Disetujui Finance' }}</div>
                                    <div class="tl-desc">Oleh <strong>{{ $fundRequest->finance->name }}</strong></div>
                                    @if($fundRequest->finance_notes)
                                        <div class="tl-note"><i class="fas fa-comment-alt mr-1"></i> {{ $fundRequest->finance_notes }}</div>
                                    @endif
                                </div>
                                @endif

                                @if($fundRequest->status === 'disbursed')
                                <div class="tl-item">
                                    <div class="tl-dot tl-dot-info"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="tl-time">Dana telah dicairkan</div>
                                    <div class="tl-title">Dana Cair</div>
                                    <div class="tl-desc">Proses selesai</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="col-12 col-lg-5">

                    {{-- Disbursed success state --}}
                    @if($fundRequest->status === 'disbursed')
                    <div class="disbursed-box">
                        <div><i class="fas fa-check-circle"></i></div>
                        <h5>Dana Telah Dicairkan</h5>
                        <p>Pengajuan ini telah selesai diproses.</p>
                        @if($fundRequest->transfer_proof)
                            <a href="{{ asset($fundRequest->transfer_proof) }}" target="_blank"
                               class="btn btn-sm mt-3" style="background:#065f46; color:#fff; border-radius:8px; font-weight:700;">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Lihat Bukti Transfer
                            </a>
                        @endif
                    </div>
                    @endif

                    {{-- Panel Approval Finance --}}
                    @if(in_array($fundRequest->status, ['pending', 'manager_approved']) && in_array($role, ['finance', 'super_admin']))
                    <div class="action-panel">
                        <div class="action-panel-header" style="background: #0f766e;">
                            <i class="fas fa-clipboard-check"></i> Persetujuan Finance
                        </div>
                        <div class="action-panel-body">
                            <p style="font-size:0.85rem; color:#475569; margin-bottom:1rem;">
                                Verifikasi pengajuan ini untuk melanjutkan proses pencairan dana.
                            </p>
                            <div class="form-group">
                                <label style="font-size:0.8rem; font-weight:600; color:#475569;">Catatan (Opsional)</label>
                                <textarea class="notes-input" id="finance-notes" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6 pr-1">
                                    <form action="{{ route('admin.finance.fund_requests.approve_finance', $fundRequest->id) }}" method="POST" id="form-approve-finance">
                                        @csrf
                                        <input type="hidden" name="notes" id="approve-notes">
                                        <button type="submit" class="btn-approve" onclick="document.getElementById('approve-notes').value = document.getElementById('finance-notes').value">
                                            <i class="fas fa-check mr-1"></i> Setujui
                                        </button>
                                    </form>
                                </div>
                                <div class="col-6 pl-1">
                                    <form action="{{ route('admin.finance.fund_requests.reject_finance', $fundRequest->id) }}" method="POST" id="form-reject-finance">
                                        @csrf
                                        <input type="hidden" name="notes" id="reject-notes">
                                        <button type="submit" class="btn-reject" onclick="document.getElementById('reject-notes').value = document.getElementById('finance-notes').value">
                                            <i class="fas fa-times mr-1"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Panel Pencairan Dana --}}
                    @if($fundRequest->status === 'finance_approved' && in_array($role, ['finance', 'super_admin']))
                    <div class="action-panel">
                        <div class="action-panel-header" style="background: #6366f1;">
                            <i class="fas fa-money-bill-wave"></i> Pencairan Dana
                        </div>
                        <div class="action-panel-body">
                            <p style="font-size:0.85rem; color:#475569; margin-bottom:1rem;">
                                Pengajuan telah disetujui. Unggah bukti transfer untuk menyelesaikan proses.
                            </p>
                            <form action="{{ route('admin.finance.fund_requests.disburse', $fundRequest->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label style="font-size:0.8rem; font-weight:600; color:#475569;">Bukti Transfer <span class="text-danger">*</span></label>
                                    <input type="file" name="transfer_proof" class="form-control" required accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted">Format: JPG, PNG, PDF — Maks. 2MB</small>
                                </div>
                                <button type="submit" class="btn-disburse">
                                    <i class="fas fa-paper-plane mr-2"></i> Tandai Dana Cair
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Status info for non-actionable states --}}
                    @if($fundRequest->status === 'pending' && !in_array($role, ['finance', 'super_admin']))
                    <div class="req-card">
                        <div class="req-card-body text-center py-4">
                            <i class="fas fa-hourglass-half fa-2x text-warning mb-3"></i>
                            <div style="font-weight:700; color:#334155; margin-bottom:0.3rem;">Menunggu Persetujuan</div>
                            <div style="font-size:0.82rem; color:#64748b;">Pengajuan sedang dalam proses review oleh Finance.</div>
                        </div>
                    </div>
                    @endif

                    @if(in_array($fundRequest->status, ['manager_rejected', 'finance_rejected']))
                    <div class="req-card">
                        <div class="req-card-body text-center py-4">
                            <i class="fas fa-times-circle fa-2x text-danger mb-3"></i>
                            <div style="font-weight:700; color:#334155; margin-bottom:0.3rem;">Pengajuan Ditolak</div>
                            <div style="font-size:0.82rem; color:#64748b;">Lihat catatan di timeline untuk informasi lebih lanjut.</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
