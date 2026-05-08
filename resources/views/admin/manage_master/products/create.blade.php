@extends('master')
@section('title', 'Tambah Produk Baru')
@section('content')
    <div class="main-content">
        <style>
            /* Premium Aesthetic Enhancements */
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

            .variant-card {
                border: 1px solid #f1f5f9;
                border-radius: 12px;
                padding: 20px;
                background: #f8fafc;
                margin-bottom: 15px;
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

        <section class="section">
            <div class="section-header">
                <h1>Tambah Produk Baru</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Produk</a></div>
                    <div class="breadcrumb-item active">Tambah Baru</div>
                </div>
            </div>

            <div class="section-body">
                <form id="form-add-product" action="{{ url('admin/manage-master/products') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Informasi Dasar</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Produk</label>
                                                <input type="text" placeholder="Masukkan Nama Produk" class="form-control" name="name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Kode Produk</label>
                                                <input type="text" placeholder="Contoh: CRM" class="form-control" name="code" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Merk</label>
                                                <select class="form-control select2" name="merek_id" required>
                                                    <option value="">Pilih Merk</option>
                                                    @foreach ($merek as $m)
                                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Kategori</label>
                                                <select class="form-control select2 select-category" name="category_id" id="add-category" required>
                                                    <option value="">Pilih Kategori</option>
                                                    @foreach ($categories as $c)
                                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Sub Kategori</label>
                                                <select class="form-control select2" name="sub_category_id" id="add-sub-category">
                                                    <option value="">Pilih Sub Kategori</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tipe Produk</label>
                                                <select class="form-control select2" name="product_type_id" id="add-product-type" required>
                                                    <option value="">Pilih Tipe Produk</option>
                                                    @foreach($productTypes as $pt)
                                                        <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Product Tier</label>
                                                <select class="form-control select2 select-tier" name="product_tier_id" id="add-product-tier">
                                                    <option value="">Tanpa Tier (Manual)</option>
                                                    @foreach($productTiers as $tier)
                                                        <option value="{{ $tier->id }}" data-multiplier="{{ $tier->multiplier }}">{{ $tier->name }} (x{{ $tier->multiplier }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Min. Stock Alert</label>
                                                <input type="number" placeholder="Batas stok minimum untuk alert" class="form-control" name="min_stock_alert" required min="0" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="status" required>
                                                    <option value="Y">Aktif</option>
                                                    <option value="N">Non Aktif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-0">
                                        <label>Produk Bundling?</label>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="is_bundle" id="is_bundle_add" value="1">
                                            <label class="custom-control-label" for="is_bundle_add">Ya, Produk ini adalah Paket/Bundling</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="bundle-items-section-add" style="display: none;">
                                <div class="card-header">
                                    <h4>Komponen Bundling</h4>
                                    <div class="card-header-action">
                                        <button type="button" class="btn btn-success btn-sm" id="btn-add-bundle-item"><i class="fas fa-plus"></i> Tambah Komponen</button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm" id="table-bundle-items-add">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="pl-4">Produk Satuan</th>
                                                <th width="150px">Jumlah</th>
                                                <th width="80px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic items -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4>Varian & SKU (Stock Keeping Unit)</h4>
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
                                                    <th class="px-2" style="width: 25%;">SKU Code (Auto)</th>
                                                    <th class="px-2" style="width: 20%;">Harga HPP (Modal)</th>
                                                    <th class="px-2" style="width: 20%;">Harga Jual</th>
                                                    <th class="px-2" style="width: 10%;" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-2 align-middle">
                                                        <input type="text" name="variants[0][netto]" class="form-control px-2" placeholder="100" required>
                                                    </td>
                                                    <td class="px-2 align-middle">
                                                        <select name="variants[0][satuan]" class="form-control select2 satuan-input" required>
                                                            <option value="">Pilih Satuan</option>
                                                            @foreach($netto_attributes as $attr)
                                                                <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-2 align-middle">
                                                        <input type="text" name="variants[0][sku]" class="form-control bg-light font-weight-bold text-primary px-2" placeholder="SKU001" required readonly style="letter-spacing: 0.5px; font-size: 11px;">
                                                    </td>
                                                    <td class="px-2 align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <span class="mr-1 text-muted small font-weight-bold">Rp</span>
                                                            <input type="text" class="form-control rupiah-variant text-right px-2" placeholder="0" required>
                                                            <input type="hidden" name="variants[0][price_real]" class="raw-price-variant">
                                                        </div>
                                                    </td>
                                                    <td class="px-2 align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <span class="mr-1 text-muted small font-weight-bold">Rp</span>
                                                            <input type="text" class="form-control rupiah-variant text-right font-weight-bold px-2" placeholder="0" required>
                                                            <input type="hidden" name="variants[0][price]" class="raw-price-variant">
                                                            <input type="hidden" name="variants[0][price_tier]" value="0">
                                                        </div>
                                                    </td>
                                                    <td class="px-2 align-middle text-center">
                                                        <span class="text-muted small">-</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3 bg-light border-top">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle mr-1"></i> SKU digenerate otomatis berdasarkan: <strong>Merk-Kategori-Kode-Netto</strong>.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4>Foto Produk</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="foto" name="foto[]" multiple accept="image/*">
                                            <label class="custom-file-label" for="foto">Pilih gambar...</label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Bisa memilih lebih dari satu gambar sekaligus.</small>
                                        <div id="image-preview-add" class="mt-4 d-flex flex-wrap justify-content-start"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light border-0 shadow-none">
                                <div class="card-body d-flex justify-content-end align-items-center py-4">
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-back mr-3 px-5">Batal & Kembali</a>
                                    <button type="button" class="btn btn-save-custom px-5" id="btn-save-product">
                                        <i class="fas fa-save mr-2"></i> Simpan Produk Baru
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

    <script>
        const MEREK_DATA = @json($merek);
        const CATEGORY_DATA = @json($categories);
        const TIER_DATA = @json($productTiers);

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

        function generateSku(merekId, categoryId, productCode, netto) {
            const mCode = getMerekCode(merekId);
            const cCode = getCategoryCode(categoryId);
            const pCode = productCode || 'UNK';
            const nPart = (netto || '').replace(/[^0-9]/g, '');
            return `${mCode}-${cCode}-${pCode}-${nPart}`.toUpperCase();
        }

        function updateAllSkus() {
            const merekId = $('select[name="merek_id"]').val();
            const categoryId = $('#add-category').val();
            const productCode = $('input[name="code"]').val();

            $('#table-variants tbody tr').each(function() {
                const netto = $(this).find('input[name*="[netto]"]').val();
                const sku = generateSku(merekId, categoryId, productCode, netto);
                $(this).find('input[name*="[sku]"]').val(sku);
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

        $(document).ready(function() {
            // Select2 initialization
            $('.select2').select2({
                width: '100%'
            });

            // Update file input label
            $('#foto').on('change', function() {
                let fileName = Array.from(this.files).map(file => file.name).join(', ');
                $(this).next('.custom-file-label').text(fileName || 'Pilih gambar...');
                
                let preview = $('#image-preview-add');
                preview.empty();
                Array.from(this.files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        preview.append(`
                            <div class="img-preview">
                                <img src="${e.target.result}">
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });

            // SKU Generation Triggers
            $('input[name="name"], input[name="code"], select[name="merek_id"], #add-category').on('change input', function() {
                updateAllSkus();
            });

            $(document).on('input', 'input[name*="[netto]"]', function() {
                updateAllSkus();
            });

            // Category -> Sub Category
            $('#add-category').on('change', function() {
                const id = $(this).val();
                const target = $('#add-sub-category');
                target.empty().append('<option value="">Pilih Sub Kategori</option>');
                
                if (id) {
                    $.get("{{ url('admin/manage-master/categories/get-subs') }}", { id: id }, function(data) {
                        data.forEach(sub => {
                            target.append(`<option value="${sub.id}">${sub.name}</option>`);
                        });
                        target.trigger('change');
                    });
                }
            });

            // Bundling logic
            $('#is_bundle_add').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#bundle-items-section-add').slideDown();
                } else {
                    $('#bundle-items-section-add').slideUp();
                    $('#table-bundle-items-add tbody').empty();
                }
            });

            let bundleItemIndex = 0;
            $('#btn-add-bundle-item').on('click', function() {
                const row = `
                    <tr>
                        <td class="pl-4">
                            <select name="bundle_items[${bundleItemIndex}][product_id]" class="form-control select-product-bundle" required></select>
                        </td>
                        <td>
                            <input type="number" name="bundle_items[${bundleItemIndex}][quantity]" class="form-control" value="1" min="1" required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table-bundle-items-add tbody').append(row);
                
                const newSelect = $('#table-bundle-items-add tbody tr:last .select-product-bundle');
                newSelect.select2({
                    ajax: {
                        url: "{{ route('admin.products.search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { search: params.term };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(p => ({ id: p.id, text: p.name }))
                            };
                        }
                    }
                });
                bundleItemIndex++;
            });

            // Variant logic
            let variantIndex = 1;
            $('#btn-add-variant').on('click', function() {
                const row = `
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
                        <td class="px-2 align-middle"><input type="text" name="variants[${variantIndex}][sku]" class="form-control bg-light font-weight-bold text-primary px-2" placeholder="SKU001" required readonly style="letter-spacing: 0.5px; font-size: 11px;"></td>
                        <td class="px-2 align-middle">
                            <div class="d-flex align-items-center">
                                <span class="mr-1 text-muted small font-weight-bold">Rp</span>
                                <input type="text" class="form-control rupiah-variant text-right px-2" placeholder="0" required>
                                <input type="hidden" name="variants[${variantIndex}][price_real]" class="raw-price-variant">
                            </div>
                        </td>
                        <td class="px-2 align-middle">
                            <div class="d-flex align-items-center">
                                <span class="mr-1 text-muted small font-weight-bold">Rp</span>
                                <input type="text" class="form-control rupiah-variant text-right font-weight-bold px-2" placeholder="0" required>
                                <input type="hidden" name="variants[${variantIndex}][price]" class="raw-price-variant">
                                <input type="hidden" name="variants[${variantIndex}][price_tier]" value="0">
                            </div>
                        </td>
                        <td class="px-2 align-middle text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus Varian"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table-variants tbody').append(row);
                $('.select2-new').select2({ width: '100%' }).removeClass('select2-new');
                variantIndex++;
                updateAllSkus();
            });

            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Rupiah formatting
            $(document).on('input', '.rupiah-variant', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                $(this).next('.raw-price-variant').val(rawValue);
            });

            // Save Product
            $('#btn-save-product').on('click', function() {
                const form = $('#form-add-product')[0];
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Mohon lengkapi semua field yang wajib diisi (bertanda merah).',
                        confirmButtonColor: '#0d9488'
                    });
                    return;
                }

                const formData = new FormData(form);
                
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sedang Memproses...');

                $.ajax({
                    url: "{{ url('admin/manage-master/products') }}",
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
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Simpan Produk Baru');
                        
                        let msg = 'Terjadi kesalahan internal pada server.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: msg,
                            confirmButtonColor: '#0d9488'
                        });
                    }
                });
            });
        });
    </script>
@endsection
