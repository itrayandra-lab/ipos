@extends('master')

@section('title', 'Terima Barang')

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
    .section-header h1 {
        font-weight: 800 !important;
        color: #1e293b !important;
        margin-bottom: 0;
    }

    .card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
    }
    .card-header {
        background-color: transparent !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .card-header h4 {
        color: #1e293b;
        font-weight: 800;
        font-size: 16px;
        margin-bottom: 0;
    }

    .form-group label {
        font-weight: 700;
        color: #475569;
        font-size: 13px;
        margin-bottom: 8px;
    }

    #table-items {
        table-layout: fixed !important;
    }
    #table-items thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 12px 15px !important;
    }
    #table-items th, #table-items td {
        vertical-align: middle !important;
        padding: 8px 10px !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }
    #table-items .form-control {
        height: 32px !important;
        padding: 4px 8px !important;
        font-size: 13px !important;
        border-radius: 8px;
    }
    #table-items .form-control[readonly] {
        background-color: #f8fafc;
        cursor: default;
    }
    .select2-container .select2-selection--single {
        min-height: 32px !important;
        border-radius: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 4px 10px !important;
        font-size: 13px !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        border: none;
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3);
    }
    .btn-secondary {
        border-radius: 12px;
        padding: 12px 25px;
        font-weight: 600;
    }
    .btn-outline-info, .btn-outline-secondary {
        border-radius: 8px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.goods_receipts.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Penerimaan Barang Baru</h1>
        </div>

        <form id="form-gr" action="{{ route('admin.purchasing.goods_receipts.store') }}" method="POST" novalidate>
            @csrf
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-clipboard-list text-teal mr-2"></i> Informasi Penerimaan</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>No. SJ Internal</label>
                                            <input type="text" value="{{ $sj_number }}" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>No. Surat Jalan Supplier <span class="text-danger">*</span></label>
                                            <input type="text" name="delivery_note_number" class="form-control" placeholder="Input nomor surat jalan fisik..." required>
                                        </div>
                                    </div>
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
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Purchase Order (Opsional)</label>
                                            <select name="purchase_order_id" id="purchase_order_id" class="form-control select2">
                                                <option value="">-- Tanpa PO (Input Manual) --</option>
                                                @foreach($pos as $po)
                                                    <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier->name }}</option>
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
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Tgl. SJ Supplier <span class="text-danger">*</span></label>
                                                    <input type="date" name="delivery_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
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
                                    <textarea name="notes" class="form-control" style="height: 42px;"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h4><i class="fas fa-boxes text-teal mr-2"></i> Item Barang Diterima</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="table-items">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 30px;">#</th>
                                                <th style="min-width: 250px;">Nama Produk</th>
                                                <th style="width: 130px;">No. Batch</th>
                                                <th style="width: 140px;">Harga Beli</th>
                                                <th style="width: 120px;">Qty Terima</th>
                                                <th>Catatan</th>
                                                <th style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be loaded here -->
                                        </tbody>
                                        <tfoot>
                                            <tr id="row-manual-btn">
                                                <td colspan="7" class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-add-manual">
                                                        <i class="fas fa-box"></i> Tambah Item Produk
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary ml-2" id="btn-add-freetext">
                                                        <i class="fas fa-pen"></i> Tambah Item Bebas (Non-Produk)
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-whitesmoke text-right">
                                <a href="{{ route('admin.purchasing.goods_receipts.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="submit" class="btn btn-primary btn-lg px-5"><i class="fas fa-check mr-2"></i> Konfirmasi Penerimaan Barang</button>
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

    function formatNumberId(val) {
        if (val === undefined || val === null || val === '') return '';
        let number = parseFloat(val);
        if (isNaN(number)) return '';
        return number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function parseNumberId(val) {
        if (val === undefined || val === null || val === '') return 0;
        let clean = val.toString().replace(/\./g, '').replace(/,/g, '.');
        return parseFloat(clean) || 0;
    }

    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        $('#purchase_order_id').on('change', function() {
            let poId = $(this).val();
            if (poId) {
                loadPoItems(poId);
                $('#row-manual-btn').hide();
            } else {
                $('#table-items tbody').empty();
                $('#row-manual-btn').show();
            }
        });

        $('#btn-add-manual').on('click', function() {
            addManualRow();
        });

        $('#btn-add-freetext').on('click', function() {
            addFreeTextRow();
        });

        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
            updateTableIndex();
        });

        $(document).on('blur', '.qty-received-input', function() {
            let val = parseNumberId($(this).val());
            $(this).val(formatNumberId(val));
        });

        $(document).on('blur', '.buy-price-input', function() {
            let val = parseNumberId($(this).val());
            $(this).val(formatNumberId(val));
        });

        // Form Submit
        $('#form-gr').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');

            if ($('#table-items tbody tr').length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Harap masukkan minimal 1 item'
                });
                return;
            }

            btn.addClass('btn-progress').attr('disabled', true);
            $.LoadingOverlay("show");

            $.ajax({
                url: "{{ route('admin.purchasing.goods_receipts.store') }}",
                method: "POST",
                data: (function() {
                    let serialized = form.serializeArray();
                    serialized.forEach(item => {
                        if (item.name.includes('qty_received') || item.name.includes('buy_price')) {
                            item.value = parseNumberId(item.value);
                        }
                    });
                    return serialized;
                })(),
                success: function(res) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    $.LoadingOverlay("hide");
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message
                        }).then(() => {
                            window.location.href = res.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                    }
                },
                error: function(err) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    $.LoadingOverlay("hide");
                    console.error('AJAX Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Terjadi kesalahan pada server'
                    });
                }
            });
        });
    });

    function loadPoItems(poId) {
        $.ajax({
            url: "{{ route('admin.purchasing.goods_receipts.get_po_items') }}",
            data: { po_id: poId },
            success: function(res) {
                $('#supplier_id').val(res.supplier_id).trigger('change');
                if (res.warehouse_id) {
                    $('#warehouse_id').val(res.warehouse_id).trigger('change');
                }
                $('#table-items tbody').empty();
                res.items.forEach((item, index) => {
                    let html = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>
                                <input type="hidden" name="items[${index}][purchase_order_item_id]" value="${item.id}">
                                <input type="hidden" name="items[${index}][product_name]" value="${item.product_name}">
                                <input type="hidden" name="items[${index}][description]" value="${item.description || ''}">
                                <input type="hidden" name="items[${index}][satuan]" class="satuan-input" value="">
                                <input type="text" value="${item.display_name || item.product_name}" class="form-control" readonly>
                            </td>
                            <td>
                                <input type="text" name="items[${index}][batch_no]" class="form-control" placeholder="Batch No...">
                            </td>
                            <td>
                                <input type="text" name="items[${index}][buy_price]" value="${formatNumberId(item.unit_price || 0)}" class="form-control buy-price-input text-center" readonly>
                            </td>
                            <td>
                                <input type="hidden" name="items[${index}][qty_ordered]" value="${item.quantity}">
                                <input type="text" name="items[${index}][qty_received]" value="${formatNumberId(item.quantity)}" class="form-control qty-received-input text-center" required>
                            </td>
                            <td>
                                <input type="text" name="items[${index}][item_notes]" class="form-control" placeholder="Catatan...">
                            </td>
                            <td></td>
                        </tr>
                    `;
                    $('#table-items tbody').append(html);
                });
                rowCount = res.items.length;
            }
        });
    }

    function addManualRow() {
        let index = rowCount++;
        let html = `
            <tr>
                <td class="text-center">${$('#table-items tbody tr').length + 1}</td>
                <td>
                    <select class="form-control product-select2" style="width:100%;">
                        <option value="">Cari Produk...</option>
                    </select>
                    <input type="hidden" name="items[${index}][product_name]" class="product-name-input">
                    <input type="hidden" name="items[${index}][product_id]" class="product-id-input">
                    <input type="hidden" name="items[${index}][variant_id]" class="variant-id-input">
                    <input type="hidden" name="items[${index}][description]" class="description-input">
                    <input type="hidden" name="items[${index}][satuan]" class="satuan-input">
                </td>
                <td>
                    <input type="text" name="items[${index}][batch_no]" class="form-control" placeholder="Batch No...">
                </td>
                <td>
                    <input type="text" name="items[${index}][buy_price]" value="0" class="form-control buy-price-input text-center">
                </td>
                <td>
                    <input type="hidden" name="items[${index}][qty_ordered]" value="0">
                    <input type="text" name="items[${index}][qty_received]" value="1" class="form-control qty-received-input text-center" required>
                </td>
                <td>
                    <input type="text" name="items[${index}][item_notes]" class="form-control" placeholder="Catatan...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        let $tr = $(html).appendTo('#table-items tbody');

        let $select = $tr.find('.product-select2');
        $select.select2({
            ajax: {
                url: "{{ route('admin.purchasing.goods_receipts.get_products') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ search: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            },
            placeholder: 'Ketik merek / produk...',
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
        }).on('select2:clear', function() {
            let row = $(this).closest('tr');
            row.find('.product-name-input').val('');
            row.find('.product-id-input').val('');
            row.find('.variant-id-input').val('');
            row.find('.description-input').val('');
            row.find('.satuan-input').val('');
        });
    }

    function updateTableIndex() {
        $('#table-items tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    function addFreeTextRow() {
        let index = rowCount++;
        let html = `
            <tr>
                <td class="text-center">${$('#table-items tbody tr').length + 1}</td>
                <td>
                    <input type="text" name="items[${index}][product_name]" class="form-control" placeholder="Nama barang..." required>
                    <input type="hidden" name="items[${index}][description]" value="">
                    <input type="hidden" name="items[${index}][satuan]" value="">
                </td>
                <td>
                    <input type="text" name="items[${index}][batch_no]" class="form-control" placeholder="Batch No...">
                </td>
                <td>
                    <input type="text" name="items[${index}][buy_price]" value="0" class="form-control buy-price-input text-center">
                </td>
                <td>
                    <input type="hidden" name="items[${index}][qty_ordered]" value="0">
                    <input type="text" name="items[${index}][qty_received]" value="1" class="form-control qty-received-input text-center" required>
                </td>
                <td>
                    <input type="text" name="items[${index}][item_notes]" class="form-control" placeholder="Catatan...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#table-items tbody').append(html);
    }
</script>
@endpush
