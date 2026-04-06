@extends('master')
@section('title', 'Detail Produk - ' . $product->name)

@push('styles')
<style>
    .product-detail-container { animation: fadeIn 0.5s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .card-product-info { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; transition: all 0.3s ease; }
    .card-product-info:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    
    .product-badge { border-radius: 20px; padding: 5px 15px; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
    .badge-status-y { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
    .badge-status-n { background: linear-gradient(135deg, #dc3545, #f86d7d); color: white; }
    
    .price-tag { font-size: 1.5rem; font-weight: 700; color: #6777ef; }
    
    .gallery-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
    .gallery-item { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; cursor: pointer; transition: transform 0.2s; border: 2px solid transparent; }
    .gallery-item:hover { transform: scale(1.05); border-color: #6777ef; }
    
    .main-image { width: 100%; height: 350px; object-fit: contain; border-radius: 15px; background: #fdfdfd; border: 1px solid #eee; margin-bottom: 15px; }
    
    .info-label { color: #888; font-size: 0.85rem; margin-bottom: 2px; }
    .info-value { font-weight: 600; color: #333; margin-bottom: 15px; }
    
    .variant-row { border-left: 4px solid #6777ef; background: #f9f9ff; transition: all 0.2s; margin-bottom: 10px; border-radius: 5px; }
    .variant-row:hover { background: #f1f1ff; transform: translateX(5px); }
    
    .batch-table thead th { background: #f4f6f9; color: #333; text-transform: uppercase; font-size: 0.7rem; border: none; }
    
    .section-title-premium { position: relative; padding-bottom: 10px; margin-bottom: 20px; font-weight: 700; color: #34395e; }
    .section-title-premium::after { content: ''; position: absolute; bottom: 0; left: 0; width: 40px; height: 3px; background: #6777ef; border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ url('admin/manage-master/products') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Detail Produk</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/products') }}">Produk</a></div>
                <div class="breadcrumb-item active">Detail</div>
            </div>
        </div>

        <div class="section-body product-detail-container">
            <div class="row">
                <!-- Product Gallery & Core Info -->
                <div class="col-lg-5">
                    <div class="card card-product-info">
                        <div class="card-body">
                            @if($product->photos->count() > 0)
                                <img src="{{ asset($product->photos->first()->foto) }}" id="main-product-image" class="main-image shadow-sm" alt="{{ $product->name }}">
                                <div class="gallery-container">
                                    @foreach($product->photos as $photo)
                                        <img src="{{ asset($photo->foto) }}" class="gallery-item shadow-sm" onclick="changeImage('{{ asset($photo->foto) }}')" alt="Preview">
                                    @endforeach
                                </div>
                            @else
                                <img src="{{ asset('assets/img/Asset 3.png') }}" class="main-image shadow-sm" alt="No Image">
                            @endif

                            <hr>
                            
                            <div class="mt-4">
                                <h5 class="section-title-premium">Informasi Dasar</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="info-label">Kode Produk</div>
                                        <div class="info-value">{{ $product->code ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Status</div>
                                        <div class="info-value">
                                            @if($product->status == 'Y')
                                                <span class="product-badge badge-status-y">Aktif</span>
                                            @else
                                                <span class="product-badge badge-status-n">Non-Aktif</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Merk</div>
                                        <div class="info-value">{{ $product->merek->name ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Slug</div>
                                        <div class="info-value"><small class="text-muted">{{ $product->slug }}</small></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variations and Inventory -->
                <div class="col-lg-7">
                    <div class="card card-product-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="text-dark font-weight-bold mb-0">{{ $product->name }}</h2>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb p-0 bg-transparent mb-3" style="font-size: 0.9rem;">
                                            <li class="breadcrumb-item">{{ $product->category->name ?? 'No Category' }}</li>
                                            @if($product->subCategory)
                                                <li class="breadcrumb-item">{{ $product->subCategory->name }}</li>
                                            @endif
                                            @if($product->productType)
                                                <li class="breadcrumb-item active">{{ $product->productType->name }}</li>
                                            @endif
                                        </ol>
                                    </nav>
                                </div>
                                <div class="text-right">
                                    <div class="info-label">Total Stok</div>
                                    <div class="h3 font-weight-bold {{ $product->stock <= $product->min_stock_alert ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($product->stock, 0, ',', '.') }}
                                    </div>
                                    @if($product->stock <= $product->min_stock_alert)
                                        <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Stok Rendah</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4">
                                <h5 class="section-title-premium">Varian Produk (SKU)</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Varian</th>
                                                <th>SKU</th>
                                                <th class="text-right">Harga Jual</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product->variants as $variant)
                                                <tr>
                                                    <td>
                                                        <div class="font-weight-bold">{{ $variant->variant_name }}</div>
                                                        <small class="text-muted">{{ $variant->netto->netto_value ?? '' }} {{ $variant->netto->satuan ?? '' }}</small>
                                                    </td>
                                                    <td><code>{{ $variant->sku_code }}</code></td>
                                                    <td class="text-right font-weight-bold text-primary">Rp {{ number_format($variant->price, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h5 class="section-title-premium">Batch Log & Stok Real-time</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm batch-table">
                                        <thead>
                                            <tr>
                                                <th>Batch No</th>
                                                <th>Varian</th>
                                                <th>Tgl Expired</th>
                                                <th class="text-right">Hrg Beli</th>
                                                <th class="text-right">Sisa Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($product->batches as $batch)
                                                <tr>
                                                    <td><span class="badge badge-light font-weight-normal">{{ $batch->batch_no }}</span></td>
                                                    <td>{{ $batch->variant->variant_name ?? '-' }}</td>
                                                    <td>
                                                        @if($batch->expiry_date)
                                                            <span class="{{ \Carbon\Carbon::parse($batch->expiry_date)->isPast() ? 'text-danger font-weight-bold' : '' }}">
                                                            {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                                                            </span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="text-right">Rp {{ number_format($batch->buy_price, 0, ',', '.') }}</td>
                                                    <td class="text-right font-weight-bold text-dark">{{ number_format($batch->qty, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data batch untuk produk ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <a href="{{ url('admin/manage-master/products') }}" class="btn btn-secondary mr-2">Kembali</a>
                                <a href="{{ url('admin/manage-master/products') }}?edit={{ $product->id }}" class="btn btn-primary">Edit Produk</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@push('scripts')
<script>
    function changeImage(src) {
        $('#main-product-image').fadeOut(200, function() {
            $(this).attr('src', src).fadeIn(200);
        });
    }

    $(document).ready(function() {
        $('.btn-edit-trigger').on('click', function() {
            // Note: Since we are on a different page, the typical JS for loading the modal might need to be adjusted
            // or we just redirect to the index and open it there.
            // But for a premium feel, maybe we should have a specialized edit page? 
            // The user asked for "action detail (show)", so let's stick to showing first.
            window.location.href = "{{ url('admin/manage-master/products') }}?edit=" + $(this).data('id');
        });
    });
</script>
@endpush
