@extends('master')
@section('title', 'Detail Pengajuan Dana')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.finance.fund_requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Pengajuan Dana</h1>
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

            <div class="row">
                <div class="col-12 col-md-7 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h4>Informasi Pengajuan</h4>
                            <div class="card-header-action">
                                @php
                                    $statusMap = [
                                        'pending' => ['label' => 'Pending Manager', 'class' => 'warning'],
                                        'manager_approved' => ['label' => 'Pending Finance', 'class' => 'info'],
                                        'manager_rejected' => ['label' => 'Ditolak Manager', 'class' => 'danger'],
                                        'finance_approved' => ['label' => 'Disetujui Finance', 'class' => 'success'],
                                        'finance_rejected' => ['label' => 'Ditolak Finance', 'class' => 'danger'],
                                        'disbursed' => ['label' => 'Sudah Cair', 'class' => 'dark'],
                                    ];
                                    $s = $statusMap[$fundRequest->status] ?? ['label' => $fundRequest->status, 'class' => 'secondary'];
                                @endphp
                                <span class="badge badge-{{ $s['class'] }}">{{ $s['label'] }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 font-weight-bold">Kode Pengajuan</div>
                                <div class="col-sm-8">{{ $fundRequest->request_code }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 font-weight-bold">Judul</div>
                                <div class="col-sm-8">{{ $fundRequest->title }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 font-weight-bold">Pengaju</div>
                                <div class="col-sm-8">{{ $fundRequest->user->name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 font-weight-bold">Nominal</div>
                                <div class="col-sm-8 text-primary font-weight-bold" style="font-size: 1.2rem">
                                    Rp {{ number_format($fundRequest->amount, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 font-weight-bold">Deskripsi</div>
                                <div class="col-sm-8">{{ $fundRequest->description }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 font-weight-bold">Tanggal Pengajuan</div>
                                <div class="col-sm-8">{{ $fundRequest->created_at->format('d F Y H:i') }}</div>
                            </div>
                            @if($fundRequest->attachment)
                            <div class="row mb-3">
                                <div class="col-sm-4 font-weight-bold">Lampiran</div>
                                <div class="col-sm-8">
                                    <a href="{{ asset($fundRequest->attachment) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-paperclip"></i> Lihat Lampiran
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Timeline Approval --}}
                    <div class="card">
                        <div class="card-header">
                            <h4>Timeline Approval</h4>
                        </div>
                        <div class="card-body">
                            <div class="activities">
                                <div class="activity">
                                    <div class="activity-icon bg-primary text-white shadow-primary">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-primary">{{ $fundRequest->created_at->diffForHumans() }}</span>
                                            <span class="bullet"></span>
                                            <a class="text-job" href="#">Dibuat</a>
                                        </div>
                                        <p>Pengajuan dibuat oleh <strong>{{ $fundRequest->user->name }}</strong>.</p>
                                    </div>
                                </div>
                                @if($fundRequest->manager_id)
                                <div class="activity">
                                    <div class="activity-icon bg-{{ strpos($fundRequest->status, 'rejected') !== false ? 'danger' : 'success' }} text-white shadow-primary">
                                        <i class="fas fa-{{ strpos($fundRequest->status, 'rejected') !== false ? 'times' : 'check' }}"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-primary">{{ $fundRequest->manager_approved_at->diffForHumans() }}</span>
                                            <span class="bullet"></span>
                                            <a class="text-job" href="#">Store Manager Review</a>
                                        </div>
                                        <p>Direview oleh <strong>{{ $fundRequest->manager->name }}</strong>.</p>
                                        @if($fundRequest->manager_notes)
                                            <div class="alert alert-light border">
                                                <strong>Catatan:</strong> {{ $fundRequest->manager_notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if($fundRequest->finance_id)
                                <div class="activity">
                                    <div class="activity-icon bg-{{ strpos($fundRequest->status, 'finance_rejected') !== false ? 'danger' : 'success' }} text-white shadow-primary">
                                        <i class="fas fa-{{ strpos($fundRequest->status, 'finance_rejected') !== false ? 'times' : 'check' }}"></i>
                                    </div>
                                    <div class="activity-detail">
                                        <div class="mb-2">
                                            <span class="text-job text-primary">{{ $fundRequest->finance_approved_at->diffForHumans() }}</span>
                                            <span class="bullet"></span>
                                            <a class="text-job" href="#">Finance Review</a>
                                        </div>
                                        <p>Direview oleh <strong>{{ $fundRequest->finance->name }}</strong>.</p>
                                        @if($fundRequest->finance_notes)
                                            <div class="alert alert-light border">
                                                <strong>Catatan:</strong> {{ $fundRequest->finance_notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-5 col-lg-5">
                    @php $role = auth()->user()->role; @endphp

                    {{-- Panel Approval Manager --}}
                    @if($fundRequest->status == 'pending' && ($role == 'store_manager' || $role == 'super_admin'))
                    <div class="card">
                        <div class="card-header">
                            <h4>Persetujuan Manager</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.finance.fund_requests.approve_manager', $fundRequest->id) }}" method="POST" id="form-approve-manager">
                                @csrf
                                <div class="form-group">
                                    <label>Catatan Manager (Opsional)</label>
                                    <textarea name="notes" class="form-control" style="height: 100px"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check"></i> Setujui</button>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" formaction="{{ route('admin.finance.fund_requests.reject_manager', $fundRequest->id) }}" class="btn btn-danger btn-block"><i class="fas fa-times"></i> Tolak</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Panel Approval Finance --}}
                    @if($fundRequest->status == 'manager_approved' && ($role == 'finance' || $role == 'super_admin'))
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h4>Persetujuan Finance</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">Manager telah menyetujui pengajuan ini. Silakan verifikasi untuk proses pencairan.</div>
                            <form action="{{ route('admin.finance.fund_requests.approve_finance', $fundRequest->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Catatan Finance (Opsional)</label>
                                    <textarea name="notes" class="form-control" style="height: 100px"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check"></i> Setujui</button>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" formaction="{{ route('admin.finance.fund_requests.reject_finance', $fundRequest->id) }}" class="btn btn-danger btn-block"><i class="fas fa-times"></i> Tolak</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Panel Pencairan Dana (Finance) --}}
                    @if($fundRequest->status == 'finance_approved' && ($role == 'finance' || $role == 'super_admin'))
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h4>Pencairan Dana</h4>
                        </div>
                        <div class="card-body text-center">
                            <p>Pengajuan telah disetujui sepenuhnya. Klik tombol di bawah jika dana sudah diserahkan/ditransfer.</p>
                            <form action="{{ route('admin.finance.fund_requests.disburse', $fundRequest->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-dark btn-lg btn-block"><i class="fas fa-money-bill-wave"></i> Tandai Sudah Cair</button>
                            </form>
                        </div>
                    </div>
                    @endif
                    
                    @if($fundRequest->status == 'disbursed')
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>Selesai</h4>
                            <p>Dana pengajuan ini telah dicairkan.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
