@extends('master')

@section('title', 'Terima Barang')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        line-height: 42px !important;
        border-color: #e4e6fc !important;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.goods_receipts.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Penerimaan Barang Baru</h1>
        </div>

        <form id="form-gr" action="{{ route('admin.purchasing.goods_receipts.store') }}" method="POST">
            @csrf
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>No. SJ Internal</label>
                                            <input type="text" value="{{ $sj_number }}" class="form-control" readonly>
                                        </div>
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
                                            <label>No. Surat Jalan Supplier <span class="text-danger">*</span></label>
                                            <input type="text" name="delivery_note_number" class="form-control" placeholder="Input nomor surat jalan fisik..." required>
                                        </div>
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
                                            <label>Tanggal Surat Jalan Supplier <span class="text-danger">*</span></label>
                                            <input type="date" name="delivery_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Tanggal Diterima <span class="text-danger">*</span></label>
                                            <input type="date" name="received_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Catatan Penerimaan</label>
                                    <textarea name="notes" class="form-control" style="height: 42px;"></textarea>
                                </div>

                                <hr>

                                <div class="section-title">Item Barang Diterima</div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-md" id="table-items">
                                        <thead>
                                            <tr>
                                                <th width="20">#</th>
                                                <th>Nama Produk</th>
                                                <th>Deskripsi</th>
                                                <th width="100">Satuan</th>
                                                <th width="120">Qty Order</th>
                                                <th width="120">Qty Terima</th>
                                                <th width="120">Selisih</th>
                                                <th>Catatan</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be loaded here -->
                                        </tbody>
                                        <tfoot>
                                            <tr id="row-manual-btn">
                                                <td colspan="9" class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-add-manual">
                                                        <i class="fas fa-plus"></i> Tambah Item Manual
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-whitesmoke text-right">
                                <a href="{{ route('admin.purchasing.goods_receipts.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="submit" class="btn btn-primary btn-lg px-5">Konfirmasi Penerimaan Barang</button>
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

        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
            updateTableIndex();
        });

        $(document).on('input', '.qty-received-input', function() {
            let row = $(this).closest('tr');
            let order = parseNumberId(row.find('.qty-ordered-input').val());
            let received = parseNumberId($(this).val());
            let diff = received - order;
            let diffInput = row.find('.qty-diff-display');
            diffInput.val(formatNumberId(diff));
            
            if (diff < 0) {
                diffInput.addClass('text-danger').removeClass('text-success');
            } else if (diff > 0) {
                diffInput.addClass('text-success').removeClass('text-danger');
            } else {
                diffInput.removeClass('text-danger text-success');
            }
        });

        // Format on blur
        $(document).on('blur', '.qty-received-input', function() {
            let val = parseNumberId($(this).val());
            $(this).val(formatNumberId(val));
        });

        // Form Submit
        $('#form-gr').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');

            if ($('#table-items tbody tr').length === 0) {
                swal('Error', 'Harap masukkan minimal 1 item', 'error');
                return;
            }

            btn.addClass('btn-progress').attr('disabled', true);
            $.LoadingOverlay("show");

            $.ajax({
                url: "{{ route('admin.purchasing.goods_receipts.store') }}",
                method: "POST",
                data: (function() {
                    // Pre-process quantities back to standard numeric format for backend
                    let serialized = form.serializeArray();
                    serialized.forEach(item => {
                        if (item.name.includes('qty_received')) {
                            item.value = parseNumberId(item.value);
                        }
                    });
                    return serialized;
                })(),
                success: function(res) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    $.LoadingOverlay("hide");
                    if (res.status === 'success') {
                        swal('Berhasil', res.message, 'success').then(() => {
                            window.location.href = res.redirect;
                        });
                    } else {
                        swal('Error', res.message, 'error');
                    }
                },
                error: function(err) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    $.LoadingOverlay("hide");
                    console.error('AJAX Error:', err);
                    swal('Error', err.responseJSON?.message || 'Terjadi kesalahan pada server', 'error');
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
                $('#table-items tbody').empty();
                res.items.forEach((item, index) => {
                    let html = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>
                                <input type="hidden" name="items[${index}][purchase_order_item_id]" value="${item.id}">
                                <input type="text" name="items[${index}][product_name]" value="${item.product_name}" class="form-control" readonly>
                            </td>
                            <td>
                                <input type="text" name="items[${index}][description]" value="${item.description || ''}" class="form-control" readonly>
                            </td>
                            <td>
                                <input type="text" name="items[${index}][satuan]" class="form-control text-center" placeholder="pcs/kg...">
                            </td>
                            <td>
                                <input type="text" name="items[${index}][qty_ordered_display]" value="${formatNumberId(item.quantity)}" class="form-control qty-ordered-input text-center" readonly>
                                <input type="hidden" name="items[${index}][qty_ordered]" value="${item.quantity}">
                            </td>
                            <td>
                                <input type="text" name="items[${index}][qty_received]" value="${formatNumberId(item.quantity)}" class="form-control qty-received-input text-center" required>
                            </td>
                            <td>
                                <input type="text" class="form-control qty-diff-display text-center font-weight-bold" value="0" readonly>
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
                    <input type="text" name="items[${index}][product_name]" class="form-control" placeholder="Nama Barang..." required>
                </td>
                <td>
                    <input type="text" name="items[${index}][description]" class="form-control" placeholder="Varian/Netto...">
                </td>
                <td>
                    <input type="text" name="items[${index}][satuan]" class="form-control text-center" placeholder="pcs/kg...">
                </td>
                <td>
                    <input type="text" name="items[${index}][qty_ordered_display]" value="0" class="form-control qty-ordered-input text-center" readonly>
                    <input type="hidden" name="items[${index}][qty_ordered]" value="0">
                </td>
                <td>
                    <input type="text" name="items[${index}][qty_received]" value="1" class="form-control qty-received-input text-center" required>
                </td>
                <td>
                    <input type="text" class="form-control qty-diff-display text-center font-weight-bold" value="1" readonly>
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

    function updateTableIndex() {
        $('#table-items tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
</script>
@endpush
