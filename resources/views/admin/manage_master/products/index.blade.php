@extends('master')
@section('title', 'Data Produk')
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
            }

            .card-header {
                border-bottom: 1px solid #f1f5f9 !important;
                padding: 20px 25px !important;
            }

            .card-header h4 {
                color: #0d9488 !important;
                font-weight: 700 !important;
            }

            #products-table {
                font-size: 13px !important;
                border: none !important;
            }

            #products-table thead th {
                background-color: #f8fafc !important;
                color: #64748b !important;
                text-transform: none !important; /* Title Case */
                font-weight: 600 !important;
                padding: 15px 12px !important;
                border-top: none !important;
            }

            #products-table tbody td {
                padding: 15px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }

            /* Custom Badges */
            .badge-soft-success {
                background-color: #dcfce7;
                color: #15803d;
                padding: 6px 12px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 11px;
                display: inline-flex;
                align-items: center;
            }
            .badge-soft-success::before {
                content: "";
                width: 6px;
                height: 6px;
                background: #15803d;
                border-radius: 50%;
                margin-right: 6px;
            }

            .badge-soft-secondary {
                background-color: #f1f5f9;
                color: #475569;
                padding: 6px 12px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 11px;
            }

            /* Table Photo Styling */
            .img-thumbnail-custom {
                width: 48px;
                height: 48px;
                object-fit: cover;
                border-radius: 10px;
                border: 2px solid #f1f5f9;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                transition: transform 0.2s;
            }
            .img-thumbnail-custom:hover {
                transform: scale(1.1);
            }

            .hierarchy-text {
                color: #64748b;
                font-size: 11px;
            }
            .hierarchy-main {
                color: #1e293b;
                font-weight: 600;
                display: block;
            }

            /* Action Button Refinement */
            .btn-action-custom {
                border-radius: 8px !important;
                font-weight: 600 !important;
                padding: 5px 15px !important;
                background-color: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
                color: #475569 !important;
                box-shadow: none !important;
            }
            .btn-action-custom:hover {
                background-color: #f1f5f9 !important;
                color: #1e293b !important;
            }

            .btn-tambah-custom {
                background: var(--primary-gradient) !important;
                border: none !important;
                border-radius: 8px !important;
                padding: 10px 20px !important;
                font-weight: 700 !important;
                box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3) !important;
                transition: all 0.3s;
            }
            .btn-tambah-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(13, 148, 136, 0.4) !important;
            }

            /* DataTable search and entries refinement */
            .dataTables_wrapper .dataTables_filter input {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
                padding: 8px 12px !important;
            }
            .dataTables_wrapper .dataTables_length select {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
            }
        </style>
        <section class="section">
            <div class="section-header">
                <h1>Data Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Produk</div>
                </div>
            </div>

            <div class="section-body">
                <!-- Redundant titles removed to improve focus and vertical space -->
                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi kesalahan!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Seluruh Produk</h4>
                        <div class="card-header-form">
                            <button type="button" class="btn btn-primary btn-tambah-custom" data-toggle="modal" data-target="#addModal">
                                <i class="fas fa-plus mr-2"></i> Tambah Produk Baru
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5" id="products-table">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Merk</th>
                                    <th>Produk</th>
                                    <th>Kategori / Tipe</th>
                                    <th>SKUs</th>
                                    <th>Status</th>
                                    <th class="text-center">Foto</th>
                                    <th width="10px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Tambah Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ url('admin/manage-master/products') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" placeholder="Masukkan Nama Produk" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Produk</label>
                                    <input type="text" placeholder="Contoh: CRM" class="form-control" name="code" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Merk</label>
                                    <select class="form-control" name="merek_id" required>
                                        <option value="">Pilih Merk</option>
                                        @foreach ($merek as $m)
                                            @php /** @var \App\Models\Merek $m */ @endphp
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control select-category" name="category_id" id="add-category" required>
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
                                    <select class="form-control" name="sub_category_id" id="add-sub-category">
                                        <option value="">Pilih Sub Kategori</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipe Produk</label>
                                    <select class="form-control" name="product_type_id" id="add-product-type" required>
                                        <option value="">Pilih Tipe Produk</option>
                                        @foreach($productTypes as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Tier</label>
                                    <select class="form-control select-tier" name="product_tier_id" id="add-product-tier">
                                        <option value="">Tanpa Tier (Manual)</option>
                                        @foreach($productTiers as $tier)
                                            <option value="{{ $tier->id }}" data-multiplier="{{ $tier->multiplier }}">{{ $tier->name }} (x{{ $tier->multiplier }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Min. Stock Alert</label>
                                    <input type="number" placeholder="Batas stok minimum untuk alert" class="form-control" name="min_stock_alert" required min="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Produk Bundling?</label>
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" name="is_bundle" id="is_bundle_add" value="1">
                                        <label class="custom-control-label" for="is_bundle_add">Ya, Bundling</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bundling Items Section -->
                        <div id="bundle-items-section-add" class="card card-primary d-none">
                            <div class="card-header">
                                <h4>Daftar Komponen Bundling</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-success btn-sm" id="btn-add-bundle-item"><i class="fas fa-plus"></i> Tambah Komponen</button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm" id="table-bundle-items-add">
                                    <thead>
                                        <tr>
                                            <th>Produk Satuan</th>
                                            <th width="120px">Jumlah</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Dynamic items -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="d-block">Varian Produk (SKU) <button type="button" class="btn btn-sm btn-success float-right mb-2" id="btn-add-variant"><i class="fas fa-plus"></i> Tambah Varian</button></label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="table-variants">
                                    <thead>
                                        <tr>
                                            <th>Netto</th>
                                            <th>Satuan</th>
                                            <th>SKU Code</th>
                                            <th class="d-none">Harga Modal (Rp)</th>
                                            <th class="d-none">Harga Kategori (Rp)</th>
                                            <th>Harga Jual (Rp)</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="variants[0][netto]" class="form-control form-control-sm" placeholder="100" required></td>
                                            <td>
                                                <select name="variants[0][satuan]" class="form-control form-control-sm satuan-input" style="height: calc(1.5em + 0.5rem + 2px);" required>
                                                    <option value="">Pilih Satuan</option>
                                                    @foreach($netto_attributes as $attr)
                                                        <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="variants[0][sku]" class="form-control form-control-sm" placeholder="SKU001" required></td>
                                            <td class="d-none">
                                                <input type="hidden" name="variants[0][price_real]" value="0">
                                            </td>
                                            <td class="d-none">
                                                <input type="hidden" name="variants[0][price_tier]" value="0">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm rupiah-variant" placeholder="Rp 0" required>
                                                <input type="hidden" name="variants[0][price]" class="raw-price-variant">
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" required>
                                <option value="Y">Aktif</option>
                                <option value="N">Non Aktif</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gambar Produk <small>(optional, multiple)</small></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="foto" name="foto[]" multiple accept="image/*">
                                <label class="custom-file-label" for="foto">Pilih gambar...</label>
                            </div>
                            <div id="image-preview-add" class="mt-3 d-flex flex-wrap"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btn-save-product">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ url('admin/manage-master/products/update') }}" method="POST" class="needs-validation" novalidate="" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="deleted_photos" id="deleted_photos">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" placeholder="Masukkan Nama Produk" class="form-control" name="name" id="name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode Produk</label>
                                    <input type="text" placeholder="Contoh: CRM" class="form-control" name="code" id="edit_code" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Merk</label>
                                    <select class="form-control" name="merek_id" id="merek_id" required>
                                        <option value="">Pilih Merk</option>
                                        @foreach ($merek as $m)
                                            @php /** @var \App\Models\Merek $m */ @endphp
                                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select class="form-control select-category" name="category_id" id="edit-category" required>
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
                                    <select class="form-control" name="sub_category_id" id="edit-sub-category">
                                        <option value="">Pilih Sub Kategori</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipe Produk</label>
                                    <select class="form-control" name="product_type_id" id="edit-product-type" required>
                                        <option value="">Pilih Tipe Produk</option>
                                        @foreach($productTypes as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Tier</label>
                                    <select class="form-control select-tier" name="product_tier_id" id="edit-product-tier">
                                        <option value="">Tanpa Tier (Manual)</option>
                                        @foreach($productTiers as $tier)
                                            <option value="{{ $tier->id }}" data-multiplier="{{ $tier->multiplier }}">{{ $tier->name }} (x{{ $tier->multiplier }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Min. Stock Alert</label>
                                    <input type="number" placeholder="Batas stok minimum untuk alert" class="form-control" name="min_stock_alert" id="min_stock_alert" required min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Produk Bundling?</label>
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" name="is_bundle" id="is_bundle_edit" value="1">
                                        <label class="custom-control-label" for="is_bundle_edit">Ya, Bundling</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bundling Items Section (Edit) -->
                        <div id="bundle-items-section-edit" class="card card-primary d-none">
                            <div class="card-header">
                                <h4>Daftar Komponen Bundling</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-success btn-sm" id="btn-add-bundle-item-edit"><i class="fas fa-plus"></i> Tambah Komponen</button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm" id="table-bundle-items-edit">
                                    <thead>
                                        <tr>
                                            <th>Produk Satuan</th>
                                            <th width="120px">Jumlah</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Dynamic items -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="d-block">Varian Produk (SKU) <button type="button" class="btn btn-sm btn-success float-right mb-2" id="btn-add-variant-edit"><i class="fas fa-plus"></i> Tambah Varian</button></label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="table-variants-edit">
                                    <thead>
                                        <tr>
                                            <th>Netto</th>
                                            <th>Satuan</th>
                                            <th>SKU Code</th>
                                            <th class="d-none">Harga Modal (Rp)</th>
                                            <th class="d-none">Harga Kategori (Rp)</th>
                                            <th>Harga Jual (Rp)</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="Y">Aktif</option>
                                <option value="N">Non Aktif</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gambar Produk <small>(optional, multiple)</small></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="foto_update" name="foto[]" multiple accept="image/*">
                                <label class="custom-file-label" for="foto_update">Pilih gambar...</label>
                            </div>
                            <div id="image-preview-update" class="mt-3 d-flex flex-wrap"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btn-update-product">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pricing Details Modal -->
    <div class="modal fade" id="pricingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rincian Harga - <span id="pricing-product-name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="pricing-content">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        function updateAllSkus(containerType) { // 'add' or 'edit'
            const merekId = containerType === 'add' ? $('select[name="merek_id"]').first().val() : $('#merek_id').val();
            const categoryId = containerType === 'add' ? $('#add-category').val() : $('#edit-category').val();
            const productCode = containerType === 'add' ? $('input[name="code"]').first().val() : $('#edit_code').val();
            const tableId = containerType === 'add' ? '#table-variants' : '#table-variants-edit';

            $(`${tableId} tbody tr`).each(function() {
                const netto = $(this).find('input[name*="[netto]"]').val();
                const sku = generateSku(merekId, categoryId, productCode, netto);
                $(this).find('input[name*="[sku]"]').val(sku);
            });
        }

        function calculateSellingPrice(row) {
            const tierMultiplier = parseFloat(row.closest('form').find('.select-tier option:selected').data('multiplier')) || 1.0;
            const modalPrice = parseFloat(row.find('.raw-modal-variant').val()) || 0;
            const targetPrice = Math.round(modalPrice * tierMultiplier);
            
            row.find('.raw-tier-variant').val(targetPrice);
            row.find('.rupiah-tier').val(formatRupiah(targetPrice));
        }

        function recalculateAllPrices(formSelector) {
            $(formSelector + ' #table-variants tbody tr, ' + formSelector + ' #table-variants-edit tbody tr').each(function() {
                calculateSellingPrice($(this));
            });
        }

        // Rupiah formatting function
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
            return 'Rp ' + rupiah;
        }

        function getRawNumber(rupiah) {
            return rupiah.replace(/[^0-9,-]/g, '').replace(',', '.');
        }

        $(document).ready(function() {
            // Update file input label with selected file names
            function updateFileLabel(input, label) {
                let fileName = Array.from(input.files).map(file => file.name).join(', ');
                $(label).text(fileName || 'Pilih gambar...');
            }

            $('#foto').on('change', function() {
                updateFileLabel(this, '#foto + .custom-file-label');
                let preview = $('#image-preview-add');
                preview.empty();
                Array.from(this.files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        preview.append(`
                            <div class="img-preview mr-2 mb-2 position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px;">
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });

            $('#foto_update').on('change', function() {
                updateFileLabel(this, '#foto_update + .custom-file-label');
                let preview = $('#image-preview-update');
                Array.from(this.files).forEach(file => {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        preview.append(`
                            <div class="img-preview mr-2 mb-2 position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px;">
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });

            $('.rupiah').on('input', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                if ($(this).attr('id') === 'price') {
                    $('#raw_price_update').val(rawValue);
                } else {
                    $('#raw_price').val(rawValue);
                }
            });

            $('#products-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/manage-master/products/all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'merek_name', name: 'merek_name' },
                    { 
                        data: 'name', 
                        name: 'name',
                        render: function(data, type, row) {
                            return `<span class="hierarchy-main">${data}</span><small class="text-muted">${row.code || ''}</small>`;
                        }
                    },
                    { 
                        data: 'hierarchy', 
                        name: 'hierarchy',
                        render: function(data, type, row) {
                            return `<span class="hierarchy-text">${data}</span>`;
                        }
                    },
                    { data: 'variant_count', name: 'variant_count' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data, type, row) {
                            if (data === 'Aktif') {
                                return `<span class="badge-soft-success">Aktif</span>`;
                            }
                            return `<span class="badge-soft-secondary">${data}</span>`;
                        }
                    },
                    { 
                        data: 'photos_preview', 
                        name: 'photos_preview',
                        className: 'text-center',
                        render: function(data, type, row) {
                            // Extract src from the string returned by controller
                            const srcMatch = data.match(/src="([^"]+)"/);
                            const src = srcMatch ? srcMatch[1] : '';
                            return `<img src="${src}" class="img-thumbnail-custom">`;
                        }
                    },
                    { data: 'action', name: 'action' }
                ]
            });

            // Cascading selects for hierarchy
            function loadSubCategories(categoryId, targetSelect, selectedId = null) {
                if (!categoryId) {
                    $(targetSelect).html('<option value="">Pilih Sub Kategori</option>');
                    return;
                }
                
                $.get("{{ url('admin/manage-master/categories/get-subs') }}", { id: categoryId }, function(data) {
                    let options = '<option value="">Pilih Sub Kategori</option>';
                    data.forEach(sub => {
                        options += `<option value="${sub.id}" ${selectedId == sub.id ? 'selected' : ''}>${sub.name}</option>`;
                    });
                    $(targetSelect).html(options);
                });
            }

            $('#add-category').on('change', function() {
                loadSubCategories($(this).val(), '#add-sub-category');
                updateAllSkus('add');
            });

            $('#edit-category').on('change', function() {
                loadSubCategories($(this).val(), '#edit-sub-category');
                updateAllSkus('edit');
            });

            $('select[name="merek_id"]').first().on('change', function() { updateAllSkus('add'); });
            $('#merek_id').on('change', function() { updateAllSkus('edit'); });
            
            $('input[name="code"]').first().on('input', function() { updateAllSkus('add'); });
            $('#edit_code').on('input', function() { updateAllSkus('edit'); });

            $(document).on('input', 'input[name*="[netto]"]', function() {
                const type = $(this).closest('table').attr('id') === 'table-variants' ? 'add' : 'edit';
                updateAllSkus(type);
            });

            // Variant Grid Management
            let variantIndex = 1;
            $('#btn-add-variant').on('click', function() {
                let html = `
                    <tr>
                        <td><input type="text" name="variants[${variantIndex}][netto]" class="form-control form-control-sm" placeholder="100" required></td>
                        <td>
                            <select name="variants[${variantIndex}][satuan]" class="form-control form-control-sm satuan-input" style="height: calc(1.5em + 0.5rem + 2px);" required>
                                <option value="">Pilih Satuan</option>
                                @foreach($netto_attributes as $attr)
                                    <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="variants[${variantIndex}][sku]" class="form-control form-control-sm" placeholder="SKU${variantIndex+1}" required></td>
                        <td class="d-none">
                            <input type="hidden" name="variants[${variantIndex}][price_real]" value="0">
                        </td>
                        <td class="d-none">
                            <input type="hidden" name="variants[${variantIndex}][price_tier]" value="0">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm rupiah-variant" placeholder="Rp 0" required>
                            <input type="hidden" name="variants[${variantIndex}][price]" class="raw-price-variant">
                        </td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-remove-variant"><i class="fas fa-times"></i></button></td>
                    </tr>
                `;
                $('#table-variants tbody').append(html);
                variantIndex++;
                updateAllSkus('add');
            });

            $(document).on('click', '.btn-remove-variant', function() {
                $(this).closest('tr').remove();
            });

            $(document).on('input', '.rupiah-variant', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                $(this).siblings('.raw-price-variant').val(rawValue);
            });

            $(document).on('input', '.rupiah-modal', function() {
                let rawValue = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(formatRupiah(rawValue));
                $(this).siblings('.raw-modal-variant').val(rawValue);
                calculateSellingPrice($(this).closest('tr'));
            });

            $('.select-tier').on('change', function() {
                const formId = $(this).closest('modal').attr('id') || $(this).closest('form').closest('div.modal').attr('id');
                recalculateAllPrices('#' + formId);
            });

            // AJAX Form Submission for Create
            $('#btn-save-product').on('click', function() {
                let form = $('#addModal form')[0];
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return;
                }
                
                let formData = new FormData(form);
                $.ajax({
                    url: "{{ url('admin/manage-master/products') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    success: function(res) {
                        $.LoadingOverlay("hide");
                        swal(res.message, { icon: "success" }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(err) {
                        $.LoadingOverlay("hide");
                        swal(err.responseJSON.message || "Something went wrong", { icon: "error" });
                    }
                });
            });

            // Edit button handler
            let variantIndexEdit = 0;
            $('#products-table').on('click', '.edit[data-id]', function(e) {
                e.preventDefault();
                $.ajax({
                    data: {
                        'id': $(this).data('id'),
                        '_token': "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    url: "{{ url('admin/manage-master/products/get') }}",
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    complete: function() {
                        $.LoadingOverlay("hide");
                    },
                    success: function(data) {
                        if (data.product_id) {
                            row.find('.product-id-hidden').val(data.product_id);
                            row.find('.description-input').val(data.description || ''); 
                            row.find('.price-input').val(data.price || 0);
                        }
                        $('#id').val(data.id);
                        $('#name').val(data.name);
                        $('#edit_code').val(data.code);
                        $('#merek_id').val(data.merek_id);
                        $('#edit-product-tier').val(data.product_tier_id);
                        $('#min_stock_alert').val(data.min_stock_alert);
                        $('#status').val(data.status);
                        
                        // Populate Hierarchy
                        $('#edit-category').val(data.category_id);
                        loadSubCategories(data.category_id, '#edit-sub-category', data.sub_category_id);
                        $('#edit-product-type').val(data.product_type_id);

                        // Populate Variants
                        $('#table-variants-edit tbody').empty();
                        variantIndexEdit = 0;
                        data.variants.forEach((v, index) => {
                            let netVal = v.netto ? v.netto.netto_value : '';
                            let satuanVal = v.netto ? v.netto.satuan : '';
                            let html = `
                                <tr>
                                    <td><input type="text" name="variants[${index}][netto]" value="${netVal}" class="form-control form-control-sm" required></td>
                                    <td>
                                        <select name="variants[${index}][satuan]" class="form-control form-control-sm satuan-select-edit" style="height: calc(1.5em + 0.5rem + 2px);" data-satuan="${satuanVal}" required>
                                            <option value="">Pilih Satuan</option>
                                            @foreach($netto_attributes as $attr)
                                                <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="variants[${index}][sku]" value="${v.sku_code}" class="form-control form-control-sm" required></td>
                                    <td class="d-none">
                                        <input type="hidden" name="variants[${index}][price_real]" value="${v.price_real || 0}">
                                    </td>
                                    <td class="d-none">
                                        <input type="hidden" name="variants[${index}][price_tier]" value="${v.price_tier || 0}">
                                    </td>
                                    <td>
                                        <input type="text" value="${formatRupiah(v.price)}" class="form-control form-control-sm rupiah-variant" required>
                                        <input type="hidden" name="variants[${index}][price]" value="${v.price}" class="raw-price-variant">
                                    </td>
                                    <td>${index > 0 ? '<button type="button" class="btn btn-sm btn-danger btn-remove-variant"><i class="fas fa-times"></i></button>' : ''}</td>
                                </tr>
                            `;
                            $('#table-variants-edit tbody').append(html);
                            variantIndexEdit = index + 1;
                        });
                        
                        // Set satuan dropdown values
                        $('.satuan-select-edit').each(function() {
                            let satuan = $(this).data('satuan');
                            if (satuan) {
                                $(this).val(satuan);
                            }
                        });

                        // Bundling Logic in Edit
                        if (data.is_bundle) {
                            $('#is_bundle_edit').prop('checked', true).trigger('change');
                            $('#table-bundle-items-edit tbody').empty();
                            if (data.bundle_items && data.bundle_items.length > 0) {
                                data.bundle_items.forEach(item => {
                                    addBundleRow('#table-bundle-items-edit', {
                                        product_id: item.product_id,
                                        product_name: (item.product && item.product.merek ? item.product.merek.name + ' ' : '') + (item.product ? item.product.name : 'Produk tidak ditemukan'),
                                        quantity: item.quantity
                                    });
                                });
                            }
                        } else {
                            $('#is_bundle_edit').prop('checked', false).trigger('change');
                            $('#table-bundle-items-edit tbody').empty();
                        }

                        $('#deleted_photos').val('');
                        let preview = $('#image-preview-update');
                        preview.empty();
                        data.photos.forEach(photo => {
                            preview.append(`
                                <div class="img-preview mr-2 mb-2 position-relative" data-id="${photo.id}">
                                    <img src="{{ asset('') }}${photo.foto}" class="img-thumbnail" style="width: 100px; height: 100px;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 0; right: 0;" onclick="removePhoto(${photo.id})">×</button>
                                </div>
                            `);
                        });
                        $('#foto_update + .custom-file-label').text('Pilih gambar...');
                        updateAllSkus('edit');
                        $('#updateModal').modal('show');
                    }
                });
            });

            // Add Variant in Edit Modal
            $('#btn-add-variant-edit').on('click', function() {
                let html = `
                    <tr>
                        <td><input type="text" name="variants[${variantIndexEdit}][netto]" class="form-control form-control-sm" placeholder="100" required></td>
                        <td>
                            <select name="variants[${variantIndexEdit}][satuan]" class="form-control form-control-sm" style="height: calc(1.5em + 0.5rem + 2px);" required>
                                <option value="">Pilih Satuan</option>
                                @foreach($netto_attributes as $attr)
                                    <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="variants[${variantIndexEdit}][sku]" class="form-control form-control-sm" required></td>
                        <td class="d-none">
                             <input type="hidden" name="variants[${variantIndexEdit}][price_real]" value="0">
                        </td>
                        <td class="d-none">
                             <input type="hidden" name="variants[${variantIndexEdit}][price_tier]" value="0">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm rupiah-variant" placeholder="Rp 0" required>
                            <input type="hidden" name="variants[${variantIndexEdit}][price]" class="raw-price-variant">
                        </td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-remove-variant"><i class="fas fa-times"></i></button></td>
                    </tr>
                `;
                $('#table-variants-edit tbody').append(html);
                variantIndexEdit++;
                updateAllSkus('edit');
            });

            // AJAX Form Submission for Update
            $('#btn-update-product').on('click', function() {
                let form = $('#updateModal form')[0];
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return;
                }
                
                let formData = new FormData(form);
                $.ajax({
                    url: "{{ url('admin/manage-master/products/update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    success: function(res) {
                        $.LoadingOverlay("hide");
                        swal(res.message, { icon: "success" }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(err) {
                        $.LoadingOverlay("hide");
                        swal(err.responseJSON.message || "Something went wrong", { icon: "error" });
                    }
                });
            });

            // Cascading selects for edit removed as hierarchy is flat
            $('#products-table').on('click', '.hapus[data-id]', function(e) {
                e.preventDefault();
                swal({
                    title: "Hapus Produk?",
                    text: "Data Produk ini akan dihapus secara permanen!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            data: {
                                'id': $(this).data('id'),
                                '_token': "{{ csrf_token() }}"
                            },
                            type: 'DELETE',
                            url: "{{ url('admin/manage-master/products') }}",
                            beforeSend: function() {
                                $.LoadingOverlay("show", {
                                    image: "",
                                    fontawesome: "fa fa-cog fa-spin"
                                });
                            },
                            complete: function() {
                                $.LoadingOverlay("hide");
                            },
                            success: function(data) {
                                swal(data.message).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(err) {
                                alert('Error: ' + err.responseText);
                                console.log(err);
                            }
                        });
                    }
                });
            });

            // --- BUNDLING JS LOGIC ---
            
            // Toggle Section for Add Modal
            $('#is_bundle_add').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#bundle-items-section-add').removeClass('d-none');
                } else {
                    $('#bundle-items-section-add').addClass('d-none');
                    $('#table-bundle-items-add tbody').empty();
                }
            });

            // Toggle Section for Edit Modal
            $('#is_bundle_edit').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#bundle-items-section-edit').removeClass('d-none');
                } else {
                    $('#bundle-items-section-edit').addClass('d-none');
                    $('#table-bundle-items-edit tbody').empty();
                }
            });

            let bundleItemIndex = 0;
            function addBundleRow(tableSelector, data = null) {
                const namePrefix = 'bundle_items';
                const html = `
                    <tr>
                        <td>
                            <select name="${namePrefix}[${bundleItemIndex}][product_id]" class="form-control select2-product-bundle" required>
                                ${data ? `<option value="${data.product_id}" selected>${data.product_name}</option>` : '<option value="">Cari Produk...</option>'}
                            </select>
                        </td>
                        <td>
                            <input type="number" name="${namePrefix}[${bundleItemIndex}][quantity]" class="form-control" value="${data ? data.quantity : 1}" min="1" step="0.01" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm btn-remove-bundle-item"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $(tableSelector + ' tbody').append(html);
                
                // Initialize Select2 for the new row
                const newRow = $(tableSelector + ' tbody tr:last');
                initProductSelect2(newRow.find('.select2-product-bundle'));
                
                bundleItemIndex++;
            }

            function initProductSelect2(element) {
                element.select2({
                    dropdownParent: element.closest('.modal'),
                    ajax: {
                        url: "{{ url('admin/manage-master/products/search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(item => ({
                                    id: item.id,
                                    text: (item.merek ? item.merek.name + ' ' : '') + item.name
                                }))
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    placeholder: 'Cari Produk Satuan...'
                });
            }

            $('#btn-add-bundle-item').on('click', function() {
                addBundleRow('#table-bundle-items-add');
            });

            $('#btn-add-bundle-item-edit').on('click', function() {
                addBundleRow('#table-bundle-items-edit');
            });

            $(document).on('click', '.btn-remove-bundle-item', function() {
                $(this).closest('tr').remove();
            });

            // --- END BUNDLING JS LOGIC ---

            // Handle edit from query param
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('edit');
            if (editId) {
                // Wait for DT or some delay to ensure page is ready
                setTimeout(() => {
                    $(`.edit[data-id="${editId}"]`).click();
                }, 500);
            }
        });

        function removePhoto(photoId) {
            let deletedPhotos = $('#deleted_photos').val();
            deletedPhotos = deletedPhotos ? deletedPhotos + ',' + photoId : photoId;
            $('#deleted_photos').val(deletedPhotos);
            $(`#image-preview-update .img-preview[data-id="${photoId}"]`).remove();
        }
    </script>
@endsection