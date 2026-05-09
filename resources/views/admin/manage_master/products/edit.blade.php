@extends('master')
@section('title', 'Edit Produk')
@section('content')
    <style>
        /* Premium Aesthetic Enhancements (Copied from Create) */
        :root {
            --primary-gradient: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        }

        .section-header {
            background: #fff;
            padding: 20px 25px !important;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px !important;
            border-left: 5px solid #0d9488;
        }

        .section-header h1 {
            font-weight: 800 !important;
            color: #1e293b !important;
            letter-spacing: -0.5px;
        }

        .card {
            border-radius: 15px !important;
            border: none !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04) !important;
            margin-bottom: 30px;
        }

        .card-header {
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 20px 25px !important;
        }

        .card-header h4 {
            color: #0d9488 !important;
            font-weight: 700 !important;
        }

        .btn-save-custom {
            background: var(--primary-gradient) !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 12px 30px !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3) !important;
            transition: all 0.3s;
            color: white !important;
        }
        .btn-save-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(13, 148, 136, 0.4) !important;
        }

        .form-group label {
            font-weight: 600 !important;
            color: #475569 !important;
            margin-bottom: 8px !important;
        }

        .form-control {
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 10px 15px !important;
            height: auto !important;
        }

        .form-control:focus {
            border-color: #0d9488 !important;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1) !important;
        }

        .img-preview {
            position: relative;
            width: 120px;
            height: 120px;
            margin-right: 15px;
            margin-bottom: 15px;
        }

        .img-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .custom-file-label {
            border-radius: 8px !important;
        }

        .btn-back {
            background: #f1f5f9 !important;
            color: #475569 !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 10px 20px !important;
            font-weight: 600 !important;
            margin-right: 10px;
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
                        <div class="col-12">
                            <!-- Informasi Utama -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3">
                                    <h4 class="text-primary"><i class="fas fa-info-circle mr-2"></i>Informasi Dasar</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Nama Produk <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $product->name }}" placeholder="Contoh: Mugwort Deep Cleansing Facial Wash" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Kode Produk (Reference)</label>
                                                <input type="text" name="code" class="form-control" value="{{ $product->code }}" placeholder="Contoh: MUG">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Merek <span class="text-danger">*</span></label>
                                                <select name="merek_id" class="form-control select2" required>
                                                    <option value="">Pilih Merek</option>
                                                    @foreach($merek as $m)
                                                        <option value="{{ $m->id }}" {{ $product->merek_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                                <select name="category_id" id="add-category" class="form-control select2" required>
                                                    <option value="">Pilih Kategori</option>
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
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
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Tipe Produk</label>
                                                <select name="product_type_id" class="form-control select2">
                                                    <option value="">Pilih Tipe</option>
                                                    @foreach($productTypes as $type)
                                                        <option value="{{ $type->id }}" {{ $product->product_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Min. Stock Alert</label>
                                                <input type="number" name="min_stock_alert" class="form-control" value="{{ $product->min_stock_alert }}" required min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Status Produk</label>
                                                <select name="status" class="form-control select2">
                                                    <option value="Y" {{ $product->status == 'Y' ? 'selected' : '' }}>Aktif (Tampil di POS)</option>
                                                    <option value="N" {{ $product->status == 'N' ? 'selected' : '' }}>Non-Aktif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="checkbox" class="custom-control-input" name="is_bundle" id="is_bundle" value="1" {{ $product->is_bundle ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="is_bundle">Ini adalah Produk Bundling</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Varian & Harga -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0"><i class="fas fa-tags mr-2"></i>Varian & SKU (Stock Keeping Unit)</h4>
                                    <button type="button" class="btn btn-success btn-sm" id="btn-add-variant">
                                        <i class="fas fa-plus mr-1"></i> Tambah Varian
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" id="table-variants">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="px-2" style="width: 10%;">Netto</th>
                                                    <th class="px-2" style="width: 15%;">Satuan</th>
                                                    <th class="px-2" style="width: 35%;">SKU Code</th>
                                                    <th class="px-2" style="width: 35%;">Harga Jual (Price)</th>
                                                    <th class="px-2" style="width: 5%;" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->variants as $index => $v)
                                                <tr>
                                                    <td class="px-2 align-middle">
                                                        <input type="text" name="variants[{{ $index }}][netto]" class="form-control px-2" value="{{ $v->netto->netto_value ?? '' }}" placeholder="100" required>
                                                    </td>
                                                    <td class="px-2 align-middle">
                                                        <select name="variants[{{ $index }}][satuan]" class="form-control select2 satuan-input" required>
                                                            <option value="">Pilih Satuan</option>
                                                            @foreach($netto_attributes as $attr)
                                                                <option value="{{ $attr->name }}" {{ ($v->netto->satuan ?? '') == $attr->name ? 'selected' : '' }}>{{ $attr->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-2 align-middle">
                                                        <input type="text" name="variants[{{ $index }}][sku]" class="form-control bg-light font-weight-bold text-primary px-2 sku-input" value="{{ $v->sku_code }}" placeholder="SKU" required style="letter-spacing: 0.5px; font-size: 11px;">
                                                    </td>
                                                    <td class="px-2 align-middle">
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span class="input-group-text px-2">Rp</span></div>
                                                            <input type="text" class="form-control rupiah-variant text-right font-weight-bold px-2" value="{{ number_format($v->price, 0, ',', '.') }}" placeholder="0" required>
                                                            <input type="hidden" name="variants[{{ $index }}][price]" class="raw-price-variant" value="{{ $v->price }}">
                                                        </div>
                                                    </td>
                                                    <td class="px-2 align-middle text-center">
                                                        @if($index > 0)
                                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Varian"><i class="fas fa-trash"></i></button>
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
                                            <i class="fas fa-info-circle mr-1"></i> SKU digenerate otomatis, tapi bisa Anda ubah secara manual jika diperlukan.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Foto Produk -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white py-3">
                                    <h4 class="text-primary"><i class="fas fa-images mr-2"></i>Foto Produk</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <div class="custom-file">
                                            <input type="file" name="foto[]" class="custom-file-input" id="foto" multiple accept="image/*">
                                            <label class="custom-file-label" for="foto">Pilih gambar...</label>
                                        </div>
                                        <small class="text-muted mt-2 d-block">Bisa pilih lebih dari 1 gambar. Format: JPG, PNG.</small>
                                        <input type="hidden" name="deleted_photos" id="deleted_photos">
                                        <div id="image-preview" class="mt-3 d-flex flex-wrap">
                                            @foreach($product->photos as $photo)
                                            <div class="img-preview mr-2 mb-2 position-relative" data-id="{{ $photo->id }}">
                                                <img src="{{ asset('') }}{{ $photo->foto }}" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover; border-radius: 12px; border: 2px solid #e2e8f0;">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute rounded-circle p-0" style="top: -5px; right: -5px; width: 25px; height: 25px; display:flex; align-items:center; justify-content:center;" onclick="removePhoto({{ $photo->id }})">×</button>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light border-0 shadow-none">
                                <div class="card-body d-flex justify-content-end align-items-center py-4">
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-back mr-3 px-5">Batal & Kembali</a>
                                    <button type="button" class="btn btn-save-custom px-5" id="btn-save-product">
                                        <i class="fas fa-save mr-2"></i> Update Produk
                                    </button>
                                </div>
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

            // Rupiah formatting
            $(document).on('input', '.rupiah-variant', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                $(this).next('.raw-price-variant').val(rawValue);
            });

            // Add Variant Row
            let variantIndex = {{ count($product->variants) }};
            $('#btn-add-variant').on('click', function() {
                let html = `
                    <tr>
                        <td class="px-2 align-middle"><input type="text" name="variants[${variantIndex}][netto]" class="form-control px-2" placeholder="100" required></td>
                        <td class="px-2 align-middle">
                            <select name="variants[${variantIndex}][satuan]" class="form-control select2-new" required>
                                <option value="">Pilih Satuan</option>
                                @foreach($netto_attributes as $attr)
                                    <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 align-middle"><input type="text" name="variants[${variantIndex}][sku]" class="form-control bg-light font-weight-bold text-primary px-2 sku-input" placeholder="SKU" required style="letter-spacing: 0.5px; font-size: 11px;"></td>
                        <td class="px-2 align-middle">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text px-2">Rp</span></div>
                                <input type="text" class="form-control rupiah-variant text-right font-weight-bold px-2" placeholder="0" required>
                                <input type="hidden" name="variants[${variantIndex}][price]" class="raw-price-variant">
                            </div>
                        </td>
                        <td class="px-2 align-middle text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Varian"><i class="fas fa-trash"></i></button>
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
                let fileName = Array.from(this.files).map(file => file.name).join(', ');
                $(this).next('.custom-file-label').text(fileName || 'Pilih gambar...');
                
                // Add new previews next to existing ones
                let previewContainer = $('#image-preview');
                Array.from(this.files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.append(`
                            <div class="img-preview mr-2 mb-2 position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover; border-radius: 12px; border: 2px solid #e2e8f0;">
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
