@extends('master')

@section('title', 'Terima Barang - ')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .section-header {
        background: #fff;
        padding: 20px 25px !important;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 25px !important;
        border-left: 5px solid #0d9488;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .section-header h1 { font-weight: 800 !important; color: #1e293b !important; margin-bottom: 0; }

    .card { border-radius: 20px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.03); }
    .card-header { background-color: transparent !important; padding: 15px 25px !important; border-bottom: 1px solid #f1f5f9 !important; }
    .card-header h4 { color: #1e293b; font-weight: 800; font-size: 16px; margin-bottom: 0; }

    .form-group label { font-weight: 700; color: #475569; font-size: 13px; margin-bottom: 8px; }

    #table-items { table-layout: fixed !important; }
    #table-items thead th {
        background-color: #f8fafc; color: #64748b; font-weight: 700;
        text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;
        border-top: none; padding: 10px 12px !important;
    }
    #table-items th, #table-items td {
        vertical-align: middle !important; padding: 8px 10px !important;
        word-wrap: break-word !important; overflow-wrap: break-word !important;
    }
    #table-items .form-control { height: 34px !important; padding: 4px 8px !important; font-size: 13px !important; border-radius: 8px; }
    #table-items .form-control[readonly] { background-color: #f1f5f9; cursor: default; color: #64748b; }

    .select2-container .select2-selection--single { min-height: 34px !important; border-radius: 8px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { padding: 5px 10px !important; font-size: 13px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px !important; }

    .btn-save-gr {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        border: none; padding: 12px 30px; border-radius: 12px;
        font-weight: 700; transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        color: white;
    }
    .btn-save-gr:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3); color: white; }

    /* PO Info Banner */
    #po-info-banner {
        display: none;
        background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%);
        border: 1.5px solid #0d9488;
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 15px;
    }
    #po-info-banner .po-badge { background: #0d9488; color: white; border-radius: 8px; padding: 4px 12px; font-weight: 800; font-size: 13px; }

    /* Row from PO vs manual */
    .row-from-po { background: #f0fdfa !important; }
    .row-from-po td:first-child::before {
        content: 'PO';
        display: inline-block;
        background: #0d9488;
        color: white;
        font-size: 9px;
        font-weight: 800;
        border-radius: 4px;
        padding: 1px 5px;
        margin-right: 4px;
        vertical-align: middle;
    }

    .badge-po { background: #0d9488; color: white; font-size: 10px; border-radius: 6px; padding: 2px 7px; font-weight: 700; }
    .badge-manual { background: #6366f1; color: white; font-size: 10px; border-radius: 6px; padding: 2px 7px; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.purchasing.goods_receipts.index') }}" class="btn btn-icon mr-3"><i class="fas fa-arrow-left"></i></a>
                <h1>Penerimaan Barang Baru</h1>
            </div>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.goods_receipts.index') }}">Penerimaan Barang</a></div>
                <div class="breadcrumb-item active">Buat</div>
            </div>
        </div>

        <form id="form-gr" action="{{ route('admin.purchasing.goods_receipts.store') }}" method="POST" novalidate>
            @csrf
            <div class="section-body">
                <div class="row">
                    <div class="col-12">

                        {{-- Card: Informasi Penerimaan --}}
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-clipboard-list text-teal mr-2"></i> Informasi Penerimaan</h4>
                            </div>
                            <div class="card-body">

                                {{-- PO Info Banner (tampil saat PO dipilih) --}}
                                <div id="po-info-banner">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-link text-teal mr-2"></i>
                                        <strong class="mr-3" style="color:#0d9488;">Terhubung ke PO:</strong>
                                        <span class="po-badge" id="po-badge-number">-</span>
                                        <span class="ml-3 text-muted small" id="po-banner-supplier"></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger ml-auto" id="btn-clear-po">
                                            <i class="fas fa-unlink mr-1"></i> Lepas PO
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>No. Penerimaan Barang (Auto)</label>
                                            <input type="text" value="{{ $gr_number }}" class="form-control" readonly style="background:#f1f5f9; font-family:monospace; font-weight:800;">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>No. Surat Jalan Supplier <span class="text-danger">*</span></label>
                                            <input type="text" name="delivery_note_number" id="delivery_note_number" class="form-control" placeholder="Contoh: SJ/SUP/2026/0012" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Purchase Order <span class="text-muted">(Opsional)</span></label>
                                            <select name="purchase_order_id" id="purchase_order_id" class="form-control select2-po">
                                                <option value="">-- Tanpa PO (Input Manual) --</option>
                                                @foreach($pos as $po)
                                                    <option value="{{ $po->id }}" data-po-number="{{ $po->po_number }}" data-supplier="{{ $po->supplier->name ?? '' }}">
                                                        {{ $po->po_number }} — {{ $po->supplier->name ?? '-' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Pilih PO untuk auto-isi data supplier, gudang & item</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Supplier <span class="text-danger">*</span></label>
                                            <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                                <option value="">Pilih Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Gudang Penerimaan <span class="text-danger">*</span></label>
                                            <select name="warehouse_id" id="warehouse_id" class="form-control select2" required>
                                                <option value="">Pilih Gudang</option>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Tgl. SJ Supplier <span class="text-danger">*</span></label>
                                                    <input type="date" name="delivery_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Tgl. Diterima <span class="text-danger">*</span></label>
                                                    <input type="date" name="received_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label>Catatan Penerimaan</label>
                                    <textarea name="notes" class="form-control" rows="1" placeholder="Catatan tambahan..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Item Barang --}}
                        <div class="card mt-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4><i class="fas fa-boxes text-teal mr-2"></i> Item Barang Diterima</h4>
                                <div id="item-action-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-manual">
                                        <i class="fas fa-search mr-1"></i> Tambah Produk (Cari)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btn-add-freetext">
                                        <i class="fas fa-pen mr-1"></i> Tambah Item Bebas
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="table-items">
                                        <thead>
                                            <tr>
                                                <th style="width:40px;" class="text-center">#</th>
                                                <th style="min-width:260px;">Nama Produk</th>
                                                <th style="width:120px;">No. Batch</th>
                                                <th style="width:140px;">Harga Beli</th>
                                                <th style="width:110px;" class="text-center">Qty PO</th>
                                                <th style="width:110px;" class="text-center">Qty Terima <span class="text-danger">*</span></th>
                                                <th>Catatan</th>
                                                <th style="width:40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-items">
                                            <tr id="empty-row">
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                                    Pilih PO untuk auto-isi item, atau tambah item manual di bawah
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-whitesmoke text-right">
                                <a href="{{ route('admin.purchasing.goods_receipts.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="submit" class="btn btn-save-gr">
                                    <i class="fas fa-check mr-2"></i> Konfirmasi Penerimaan Barang
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let rowCount = 0;

    function formatNum(val) {
        let n = parseFloat(val);
        if (isNaN(n)) return '';
        return n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function parseNum(val) {
        if (!val) return 0;
        return parseFloat(val.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
    }

    function hideEmptyRow() {
        $('#empty-row').hide();
    }
    function showEmptyRowIfNeeded() {
        if ($('#tbody-items tr:visible').not('#empty-row').length === 0) {
            $('#empty-row').show();
        }
    }

    $(document).ready(function() {
        // Init Select2
        $('.select2').select2({ width: '100%' });
        $('.select2-po').select2({
            width: '100%',
            placeholder: '-- Tanpa PO (Input Manual) --',
            allowClear: true
        });

        // ============================
        // PO Selection Handler
        // ============================
        $('#purchase_order_id').on('change', function() {
            let poId = $(this).val();
            if (poId) {
                let $opt = $(this).find(':selected');
                let poNumber = $opt.data('po-number') || $opt.text();
                let supplierName = $opt.data('supplier') || '';

                // Tampilkan banner PO
                $('#po-badge-number').text(poNumber);
                $('#po-banner-supplier').text(supplierName ? '· ' + supplierName : '');
                $('#po-info-banner').slideDown(200);

                loadPoItems(poId);
            } else {
                clearPo();
            }
        });

        // Tombol Lepas PO
        $('#btn-clear-po').on('click', function() {
            $('#purchase_order_id').val('').trigger('change');
        });

        // ============================
        // Tambah Item Buttons
        // ============================
        $('#btn-add-manual').on('click', function() { addManualRow(); });
        $('#btn-add-freetext').on('click', function() { addFreeTextRow(); });

        // ============================
        // Remove row
        // ============================
        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
            updateIndex();
            showEmptyRowIfNeeded();
        });

        // Format number on blur
        $(document).on('blur', '.qty-received-input, .buy-price-input', function() {
            let val = parseNum($(this).val());
            $(this).val(formatNum(val));
        });

        // ============================
        // Form Submit
        // ============================
        $('#form-gr').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');

            // Collect visible (real) rows
            let realRows = $('#tbody-items tr:visible').not('#empty-row');

            if (realRows.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Harap tambahkan minimal 1 item barang.' });
                return;
            }

            let errors = [];
            if (!$('[name="delivery_note_number"]').val().trim()) errors.push('No. Surat Jalan Supplier wajib diisi');
            if (!$('[name="supplier_id"]').val()) errors.push('Supplier wajib dipilih');
            if (!$('[name="warehouse_id"]').val()) errors.push('Gudang Penerimaan wajib dipilih');

            let hasEmptyProduct = false, hasInvalidQty = false;
            realRows.each(function() {
                let pname = $(this).find('[name$="[product_name]"]').val();
                if (!pname || !pname.trim()) hasEmptyProduct = true;
                let qty = parseNum($(this).find('[name$="[qty_received]"]').val());
                if (qty <= 0) hasInvalidQty = true;
            });

            if (hasEmptyProduct) errors.push('Setiap item harus memiliki nama produk');
            if (hasInvalidQty) errors.push('Qty Terima setiap item harus lebih dari 0');

            if (errors.length > 0) {
                Swal.fire({
                    icon: 'warning', title: 'Data Belum Lengkap',
                    html: '<div style="text-align:left">' + errors.map(e => '<div style="padding:4px 0">• ' + e + '</div>').join('') + '</div>'
                });
                return;
            }

            btn.addClass('btn-progress').attr('disabled', true);
            $.LoadingOverlay("show");

            let formData = form.serializeArray();
            formData.forEach(item => {
                if (item.name.includes('qty_received') || item.name.includes('buy_price')) {
                    item.value = parseNum(item.value);
                }
            });

            $.ajax({
                url: "{{ route('admin.purchasing.goods_receipts.store') }}",
                method: 'POST',
                data: formData,
                success: function(res) {
                    $.LoadingOverlay("hide");
                    btn.removeClass('btn-progress').attr('disabled', false);
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => { window.location.href = res.redirect; });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function(err) {
                    $.LoadingOverlay("hide");
                    btn.removeClass('btn-progress').attr('disabled', false);
                    let msg = err.responseJSON?.message || 'Terjadi kesalahan pada server';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });
    });

    // ============================
    // Load PO Items via AJAX
    // ============================
    function loadPoItems(poId) {
        $.LoadingOverlay("show");
        $.ajax({
            url: "{{ route('admin.purchasing.goods_receipts.get_po_items') }}",
            data: { po_id: poId },
            success: function(res) {
                $.LoadingOverlay("hide");

                // Set supplier
                if (res.supplier_id) {
                    $('#supplier_id').val(res.supplier_id).trigger('change');
                }

                // Set warehouse
                if (res.warehouse_id) {
                    $('#warehouse_id').val(res.warehouse_id).trigger('change');
                }

                // Clear tbody & remove only non-PO rows (keep fresh)
                $('#tbody-items tr').not('#empty-row').remove();
                hideEmptyRow();

                if (res.items && res.items.length > 0) {
                    res.items.forEach(function(item, index) {
                        let rowIndex = rowCount++;
                        let displayName = item.display_name || item.product_name || '-';
                        let qtyOrdered = item.quantity || 0;
                        let buyPrice = item.unit_price || 0;

                        let html = `
                        <tr class="row-from-po" data-row-type="po">
                            <td class="text-center font-weight-bold">${index + 1}</td>
                            <td>
                                <input type="hidden" name="items[${rowIndex}][purchase_order_item_id]" value="${item.id}">
                                <input type="hidden" name="items[${rowIndex}][product_name]" value="${escHtml(item.product_name || '')}">
                                <input type="hidden" name="items[${rowIndex}][description]" value="${escHtml(item.description || '')}">
                                <input type="hidden" name="items[${rowIndex}][satuan]" value="${escHtml(item.satuan || '')}">
                                <div class="font-weight-bold text-dark" style="font-size:13px;">${escHtml(displayName)}</div>
                                ${item.description ? '<small class="text-muted">' + escHtml(item.description) + '</small>' : ''}
                            </td>
                            <td>
                                <input type="text" name="items[${rowIndex}][batch_no]" class="form-control" placeholder="Batch No...">
                            </td>
                            <td>
                                <input type="text" name="items[${rowIndex}][buy_price]" value="${formatNum(buyPrice)}" class="form-control buy-price-input text-right">
                            </td>
                            <td class="text-center">
                                <input type="hidden" name="items[${rowIndex}][qty_ordered]" value="${qtyOrdered}">
                                <span class="font-weight-bold text-info">${formatNum(qtyOrdered)}</span>
                                ${item.satuan ? '<small class="text-muted d-block">' + escHtml(item.satuan) + '</small>' : ''}
                            </td>
                            <td>
                                <input type="text" name="items[${rowIndex}][qty_received]" value="${formatNum(qtyOrdered)}" class="form-control qty-received-input text-center font-weight-bold" required>
                            </td>
                            <td>
                                <input type="text" name="items[${rowIndex}][item_notes]" class="form-control" placeholder="Catatan...">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>`;
                        $('#tbody-items').append(html);
                    });
                } else {
                    showEmptyRowIfNeeded();
                    Swal.fire({ icon: 'info', title: 'Info', text: 'PO ini tidak memiliki item barang.' });
                }
            },
            error: function(err) {
                $.LoadingOverlay("hide");
                Swal.fire({ icon: 'error', title: 'Gagal Memuat PO', text: err.responseJSON?.message || 'Terjadi kesalahan' });
            }
        });
    }

    function clearPo() {
        $('#po-info-banner').slideUp(200);
        // Hapus baris dari PO
        $('#tbody-items tr[data-row-type="po"]').remove();
        updateIndex();
        showEmptyRowIfNeeded();
        // Reset supplier & warehouse
        $('#supplier_id').val('').trigger('change');
        $('#warehouse_id').val('').trigger('change');
    }

    // ============================
    // Add Manual Row (cari produk)
    // ============================
    function addManualRow() {
        hideEmptyRow();
        let rowIndex = rowCount++;
        let rowNum = $('#tbody-items tr:visible').not('#empty-row').length + 1;

        let html = `
        <tr data-row-type="manual">
            <td class="text-center font-weight-bold text-muted">${rowNum}</td>
            <td>
                <select class="form-control product-select2" style="width:100%;"></select>
                <input type="hidden" name="items[${rowIndex}][product_name]" class="product-name-input">
                <input type="hidden" name="items[${rowIndex}][product_id]" class="product-id-input">
                <input type="hidden" name="items[${rowIndex}][variant_id]" class="variant-id-input">
                <input type="hidden" name="items[${rowIndex}][description]" class="description-input" value="">
                <input type="hidden" name="items[${rowIndex}][satuan]" class="satuan-input" value="">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][batch_no]" class="form-control" placeholder="Batch No...">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][buy_price]" value="0" class="form-control buy-price-input text-right">
            </td>
            <td class="text-center text-muted">—</td>
            <td>
                <input type="hidden" name="items[${rowIndex}][qty_ordered]" value="0">
                <input type="text" name="items[${rowIndex}][qty_received]" value="1" class="form-control qty-received-input text-center font-weight-bold" required>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][item_notes]" class="form-control" placeholder="Catatan...">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;

        let $tr = $(html).appendTo('#tbody-items');

        $tr.find('.product-select2').select2({
            ajax: {
                url: "{{ route('admin.purchasing.goods_receipts.get_products') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ search: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            },
            placeholder: 'Ketik nama produk / merek / SKU...',
            minimumInputLength: 2,
            width: '100%'
        }).on('select2:select', function(e) {
            let data = e.params.data;
            let row = $(this).closest('tr');
            row.find('.product-name-input').val(data.product_name);
            row.find('.product-id-input').val(data.product_id);
            row.find('.variant-id-input').val(data.variant_id);
            row.find('.description-input').val(data.description);
            row.find('.satuan-input').val(data.satuan);
        });
    }

    // ============================
    // Add Free Text Row
    // ============================
    function addFreeTextRow() {
        hideEmptyRow();
        let rowIndex = rowCount++;
        let rowNum = $('#tbody-items tr:visible').not('#empty-row').length + 1;

        let html = `
        <tr data-row-type="freetext">
            <td class="text-center font-weight-bold text-muted">${rowNum}</td>
            <td>
                <input type="text" name="items[${rowIndex}][product_name]" class="form-control" placeholder="Nama barang / item..." required>
                <input type="hidden" name="items[${rowIndex}][description]" value="">
                <input type="hidden" name="items[${rowIndex}][satuan]" value="">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][batch_no]" class="form-control" placeholder="Batch No...">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][buy_price]" value="0" class="form-control buy-price-input text-right">
            </td>
            <td class="text-center text-muted">—</td>
            <td>
                <input type="hidden" name="items[${rowIndex}][qty_ordered]" value="0">
                <input type="text" name="items[${rowIndex}][qty_received]" value="1" class="form-control qty-received-input text-center font-weight-bold" required>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][item_notes]" class="form-control" placeholder="Catatan...">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;

        $('#tbody-items').append(html);
    }

    function updateIndex() {
        let i = 1;
        $('#tbody-items tr:visible').not('#empty-row').each(function() {
            $(this).find('td:first').text(i++);
        });
    }

    function escHtml(str) {
        if (!str) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
</script>
@endpush
