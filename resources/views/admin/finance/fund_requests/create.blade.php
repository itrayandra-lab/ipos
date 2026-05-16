@extends('master')
@section('title', 'Buat Pengajuan Dana')

@section('content')
<style>
    .fund-card {
        background: #fff;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .fund-card-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .fund-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: #334155;
        font-size: 0.95rem;
    }
    .fund-card-header i { color: #0f766e; }
    .fund-card-body { padding: 1.5rem; }
    .fund-label {
        display: block;
        font-weight: 600;
        color: #475569;
        font-size: 0.82rem;
        margin-bottom: 0.4rem;
    }
    .fund-input {
        display: block;
        width: 100%;
        padding: 0.55rem 0.9rem;
        font-size: 0.88rem;
        color: #1e293b;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        transition: border-color 0.2s, box-shadow 0.2s;
        height: auto;
    }
    .fund-input:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15,118,110,0.1);
        outline: none;
    }
    .fund-input[readonly] {
        background: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .fund-input-group {
        display: flex;
        align-items: stretch;
    }
    .fund-input-prefix {
        display: flex;
        align-items: center;
        padding: 0.55rem 0.9rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-right: none;
        border-radius: 8px 0 0 8px;
        font-weight: 600;
        font-size: 0.88rem;
        color: #64748b;
        white-space: nowrap;
    }
    .fund-input-group .fund-input {
        border-radius: 0 8px 8px 0;
    }
    .fund-info-box {
        background: #f0fdfa;
        border: 1px solid #ccfbf1;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.82rem;
        color: #0f766e;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .fund-info-box i { margin-top: 2px; flex-shrink: 0; }
    .btn-fund-submit {
        background: #0f766e;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.7rem 1.5rem;
        font-weight: 700;
        font-size: 0.88rem;
        width: 100%;
        transition: background 0.2s, transform 0.15s;
    }
    .btn-fund-submit:hover {
        background: #0d9488;
        color: #fff;
        transform: translateY(-1px);
    }
    .btn-fund-cancel {
        display: block;
        text-align: center;
        margin-top: 0.6rem;
        padding: 0.6rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-fund-cancel:hover { background: #e2e8f0; color: #334155; text-decoration: none; }
    .form-group { margin-bottom: 1.1rem; }
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.finance.fund_requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Buat Pengajuan Dana</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item active">Pengajuan Dana</div>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ route('admin.finance.fund_requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{-- Kolom Kiri --}}
                    <div class="col-12 col-lg-7">
                        <div class="fund-card">
                            <div class="fund-card-header">
                                <i class="fas fa-info-circle"></i>
                                <h5>Detail Pengajuan</h5>
                            </div>
                            <div class="fund-card-body">

                                <div class="form-group">
                                    <label class="fund-label">Nama Pengaju</label>
                                    <input type="text" class="fund-input" value="{{ Auth::user()->name }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="fund-label">Kategori Pengajuan <span class="text-danger">*</span></label>
                                    <select name="expense_category_id" class="fund-input @error('expense_category_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('expense_category_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @if($categories->isEmpty())
                                        <div class="text-warning small mt-1">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Belum ada kategori. <a href="{{ route('admin.finance.expense_categories.index') }}" target="_blank">Tambah kategori dulu</a>.
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label class="fund-label">Judul / Nama Kegiatan <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="fund-input @error('title') is-invalid @enderror"
                                        value="{{ old('title') }}" placeholder="Contoh: Pembelian Stok ATK Kantor" required>
                                    @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="fund-label">Nominal Dana <span class="text-danger">*</span></label>
                                    <div class="fund-input-group">
                                        <span class="fund-input-prefix">Rp</span>
                                        <input type="number" name="amount" class="fund-input @error('amount') is-invalid @enderror"
                                            value="{{ old('amount') }}" placeholder="0" min="1" required>
                                    </div>
                                    @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="fund-label">Deskripsi & Keperluan <span class="text-danger">*</span></label>
                                    <textarea name="description" class="fund-input @error('description') is-invalid @enderror"
                                        rows="5" placeholder="Tuliskan rincian kebutuhan dana secara lengkap..." required>{{ old('description') }}</textarea>
                                    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-0">
                                    <label class="fund-label">Lampiran / Nota <span class="text-muted">(Opsional)</span></label>
                                    <input type="file" name="attachment" class="fund-input" accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted d-block mt-1">Format: JPG, PNG, PDF — Maks. 2MB</small>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="col-12 col-lg-5">
                        <div class="fund-card">
                            <div class="fund-card-header">
                                <i class="fas fa-university"></i>
                                <h5>Rekening Tujuan Transfer</h5>
                            </div>
                            <div class="fund-card-body">
                                <div class="fund-info-box">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Pastikan data rekening benar. Dana akan ditransfer ke rekening ini setelah disetujui.</span>
                                </div>

                                <div class="form-group">
                                    <label class="fund-label">Nama Bank <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="fund-input @error('bank_name') is-invalid @enderror"
                                        value="{{ old('bank_name') }}" placeholder="Contoh: BCA / Mandiri / BNI" required>
                                    @error('bank_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="fund-label">Nomor Rekening <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="fund-input @error('bank_account_number') is-invalid @enderror"
                                        value="{{ old('bank_account_number') }}" placeholder="Masukkan No. Rekening" required>
                                    @error('bank_account_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="fund-label">Atas Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_name" class="fund-input @error('bank_account_name') is-invalid @enderror"
                                        value="{{ old('bank_account_name') }}" placeholder="Nama Sesuai Rekening" required>
                                    @error('bank_account_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn-fund-submit">
                                        <i class="fas fa-paper-plane mr-2"></i> Kirim Pengajuan
                                    </button>
                                    <a href="{{ route('admin.finance.fund_requests.index') }}" class="btn-fund-cancel">Batal</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
