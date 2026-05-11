@extends('master')
@section('title', 'Edit Pengajuan Dana')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.finance.fund_requests.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Edit Pengajuan Dana</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-8">
                    <div class="card">
                        <form action="{{ route('admin.finance.fund_requests.update', $fundRequest->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-header">
                                <h4>Form Edit Pengajuan</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Judul Pengajuan</label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $fundRequest->title) }}" required>
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Nominal Dana (Rp)</label>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $fundRequest->amount) }}" required>
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Deskripsi / Keperluan</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" style="height: 150px" required>{{ old('description', $fundRequest->description) }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Lampiran Baru (Opsional)</label>
                                    <input type="file" name="attachment" class="form-control">
                                    @if($fundRequest->attachment)
                                        <div class="mt-2">
                                            <small class="text-muted">Lampiran saat ini: <a href="{{ asset($fundRequest->attachment) }}" target="_blank">Lihat File</a></small>
                                        </div>
                                    @endif
                                    <small class="text-muted d-block mt-1">Format: JPG, PNG, PDF (Maks. 2MB). Kosongkan jika tidak ingin mengubah lampiran.</small>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
