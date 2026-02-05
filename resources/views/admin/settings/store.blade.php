@extends('master')

@section('title', 'Admin | Pengaturan Toko')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Pengaturan Toko</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ url('admin/settings/general') }}">Pengaturan</a></div>
                    <div class="breadcrumb-item active">Toko</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Detail Toko</h2>
                <p class="section-lead">
                    Atur informasi toko Anda seperti logo, alamat, dan kontak yang akan ditampilkan di struk pembelian.
                </p>

                @if (session('message'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert">
                                <span>×</span>
                            </button>
                            {{ session('message') }}
                        </div>
                    </div>
                @endif

                <div class="card">
                    <form method="POST" action="{{ route('admin.settings.store.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-header">
                            <h4>Profil Toko</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Logo Toko</label>
                                <div class="col-sm-9">
                                    <div class="mb-2">
                                        @if($setting->logo_path)
                                            <img src="{{ asset($setting->logo_path) }}" alt="Logo" style="max-height: 80px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                                        @else
                                            <div class="text-muted small">Belum ada logo</div>
                                        @endif
                                    </div>
                                    <div class="custom-file">
                                        <input type="file" name="logo" class="custom-file-input" id="logoUpload">
                                        <label class="custom-file-label" for="logoUpload">Pilih file logo (PNG, JPG)...</label>
                                    </div>
                                    <small class="form-text text-muted">Format: png, jpg. Ukuran maksimal 2MB. Disarankan latar transparan.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Toko</label>
                                <div class="col-sm-9">
                                    <input type="text" name="store_name" class="form-control" value="{{ $setting->store_name }}" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat Lengkap</label>
                                <div class="col-sm-9">
                                    <textarea name="address" class="form-control" style="height: 100px" required>{{ $setting->address }}</textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nomor Telepon</label>
                                <div class="col-sm-9">
                                    <input type="text" name="phone" class="form-control" value="{{ $setting->phone }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">WhatsApp</label>
                                <div class="col-sm-9">
                                    <input type="text" name="whatsapp" class="form-control" value="{{ $setting->whatsapp }}">
                                    <small class="form-text text-muted">Akan ditampilkan di struk.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email</label>
                                <div class="col-sm-9">
                                    <input type="email" name="email" class="form-control" value="{{ $setting->email }}">
                                </div>
                            </div>

                            <hr>
                            <h6 class="text-muted mb-4">Media Sosial & Marketplace</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Instagram URL</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fab fa-instagram"></i></div>
                                            </div>
                                            <input type="text" name="instagram" class="form-control" value="{{ $setting->instagram }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Facebook URL</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fab fa-facebook"></i></div>
                                            </div>
                                            <input type="text" name="facebook" class="form-control" value="{{ $setting->facebook }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>TikTok URL</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fab fa-tiktok"></i></div>
                                            </div>
                                            <input type="text" name="tiktok" class="form-control" value="{{ $setting->tiktok }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Shopee URL</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text text-warning font-weight-bold">S</div>
                                            </div>
                                            <input type="text" name="shopee_url" class="form-control" value="{{ $setting->shopee_url }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tokopedia URL</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text text-success font-weight-bold">T</div>
                                            </div>
                                            <input type="text" name="tokopedia_url" class="form-control" value="{{ $setting->tokopedia_url }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Website</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fas fa-globe"></i></div>
                                            </div>
                                            <input type="text" name="website" class="form-control" value="{{ $setting->website }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="form-group">
                                <label>Teks Footer Struk</label>
                                <textarea name="footer_text" class="form-control" style="height: 60px">{{ $setting->footer_text }}</textarea>
                                <small class="form-text text-muted">Pesan yang muncul di bagian paling bawah struk.</small>
                            </div>

                        </div>
                        <div class="card-footer bg-whitesmoke text-right">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
