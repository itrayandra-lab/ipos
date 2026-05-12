@extends('master')
@section('title', 'Edit Produk')
@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #f0fdfa;
            --primary-gradient: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --background: #f8fafc;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            --input-focus: rgba(13, 148, 136, 0.15);
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--background);
        }

        .section-header {
            background: #ffffff !important;
            padding: 20px 25px !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
            margin: 0 0 30px 0 !important;
            border-left: 6px solid var(--primary) !important;
            border-top: none !important;
            border-right: none !important;
            border-bottom: none !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            position: relative !important;
            z-index: 10 !important;
            overflow: hidden !important;
            background-clip: padding-box !important;
        }

        .section-header::after, .section-header::before {
            display: none !important;
        }

        .section-header h1 {
            font-size: 24px !important;
            font-weight: 800 !important;
            color: #1e293b !important;
            letter-spacing: -0.025em;
            margin-bottom: 0 !important;
        }

        .section-header-breadcrumb {
            background: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
            backdrop-filter: none !important;
            border: none !important;
            margin-top: 0 !important;
        }

        .section-header-breadcrumb .breadcrumb-item, 
        .section-header-breadcrumb .breadcrumb-item a {
            color: #64748b !important;
            font-weight: 500 !important;
        }

        .section-header-breadcrumb .breadcrumb-item.active {
            color: var(--primary) !important;
            font-weight: 700 !important;
        }

        .premium-card {
            background: #ffffff;
            border-radius: 20px !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            box-shadow: var(--card-shadow) !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .premium-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.06), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        }

        .card-header-premium {
            background: #ffffff;
            border-bottom: 1px solid rgba(241, 245, 249, 0.5) !important;
            padding: 28px 30px !important;
            display: flex;
            align-items: center;
            position: relative;
        }

        .card-header-premium::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 30px;
            right: 30px;
            height: 1px;
            background: linear-gradient(90deg, var(--primary) 0%, transparent 100%);
            opacity: 0.3;
        }

        .card-header-premium h4 {
            margin: 0;
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            display: flex;
            align-items: center;
        }

        .card-header-premium .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }

        .form-group label {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            margin-bottom: 8px !important;
            display: block;
        }

        .form-control-premium {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            transition: all 0.2s ease;
            height: auto !important;
        }

        .form-control-premium:focus {
            background: #ffffff !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px var(--input-focus), 0 10px 15px -3px rgba(0, 0, 0, 0.05) !important;
            outline: none;
            transform: translateY(-1px);
        }

        .select2-container--default .select2-selection--single {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            height: 48px !important;
            padding: 10px 16px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-weight: 500 !important;
            line-height: 28px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
        }

        .variant-table thead th {
            background: #f8fafc !important;
            border-bottom: 2px solid #f1f5f9 !important;
            color: #64748b !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 11px !important;
            letter-spacing: 0.05em;
            padding: 16px !important;
        }

        .variant-table tbody td {
            padding: 20px 16px !important;
            border-bottom: 1px solid rgba(241, 245, 249, 0.8) !important;
            vertical-align: middle !important;
            transition: all 0.2s ease;
        }

        .variant-table tbody tr:hover td {
            background: var(--primary-light);
        }

        .btn-premium-save {
            background: var(--primary-gradient) !important;
            border: none !important;
            border-radius: 14px !important;
            padding: 16px 40px !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            color: #fff !important;
            box-shadow: 0 10px 20px -5px rgba(13, 148, 136, 0.4) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-premium-save:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px -5px rgba(13, 148, 136, 0.5) !important;
        }

        .btn-premium-save:active {
            transform: translateY(-1px);
        }

        .btn-back-premium {
            background: #ffffff !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            padding: 16px 30px !important;
            font-weight: 600 !important;
            transition: all 0.2s ease;
        }

        .btn-back-premium:hover {
            background: #f1f5f9 !important;
            color: #1e293b !important;
        }

        .image-upload-wrapper {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .image-upload-wrapper:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .img-preview-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin-right: 20px;
            margin-bottom: 20px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .img-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .img-preview-container .btn-remove {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .img-preview-container .btn-remove:hover {
            transform: scale(1.1);
            background: var(--danger);
        }

        .input-group-premium {
            position: relative;
            display: flex;
            align-items: stretch;
            width: 100%;
        }

        .input-group-premium .input-group-text {
            background: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px 0 0 12px !important;
            font-weight: 700;
            color: #64748b;
            padding: 0 16px;
        }

        .input-group-premium .form-control-premium {
            border-radius: 0 12px 12px 0 !important;
        }

        .badge-premium {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }

        .sticky-footer {
            position: sticky;
            bottom: 20px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            padding: 20px 30px !important;
            margin-top: 40px;
            z-index: 100;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            border-radius: 20px !important;
            box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.05) !important;
        }

        /* Animation */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-up {
            animation: fadeInUp 0.4s ease forwards;
        }
    /* Prevent Select2 text wrapping for cleaner UI */
    .select2-container--default .select2-selection--single {
        height: auto !important;
        min-height: 42px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        white-space: normal !important;
        word-break: break-word !important;
        line-height: 1.4 !important;
        padding: 8px 15px !important;
    }
    
    .product-col {
        width: 400px;
        max-width: 400px;
    }
    
    .summary-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
    }
    
    .summary-label {
        font-size: 10px;
        letter-spacing: 0.3px;
        color: #64748b;
        margin-bottom: 4px;
    }
    
    .summary-amount {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 8px;
    }
    
    .btn-apply-price {
        transition: all 0.3s;
        border: none;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.3px;
        padding: 5px 10px;
    }
    .btn-apply-price:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
</style>

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produk</a></div>
                    <div class="breadcrumb-item active">Edit Produk</div>
                </div>
            </div>

            <div class="section-body">
                <form id="form-edit-product" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Basic Information -->
                            <div class="premium-card animate-fade-up mb-4">
                                <div class="card-header-premium">
                                    <div class="icon-box"><i class="fas fa-info-circle"></i></div>
                                    <h4>Informasi Dasar Produk</h4>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label><i class="fas fa-signature mr-1"></i> Nama Produk <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control-premium w-100" value="{{ $product->name }}" placeholder="Contoh: Mugwort Deep Cleansing Facial Wash" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fas fa-barcode mr-1"></i> Kode Produk / Ref</label>
                                                <input type="text" name="code" class="form-control-premium w-100" value="{{ $product->code }}" placeholder="Contoh: MUG">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><i class="fas fa-copyright mr-1"></i> Merek <span class="text-danger">*</span></label>
                                                <select name="merek_id" class="form-control select2" required>
                                                    <option value="">Pilih Merek</option>
                                                    @foreach($merek as $m)
                                                        <option value="{{ $m->id }}" {{ $product->merek_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><i class="fas fa-warehouse mr-1"></i> Supplier / Pabrik</label>
                                                <select name="supplier_id" class="form-control select2">
                                                    <option value="">Pilih Supplier</option>
                                                    @foreach($suppliers as $s)
                                                        <option value="{{ $s->id }}" {{ $product->supplier_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><i class="fas fa-list-ul mr-1"></i> Kategori <span class="text-danger">*</span></label>
                                                <select name="category_id" id="add-category" class="form-control select2" required>
                                                    <option value="">Pilih Kategori</option>
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Sub Kategori</label>
                                                <select name="sub_category_id" id="add-sub-category" class="form-control select2">
                                                    <option value="">Pilih Sub Kategori</option>
                                                    @if($product->subCategory)
                                                        <option value="{{ $product->sub_category_id }}" selected>{{ $product->subCategory->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><i class="fas fa-shapes mr-1"></i> Tipe Produk</label>
                                                <select name="product_type_id" class="form-control select2">
                                                    <option value="">Pilih Tipe</option>
                                                    @foreach($productTypes as $type)
                                                        <option value="{{ $type->id }}" {{ $product->product_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Min. Stock Alert</label>
                                                <input type="number" name="min_stock_alert" class="form-control-premium w-100" value="{{ $product->min_stock_alert }}" required min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Status Produk</label>
                                                <select name="status" class="form-control-premium w-100">
                                                    <option value="Y" {{ $product->status == 'Y' ? 'selected' : '' }}>Aktif (Tampil di POS)</option>
                                                    <option value="N" {{ $product->status == 'N' ? 'selected' : '' }}>Non-Aktif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-0 mt-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_bundle" id="is_bundle" value="1" {{ $product->is_bundle ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold text-primary" for="is_bundle" style="cursor: pointer;">Produk ini adalah Paket / Bundling</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bundle Items -->
                            <div class="premium-card animate-fade-up mb-4" id="bundle-items-section" style="display: {{ $product->is_bundle ? 'block' : 'none' }};">
                                <div class="card-header-premium">
                                    <div class="icon-box"><i class="fas fa-layer-group"></i></div>
                                    <h4 class="flex-grow-1">Komponen Bundling</h4>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" id="btn-add-bundle-item">
                                        <i class="fas fa-plus mr-1"></i> Tambah Item
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover mb-0 variant-table" id="table-bundle-items">
                                        <thead>
                                            <tr>
                                                <th class="pl-4 product-col">Produk</th>
                                                <th width="140px">HPP (Modal)</th>
                                                <th width="160px">Harga Jual Satuan</th>
                                                <th width="90px">Jumlah</th>
                                                <th width="160px">Subtotal HPP</th>
                                                <th width="60px" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($product->is_bundle)
                                                @foreach($product->bundleItems as $bi)
                                                    @php
                                                        $comp = $bi->product;
                                                        $selectedVariant = $bi->variant;
                                                        $latestBatch = $comp ? \App\Models\ProductBatch::where('product_id', $comp->id)->orderBy('id', 'desc')->first() : null;
                                                        $buyPrice = $latestBatch?->buy_price ?? 0;
                                                        
                                                        // Use selected variant price or fallback
                                                        $sellPrice = 0;
                                                        if ($selectedVariant) {
                                                            $sellPrice = $selectedVariant->price;
                                                            $isApproved = $selectedVariant->is_approved;
                                                            $hetPrice = $selectedVariant->het_online ?: $sellPrice;
                                                        } else {
                                                            $sellPrice = $comp ? ($comp->price_real > 0 ? $comp->price_real : ($comp->variants->first()?->price ?? $comp->price)) : 0;
                                                            $isApproved = $comp?->variants->first()?->is_approved ?? false;
                                                            $hetPrice = $sellPrice;
                                                        }
                                                        
                                                        $subHpp = $buyPrice * $bi->quantity;
                                                    @endphp
                                                    <tr class="bundle-row" 
                                                        data-approved="{{ $isApproved ? '1' : '0' }}"
                                                        data-het="{{ $hetPrice }}"
                                                        data-legacy="{{ $sellPrice }}">
                                                        <td class="pl-4">
                                                            <select name="bundle_items[{{ $loop->index }}][variant_id]" class="form-control select-product-bundle" required>
                                                                @if($selectedVariant)
                                                                    @php
                                                                        $mName = $comp->merek->name ?? '';
                                                                        $pName = $comp->name ?? '';
                                                                        $vName = ($selectedVariant->variant_name && $selectedVariant->variant_name !== 'Default') ? $selectedVariant->variant_name : '';
                                                                        $nVal = $selectedVariant->netto->netto_value ?? '';
                                                                        $nSat = $selectedVariant->netto->satuan ?? '';
                                                                        $nFull = trim($nVal . ' ' . $nSat);

                                                                        $lblParts = [];
                                                                        if ($mName) $lblParts[] = $mName;
                                                                        
                                                                        if ($vName) {
                                                                            if (stripos($vName, $pName) !== false) {
                                                                                $lblParts[] = $vName;
                                                                            } else {
                                                                                $lblParts[] = $pName;
                                                                                $lblParts[] = $vName;
                                                                            }
                                                                        } else {
                                                                            $lblParts[] = $pName;
                                                                        }

                                                                        if ($nFull) {
                                                                            $cleanCurr = strtolower(str_replace(' ', '', implode('', $lblParts)));
                                                                            $cleanN = strtolower(str_replace(' ', '', $nFull));
                                                                            if (strpos($cleanCurr, $cleanN) === false) {
                                                                                $lblParts[] = $nFull;
                                                                            }
                                                                        }

                                                                        $displayLabel = implode(' - ', array_filter($lblParts));
                                                                        $displayLabel = preg_replace('/\s+/', ' ', $displayLabel);
                                                                    @endphp
                                                                    <option value="{{ $selectedVariant->id }}" selected>
                                                                        {{ $displayLabel }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                            <input type="hidden" name="bundle_items[{{ $loop->index }}][product_id]" value="{{ $bi->product_id }}" class="product-id-input">
                                                        </td>
                                                        <td>
                                                            <div class="hpp-display" data-value="{{ $buyPrice }}">Rp {{ number_format($buyPrice, 0, ',', '.') }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="price-display" data-value="{{ $sellPrice }}">Rp {{ number_format($sellPrice, 0, ',', '.') }}</div>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="bundle_items[{{ $loop->index }}][quantity]" class="form-control-premium w-100 bundle-qty" value="{{ $bi->quantity }}" min="1" required>
                                                        </td>
                                                        <td>
                                                            <div class="subtotal-hpp-display font-weight-bold">Rp {{ number_format($subHpp, 0, ',', '.') }}</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle border-0 btn-remove-row"><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="p-3" id="bundle-summary" style="{{ $product->is_bundle ? '' : 'display: none;' }} background: #f1f5f9;">
                                        <div class="row no-gutters mx-n2">
                                            <div class="col-md-3 px-2">
                                                <div class="summary-card h-100">
                                                    <div class="summary-label text-uppercase font-weight-bold">Total Modal (HPP)</div>
                                                    <div class="summary-amount text-danger" id="total-bundle-hpp">Rp 0</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-2">
                                                <div class="summary-card h-100">
                                                    <div class="summary-label text-uppercase font-weight-bold">Total Harga Normal</div>
                                                    <div class="summary-amount text-dark" id="total-bundle-normal">Rp 0</div>
                                                    <button type="button" class="btn btn-info btn-block btn-sm rounded-pill btn-apply-price" id="btn-apply-normal-price">
                                                        <i class="fas fa-check-circle mr-1"></i> Gunakan Normal
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-2">
                                                <div class="summary-card h-100 border-primary" style="border-width: 1.5px;">
                                                    <div class="summary-label text-uppercase font-weight-bold">Harga Transisi</div>
                                                    <div class="summary-amount text-primary" id="bundle-transition-price">Rp 0</div>
                                                    <button type="button" class="btn btn-primary btn-block btn-sm rounded-pill btn-apply-price" id="btn-apply-transition-price">
                                                        <i class="fas fa-magic mr-1"></i> Gunakan Transisi
                                                    </button>
                                                    <div class="mt-1 text-center" style="font-size: 9px;">Approved? <span id="status-all-approved" class="badge badge-secondary py-0 px-1" style="font-size: 8px;">Tidak</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 px-2">
                                                <div class="summary-card h-100 bg-white shadow-sm">
                                                    <div class="summary-label text-uppercase font-weight-bold">Margin Keuntungan</div>
                                                    <div class="summary-amount mb-0" id="bundle-profit-margin">Rp 0</div>
                                                    <div class="text-muted mt-1" style="font-size: 9px;">(Berdasarkan Harga Jual Paket)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Variants & SKU -->
                            <div class="premium-card animate-fade-up mb-4">
                                <div class="card-header-premium">
                                    <div class="icon-box"><i class="fas fa-tags"></i></div>
                                    <h4 class="flex-grow-1">Varian & SKU (Inventory)</h4>
                                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3" id="btn-add-variant">
                                        <i class="fas fa-plus mr-1"></i> Tambah Varian
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 variant-table" id="table-variants">
                                            <thead>
                                                <tr>
                                                    <th width="12%">Netto</th>
                                                    <th width="18%">Satuan</th>
                                                    <th width="35%">SKU Code</th>
                                                    <th width="30%">Harga Jual (Price)</th>
                                                    <th width="5%" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->variants as $index => $v)
                                                <tr>
                                                    <td>
                                                        <input type="text" name="variants[{{ $index }}][netto]" class="form-control-premium w-100" value="{{ $v->netto->netto_value ?? '' }}" placeholder="100" required>
                                                    </td>
                                                    <td>
                                                        <select name="variants[{{ $index }}][satuan]" class="form-control select2 satuan-input" required>
                                                            <option value="">Pilih</option>
                                                            @foreach($netto_attributes as $attr)
                                                                <option value="{{ $attr->name }}" {{ ($v->netto->satuan ?? '') == $attr->name ? 'selected' : '' }}>{{ $attr->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="variants[{{ $index }}][sku]" class="form-control-premium w-100 bg-white font-weight-bold text-primary sku-input" value="{{ $v->sku_code }}" placeholder="SKU" required style="letter-spacing: 0.5px;">
                                                    </td>
                                                    <td>
                                                        <div class="input-group-premium">
                                                            <div class="input-group-text">Rp</div>
                                                            <input type="text" class="form-control-premium w-100 rupiah-variant text-right font-weight-bold" value="{{ number_format($v->price, 0, ',', '.') }}" placeholder="0" required>
                                                            <input type="hidden" name="variants[{{ $index }}][price]" class="raw-price-variant" value="{{ $v->price }}">
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($index > 0)
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle border-0 btn-remove-row" title="Hapus Varian"><i class="fas fa-trash"></i></button>
                                                        @else
                                                        <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3 bg-light border-top">
                                        <small class="text-muted">
                                            <i class="fas fa-magic mr-1 text-primary"></i> SKU digenerate otomatis. Klik SKU untuk mengedit manual jika diperlukan.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Photos -->
                            <div class="premium-card animate-fade-up mb-5">
                                <div class="card-header-premium">
                                    <div class="icon-box"><i class="fas fa-images"></i></div>
                                    <h4>Foto Produk</h4>
                                </div>
                                <div class="card-body p-4">
                                    <div class="image-upload-wrapper mb-4" onclick="$('#foto').click()">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <h5 class="mb-1">Klik atau seret file gambar baru ke sini</h5>
                                        <p class="text-muted small mb-0">Mendukung format JPG, PNG. Format: JPG, PNG.</p>
                                        <input type="file" name="foto[]" class="d-none" id="foto" multiple accept="image/*">
                                    </div>
                                    
                                    <input type="hidden" name="deleted_photos" id="deleted_photos">
                                    <div id="image-preview" class="d-flex flex-wrap">
                                        @foreach($product->photos as $photo)
                                        <div class="img-preview-container animate-fade-up" data-id="{{ $photo->id }}">
                                            <img src="{{ asset('') }}{{ $photo->foto }}">
                                            <button type="button" class="btn-remove" onclick="removePhoto({{ $photo->id }})"><i class="fas fa-times"></i></button>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Action Bar (Sticky) -->
                            <div class="premium-card sticky-footer">
                                <a href="{{ route('admin.products.index') }}" class="btn-back-premium mr-3">Batal & Kembali</a>
                                <button type="button" class="btn-premium-save" id="btn-save-product">
                                    <i class="fas fa-save mr-2"></i> Update Data Produk
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @push('scripts')
    <script>
        const MEREK_DATA = @json($merek);
        const CATEGORY_DATA = @json($categories);

        function getMerekCode(id) {
            if (!id) return 'UNK';
            const m = MEREK_DATA.find(x => x.id == id);
            return m ? (m.code || 'UNK') : 'UNK';
        }

        function getCategoryCode(id) {
            if (!id) return 'UNK';
            const c = CATEGORY_DATA.find(x => x.id == id);
            return c ? (c.code || 'UNK') : 'UNK';
        }

        function updateAllSkus() {
            const merekId = $('select[name="merek_id"]').val();
            const categoryId = $('#add-category').val();
            const productCode = $('input[name="code"]').val() || 'UNK';
            const mCode = getMerekCode(merekId);
            const cCode = getCategoryCode(categoryId);

            $('#table-variants tbody tr').each(function() {
                const skuInput = $(this).find('input[name*="[sku]"]');
                // Only auto-generate if user hasn't marked it as manually edited
                if(skuInput.attr('data-manual') !== 'true') {
                    const netto = $(this).find('input[name*="[netto]"]').val() || '';
                    const nettoPart = netto.replace(/[^0-9]/g, '');
                    const sku = `${mCode}-${cCode}-${productCode}-${nettoPart}`.toUpperCase();
                    skuInput.val(sku);
                }
            });
        }

        function formatRupiah(angka) {
            let number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        let deletedPhotoIds = [];
        function removePhoto(id) {
            $(`.img-preview[data-id="${id}"]`).remove();
            deletedPhotoIds.push(id);
            $('#deleted_photos').val(deletedPhotoIds.join(','));
        }

        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            // Mark SKU as manual if user edits it directly
            $(document).on('input', '.sku-input', function() {
                $(this).attr('data-manual', 'true');
            });

            // Cascading Category
            $('#add-category').on('change', function() {
                const categoryId = $(this).val();
                if (categoryId) {
                    $.get("{{ url('admin/manage-master/categories/get-subs') }}", { id: categoryId }, function(data) {
                        let options = '<option value="">Pilih Sub Kategori</option>';
                        data.forEach(sub => { options += `<option value="${sub.id}">${sub.name}</option>`; });
                        $('#add-sub-category').html(options).trigger('change');
                        updateAllSkus();
                    });
                }
            });

            // SKU Generation Triggers
            $('input[name="name"], input[name="code"], select[name="merek_id"], #add-category').on('change input', function() {
                updateAllSkus();
            });

            $(document).on('input', 'input[name*="[netto]"]', function() {
                updateAllSkus();
            });

            $('#btn-apply-normal-price').on('click', function() {
                const totalNormal = parseInt($('#total-bundle-normal').text().replace(/[^0-9]/g, '')) || 0;
                const priceInput = $('.rupiah-variant').first();
                priceInput.val(formatRupiah(totalNormal)).trigger('input');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Harga Diterapkan',
                    text: 'Harga paket telah disesuaikan dengan total HET komponen.',
                    timer: 1000,
                    showConfirmButton: false
                });
            });

            // Rupiah formatting
            $(document).on('input', '.rupiah-variant', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                $(this).next('.raw-price-variant').val(rawValue);
                calculateBundleTotals();
            });

            // Bundling logic
            $('#is_bundle').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#bundle-items-section').slideDown();
                    $('#bundle-summary').show();
                } else {
                    $('#bundle-items-section').slideUp();
                    $('#bundle-summary').hide();
                }
            });

            function calculateBundleTotals() {
                let totalHpp = 0;
                let totalNormal = 0;
                let totalHetSum = 0;
                let totalLegacySum = 0;
                let allApproved = true;

                $('.bundle-row').each(function() {
                    const qty = parseInt($(this).find('.bundle-qty').val()) || 0;
                    const hpp = parseInt($(this).find('.hpp-display').attr('data-value')) || 0;
                    const price = parseInt($(this).find('.price-display').attr('data-value')) || 0;
                    
                    const approved = $(this).attr('data-approved') == 1;
                    const het = parseInt($(this).attr('data-het')) || price;
                    const legacy = parseInt($(this).attr('data-legacy')) || price;

                    if ($(this).find('.select-product-bundle').val()) {
                        if (!approved) allApproved = false;
                        totalHetSum += het * qty;
                        totalLegacySum += legacy * qty;
                    }

                    const subHpp = hpp * qty;
                    const subNormal = price * qty;
                    
                    $(this).find('.subtotal-hpp-display').text('Rp ' + formatRupiah(subHpp));
                    
                    totalHpp += subHpp;
                    totalNormal += subNormal;
                });

                $('#total-bundle-hpp').text('Rp ' + formatRupiah(totalHpp));
                $('#total-bundle-normal').text('Rp ' + formatRupiah(totalNormal));

                const transitionPrice = allApproved ? totalHetSum : totalLegacySum;
                $('#bundle-transition-price').text('Rp ' + formatRupiah(transitionPrice));
                $('#status-all-approved').text(allApproved ? 'Ya' : 'Tidak')
                    .removeClass('text-success text-danger')
                    .addClass(allApproved ? 'text-success' : 'text-danger');

                // Calculate Profit based on Bundle Selling Price
                const bundleSellingPrice = parseInt($('.raw-price-variant').first().val()) || 0;
                const margin = bundleSellingPrice - totalHpp;
                
                $('#bundle-profit-margin').text('Rp ' + formatRupiah(margin));
                if(margin < 0) {
                    $('#bundle-profit-margin').removeClass('text-success').addClass('text-danger');
                } else {
                    $('#bundle-profit-margin').removeClass('text-danger').addClass('text-success');
                }
            }

            $('#btn-apply-transition-price').on('click', function() {
                const transitionPrice = parseInt($('#bundle-transition-price').text().replace(/[^0-9]/g, '')) || 0;
                const priceInput = $('.rupiah-variant').first();
                priceInput.val(formatRupiah(transitionPrice)).trigger('input');
                
                Swal.fire({
                    icon: 'info',
                    title: 'Harga Transisi Diterapkan',
                    text: 'Harga paket disesuaikan dengan status approval komponen.',
                    timer: 1000,
                    showConfirmButton: false
                });
            });

            let bundleItemIndex = {{ $product->is_bundle ? count($product->bundleItems) : 0 }};
            $('#btn-add-bundle-item').on('click', function() {
                const row = `
                    <tr class="bundle-row">
                        <td class="pl-4">
                            <select name="bundle_items[${bundleItemIndex}][variant_id]" class="form-control select-product-bundle" required></select>
                            <input type="hidden" name="bundle_items[${bundleItemIndex}][product_id]" class="product-id-input">
                        </td>
                        <td>
                            <div class="hpp-display" data-value="0">Rp 0</div>
                        </td>
                        <td>
                            <div class="price-display" data-value="0">Rp 0</div>
                        </td>
                        <td>
                            <input type="number" name="bundle_items[${bundleItemIndex}][quantity]" class="form-control-premium w-100 bundle-qty" value="1" min="1" required>
                        </td>
                        <td>
                            <div class="subtotal-hpp-display font-weight-bold">Rp 0</div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle border-0 btn-remove-row"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table-bundle-items tbody').append(row);
                $('#bundle-summary').show();
                
                const newRow = $('#table-bundle-items tbody tr:last');
                const newSelect = newRow.find('.select-product-bundle');
                
                initBundleSelect(newSelect);
                bundleItemIndex++;
            });

            function initBundleSelect(selector) {
                selector.select2({
                    placeholder: 'Cari Merek + Produk + Varian...',
                    ajax: {
                        url: "{{ route('admin.products.search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { search: params.term };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(v => ({ 
                                    id: v.variant_id, 
                                    text: v.text,
                                    product_id: v.product_id,
                                    buy_price: v.buy_price,
                                    selling_price: v.selling_price,
                                    het_online: v.het_online,
                                    legacy_price: v.legacy_price,
                                    is_approved: v.is_approved
                                }))
                            };
                        }
                    }
                });

                selector.on('select2:select', function(e) {
                    const data = e.params.data;
                    const row = $(this).closest('tr');
                    
                    row.find('.product-id-input').val(data.product_id);
                    row.find('.hpp-display').text('Rp ' + formatRupiah(data.buy_price)).attr('data-value', data.buy_price);
                    row.find('.price-display').text('Rp ' + formatRupiah(data.selling_price)).attr('data-value', data.selling_price);
                    
                    row.attr('data-het', data.het_online);
                    row.attr('data-legacy', data.legacy_price);
                    row.attr('data-approved', data.is_approved ? 1 : 0);
                    
                    calculateBundleTotals();
                });
            }

            // Initialize existing bundle selects
            $('.select-product-bundle').each(function() {
                initBundleSelect($(this));
            });

            $(document).on('change', '.select-variant-bundle', function() {
                const option = $(this).find('option:selected');
                const row = $(this).closest('tr');
                const price = option.data('price') || 0;
                row.find('.price-display').text('Rp ' + formatRupiah(price)).attr('data-value', price);
                calculateBundleTotals();
            });

            $(document).on('input', '.bundle-qty', function() {
                calculateBundleTotals();
            });

            // Run initial calculation
            if($('#is_bundle').is(':checked')) {
                calculateBundleTotals();
            }

            // Add Variant Row
            let variantIndex = {{ count($product->variants) }};
            $('#btn-add-variant').on('click', function() {
                let html = `
                    <tr>
                        <td><input type="text" name="variants[${variantIndex}][netto]" class="form-control-premium w-100" placeholder="100" required></td>
                        <td>
                            <select name="variants[${variantIndex}][satuan]" class="form-control select2-new" required>
                                <option value="">Pilih</option>
                                @foreach($netto_attributes as $attr)
                                    <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="variants[${variantIndex}][sku]" class="form-control-premium w-100 bg-white font-weight-bold text-primary sku-input" placeholder="SKU" required style="letter-spacing: 0.5px;"></td>
                        <td>
                            <div class="input-group-premium">
                                <div class="input-group-text">Rp</div>
                                <input type="text" class="form-control-premium w-100 rupiah-variant text-right font-weight-bold" placeholder="0" required>
                                <input type="hidden" name="variants[${variantIndex}][price]" class="raw-price-variant">
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle border-0 btn-remove-row" title="Hapus Varian"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table-variants tbody').append(html);
                $('.select2-new').select2({ width: '100%' }).removeClass('select2-new');
                variantIndex++;
                updateAllSkus();
            });

            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Photo Preview
            $('#foto').on('change', function() {
                // Add new previews next to existing ones
                let previewContainer = $('#image-preview');
                Array.from(this.files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.append(`
                            <div class="img-preview-container animate-fade-up">
                                <img src="${e.target.result}">
                                <button type="button" class="btn-remove" onclick="$(this).parent().remove()"><i class="fas fa-times"></i></button>
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });

            // Save Product AJAX
            $('#btn-save-product').on('click', function() {
                let form = $('#form-edit-product')[0];
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                let formData = new FormData(form);
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sedang Memproses...');

                $.ajax({
                    url: "{{ url('admin/manage-master/products/update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = "{{ route('admin.products.index') }}";
                        });
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Update Produk');
                        let msg = err.responseJSON?.message || 'Terjadi kesalahan sistem';
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg, confirmButtonColor: '#0d9488' });
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
