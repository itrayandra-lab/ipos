@extends('master')
@section('title', 'Edit Pengajuan Dana')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --primary-color: #0f766e;
        --primary-hover: #0d9488;
        --border-radius: 12px;
        --card-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .premium-card {
        background: white;
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-top: 10px;
    }

    .card-header-premium {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #edf2f7;
    }

    .card-header-premium h4 {
        margin-bottom: 0;
        font-weight: 700;
        color: #334155;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-header-premium h4 i {
        color: var(--primary-color);
    }

    .card-body-premium {
        padding: 1.5rem;
        font-family: 'Inter', sans-serif;
    }

    .form-group label {
        font-weight: 600 !important;
        color: #475569 !important;
        font-size: 0.8rem !important;
        margin-bottom: 0.4rem !important;
    }

    .form-control-premium {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        padding: 0.5rem 0.8rem !important;
        font-size: 0.85rem !important;
        color: #1e293b !important;
        transition: all 0.2s ease !important;
    }

    .form-control-premium:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1) !important;
    }

    .input-group-text-premium {
        border-radius: 8px 0 0 8px !important;
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-right: none !important;
        color: #64748b !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
    }

    .input-group-premium .form-control-premium {
        border-radius: 0 8px 8px 0 !important;
    }

    .btn-submit-premium {
        background-color: var(--primary-color) !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 0.6rem 1.5rem !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        transition: all 0.2s ease !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-submit-premium:hover {
        background-color: var(--primary-hover) !important;
        transform: translateY(-1px);
    }

    .alert-premium {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
        background: #fffbeb;
        border: 1px solid #fef3c7;
        color: #92400e;
        margin-bottom: 1.5rem;
    }
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Edit Pengajuan Dana</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item">Edit Pengajuan</div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.finance.fund_requests.update', $fundRequest->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    {{-- Kolom Kiri: Detail Pengajuan --}}
                    <div class="col-12 col-lg-7">
                        <div class="premium-card">
                            <div class="card-header-premium">
                                <h4><i class="fas fa-edit mr-2"></i> Detail Pengajuan</h4>
                            </div>
                            <div class="card-body-premium">
                                <div class="form-group">
                                    <label>Kategori Pengajuan <span class="text-danger">*</span></label>
                                    <select name="expense_category_id" class="form-control-premium @error('expense_category_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('expense_category_id', $fundRequest->expense_category_id) == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Judul / Nama Kegiatan <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control-premium @error('title') is-invalid @enderror" value="{{ old('title', $fundRequest->title) }}" required>
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Nominal Dana <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-premium">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text-premium">Rp</span>
                                        </div>
                                        <input type="number" name="amount" class="form-control-premium @error('amount') is-invalid @enderror" value="{{ old('amount', $fundRequest->amount) }}" required>
                                    </div>
                                    @error('amount') <div class="invalid-feedback text-danger d-block mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Deskripsi & Keperluan <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control-premium @error('description') is-invalid @enderror" style="height: 150px" required>{{ old('description', $fundRequest->description) }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label>Lampiran Baru (Opsional)</label>
                                    <input type="file" name="attachment" class="form-control-premium">
                                    @if($fundRequest->attachment)
                                        <div class="mt-2">
                                            <small class="text-muted">Lampiran saat ini: <a href="{{ asset($fundRequest->attachment) }}" target="_blank">Lihat File</a></small>
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-1">Format: JPG, PNG, PDF (Maks. 2MB). Kosongkan jika tidak ingin mengubah.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Informasi Bank --}}
                    <div class="col-12 col-lg-5">
                        <div class="premium-card">
                            <div class="card-header-premium">
                                <h4><i class="fas fa-university mr-2"></i> Rekening Tujuan</h4>
                            </div>
                            <div class="card-body-premium">
                                <div class="alert alert-premium">
                                    <i class="fas fa-shield-alt mr-1"></i> Pastikan data rekening benar untuk menghindari kesalahan transfer.
                                </div>
                                <div class="form-group">
                                    <label>Nama Bank <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control-premium @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $fundRequest->bank_name) }}" required>
                                    @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Nomor Rekening <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="form-control-premium @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number', $fundRequest->bank_account_number) }}" required>
                                    @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group mb-4">
                                    <label>Atas Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_name" class="form-control-premium @error('bank_account_name') is-invalid @enderror" value="{{ old('bank_account_name', $fundRequest->bank_account_name) }}" required>
                                    @error('bank_account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-submit-premium w-100 justify-content-center py-3">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.finance.fund_requests.index') }}" class="btn btn-light w-100 mt-2 py-2" style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; color: #64748b;">
                                    Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

