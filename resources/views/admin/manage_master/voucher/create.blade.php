@extends('master')
@section('title', 'Tambah Voucher')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Tambah Voucher</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/voucher') }}">Data Voucher</a></div>
                <div class="breadcrumb-item active">Tambah Voucher</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Form Tambah Voucher</h2>
            <p class="section-lead">
                Buat voucher baru untuk produk Anda.
            </p>

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session()->get('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <form action="{{ url('admin/manage-master/voucher') }}" method="POST" class="needs-validation" novalidate="">
                    @csrf
                    <div class="card-header">
                        <h4>Form Input Voucher</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Voucher</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required placeholder="Contoh: Diskon Kemerdekaan">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Voucher</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" required placeholder="Contoh: MERDEKA45">
                                    <small class="form-text text-muted">Kode akan digabung dengan ID Produk agar unik.</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="d-block">Tipe Diskon</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="discount_type" id="type_percent" value="PERCENT" {{ old('discount_type', 'PERCENT') == 'PERCENT' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_percent">Persentase (%)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="discount_type" id="type_nominal" value="NOMINAL" {{ old('discount_type') == 'NOMINAL' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_nominal">Nominal (Rp)</label>
                                    </div>
                                    @error('discount_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6" id="percent_wrapper">
                                <div class="form-group">
                                    <label>Persentase Diskon (%)</label>
                                    <input type="number" class="form-control @error('percent') is-invalid @enderror" name="percent" value="{{ old('percent') }}" min="0" max="100">
                                    @error('percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="nominal_wrapper" style="display: none;">
                                <div class="form-group">
                                    <label>Nominal Diskon (Rp)</label>
                                    <input type="number" class="form-control @error('nominal') is-invalid @enderror" name="nominal" value="{{ old('nominal') }}" min="0">
                                    @error('nominal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                                        <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                                        <option value="NON ACTIVE" {{ old('status') == 'NON ACTIVE' ? 'selected' : '' }}>NON ACTIVE</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mulai Berlaku (Opsional)</label>
                                    <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Selesai Berlaku (Opsional)</label>
                                    <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Batas Penggunaan (Opsional)</label>
                            <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" name="usage_limit" value="{{ old('usage_limit') }}" min="1">
                            <small class="form-text text-muted">Kosongkan jika voucher dapat digunakan tanpa batas.</small>
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Pilih Produk (Multiple) <small class="text-info">(Opsional - Kosongkan jika untuk semua produk)</small></label>
                            <select class="form-control select2 @error('products') is-invalid @enderror" name="products[]" multiple="multiple">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ (collect(old('products'))->contains($product->id)) ? 'selected' : '' }}>{{ $product->name ?? $product->title }} ({{ $product->sku ?? 'No SKU' }})</option>
                                @endforeach
                            </select>
                            @error('products')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ url('admin/manage-master/voucher') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Bootstrap validation
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        $('.select2').select2({
            placeholder: "Pilih Produk yang akan diberikan voucher",
            allowClear: true
        });

        function toggleDiscountType() {
            if ($('#type_nominal').is(':checked')) {
                $('#percent_wrapper').hide();
                $('#nominal_wrapper').show();
            } else {
                $('#percent_wrapper').show();
                $('#nominal_wrapper').hide();
            }
        }

        $('input[name="discount_type"]').change(toggleDiscountType);
        toggleDiscountType(); // Run on load
    });
</script>
@endsection
