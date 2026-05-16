@extends('master')
@section('title', 'Pilih Cabang')
@section('content')
<style>
    .branch-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        margin-bottom: 15px;
        background: #fff;
    }
    .branch-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(103, 119, 239, 0.15);
        background: #6777ef;
    }
    .branch-card:hover .branch-name { color: #fff; }
    .branch-card:hover .branch-icon { background: rgba(255,255,255,0.2); color: #fff; }
    .branch-card:hover .branch-address { color: rgba(255,255,255,0.8); }
    .branch-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: rgba(103, 119, 239, 0.1);
        color: #6777ef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.3s ease;
    }
    .branch-name {
        font-weight: 700;
        color: #34395e;
        font-size: 1.1rem;
        display: block;
    }
    .branch-address {
        color: #98a6ad;
        font-size: 0.85rem;
    }
</style>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pilih Cabang Aktif</h1>
        </div>

        <div class="section-body">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="text-center mb-5">
                        <h2 class="font-weight-bold">Selamat Datang, {{ Auth::user()->name }}</h2>
                        <p class="text-muted">Silakan pilih cabang yang ingin Anda kelola saat ini untuk memisahkan data transaksi.</p>
                    </div>
                    
                    <div class="row">
                        @foreach(Auth::user()->warehouses as $warehouse)
                            <div class="col-md-6">
                                <a href="{{ route('branch.switch', $warehouse->id) }}" class="text-decoration-none">
                                    <div class="card branch-card">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="branch-icon mr-4">
                                                <i class="fas fa-store"></i>
                                            </div>
                                            <div>
                                                <span class="branch-name">{{ $warehouse->name }}</span>
                                                <span class="branch-address"><i class="fas fa-map-marker-alt mr-1"></i> {{ $warehouse->address ?? 'Alamat tidak tersedia' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ url('/logout') }}" class="btn btn-outline-danger btn-round">
                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
