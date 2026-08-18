@extends('master')

@section('title', 'Tambah Surat Jalan Supplier')

@push('styles')
<style>
    .section-header {
        background: #fff; padding: 20px 25px !important; border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px !important;
        border-left: 5px solid #0d9488; display: flex; justify-content: space-between; align-items: center;
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
    #table-items th, #table-items td { vertical-align: middle !important; padding: 8px 10px !important; }
    #table-items .form-control { height: 34px !important; padding: 4px 8px !important; font-size: 13px !important; border-radius: 8px; }
    #table-items .form-control[readonly] { background-color: #f1f5f9; cursor: default; color: #64748b; }

    .btn-save { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border: none; padding: 12px 30px; border-radius: 12px; font-weight: 700; transition: all 0.3s; box-shadow: 0 4px 12px rgba(13,148,136,0.2); color: white; }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(13,148,136,0.3); color: white; }

    .batch-info { font-size: 11px; color: #64748b; margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.purchasing.supplier_delivery_notes.index') }}" class="btn btn-icon mr-3"><i class="fas fa-arrow-left"></i></a>
                <h1>Tambah Surat Jalan Supplier</h1>
            </div>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchasing.supplier_delivery_notes.index') }}">Surat Jalan Supplier</a></div>
                <div class="breadcrumb-item active">Buat</div>
            </div>
        </div>

        <form id="form-sdn" action="{{ route('admin.purchasing.supplier_delivery_notes.store') }}" method="POST" novalidate>
            @csrf
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-clipboard-list text-teal mr-2"></i> Informasi Surat Jalan</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No. Surat Jalan (Auto)</label>
                                            <input type="text" value="{{ $sj_number }}" class="form-control" readonly style="background:#f1f5f9; font-family:monospace; font-weight:800;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No. SJ Fisik <span class="text-muted">(Opsional)</span></label>
                                            <input type="text" name="delivery_note_number" class="form-control" placeholder="Contoh: SJ/SUP/2026/0012">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Supplier <span class="text-danger">*</span></label>
                                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                                <option value="">Pilih Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal <span class="text-danger">*</span></label>
                                            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="1" placeholder="Catatan tambahan..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4><i class="fas fa-boxes text-teal mr-2"></i> Item Batch (Link Stok yang Sudah Ada)</h4>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-batch">
                                    <i class="fas fa-search mr-1"></i> Tambah Batch
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="table-items">
                                        <thead>
                                            <tr>
                                                <th style="width:40px;" class="text-center">#</th>
                                                <th style="min-width:250px;">Produk</th>
                                                <th style="width:100px;">Batch No</th>
                                                <th style="width:100px;">Gudang</th>
                                                <th style="width:100px;" class="text-center">Stok Saat Ini</th>
                                                <th style="width:110px;" class="text-center">Qty Dikirim <span class="text-danger">*</span></th>
                                                <th style="width:120px;">Expiry</th>
                                                <th style="width:40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-items">
                                            <tr id="empty-row">
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                                    Klik "Tambah Batch" untuk mencari dan memilih batch produk yang sudah ada
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-whitesmoke text-right">
                                <a href="{{ route('admin.purchasing.supplier_delivery_notes.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-check mr-2"></i> Simpan Surat Jalan
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
<script>
    let rowCount = 0;
    let addedBatchIds = [];

    function formatNum(val) {
        let n = parseInt(val);
        if (isNaN(n)) return '0';
        return n.toLocaleString('id-ID');
    }

    function hideEmptyRow() { $('#empty-row').hide(); }
    function showEmptyRowIfNeeded() {
        if ($('#tbody-items tr:visible').not('#empty-row').length === 0) {
            $('#empty-row').show();
        }
    }

    $(document).ready(function() {
        $('#supplier_id').select2({ width: '100%', placeholder: 'Pilih Supplier', allowClear: true });

        $('#btn-add-batch').on('click', function() {
            openBatchSearch();
        });

        $(document).on('click', '.btn-remove-row', function() {
            let batchId = $(this).data('batch-id');
            addedBatchIds = addedBatchIds.filter(id => id != batchId);
            $(this).closest('tr').remove();
            updateIndex();
            showEmptyRowIfNeeded();
        });

        $('#form-sdn').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');

            let realRows = $('#tbody-items tr:visible').not('#empty-row');
            if (realRows.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Harap tambahkan minimal 1 batch.' });
                return;
            }

            let errors = [];
            if (!$('[name="supplier_id"]').val()) errors.push('Supplier wajib dipilih');

            let hasInvalidQty = false;
            realRows.each(function() {
                let qty = parseInt($(this).find('.qty-input').val());
                if (!qty || qty <= 0) hasInvalidQty = true;
            });
            if (hasInvalidQty) errors.push('Qty Dikirim setiap item harus lebih dari 0');

            if (errors.length > 0) {
                Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', html: '<div style="text-align:left">' + errors.map(e => '<div style="padding:4px 0">• ' + e + '</div>').join('') + '</div>' });
                return;
            }

            btn.addClass('btn-progress').attr('disabled', true);
            $.LoadingOverlay("show");

            let formData = form.serializeArray();
            $.ajax({
                url: "{{ route('admin.purchasing.supplier_delivery_notes.store') }}",
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

    function openBatchSearch() {
        let supplierId = $('#supplier_id').val();
        let url = "{{ route('admin.purchasing.supplier_delivery_notes.get_batches') }}";
        if (supplierId) url += '?supplier_id=' + supplierId;

        Swal.fire({
            title: 'Cari Batch Produk',
            html: '<input id="swal-search" class="swal2-input" placeholder="Ketik nama produk, merek, atau batch no..." style="font-size:14px;">' +
                  '<div id="swal-results" style="text-align:left;max-height:300px;overflow-y:auto;margin-top:15px;"></div>',
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonText: 'Tutup',
            width: 700,
            didOpen: () => {
                let $input = $('#swal-search');
                let $results = $('#swal-results');
                let debounceTimer;

                $input.on('input', function() {
                    clearTimeout(debounceTimer);
                    let search = $(this).val();
                    if (search.length < 2) { $results.html(''); return; }

                    debounceTimer = setTimeout(() => {
                        $.ajax({
                            url: url + (url.includes('?') ? '&' : '?') + 'search=' + encodeURIComponent(search),
                            success: function(data) {
                                $results.html('');
                                if (data.length === 0) {
                                    $results.html('<div class="text-center text-muted py-3">Batch tidak ditemukan</div>');
                                    return;
                                }
                                data.forEach(function(batch) {
                                    if (addedBatchIds.includes(batch.id)) return;
                                    let disabled = batch.current_stock <= 0;
                                    let btnClass = disabled ? 'btn-outline-secondary' : 'btn-outline-success';
                                    let $item = $(
                                        '<div class="d-flex align-items-center justify-content-between p-2 mb-1 ' + (disabled ? 'opacity-50' : '') + '" ' + (disabled ? '' : 'style="cursor:pointer;"') + ' data-batch-id="' + batch.id + '">' +
                                            '<div>' +
                                                '<div class="font-weight-bold" style="font-size:13px;">' + escHtml(batch.product_name) + '</div>' +
                                                '<div class="batch-info">Batch: ' + escHtml(batch.batch_no) + ' | Gudang: ' + escHtml(batch.warehouse_name) + ' | Exp: ' + batch.expiry_date + '</div>' +
                                            '</div>' +
                                            '<div class="text-right">' +
                                                '<div class="font-weight-bold text-' + (disabled ? 'muted' : 'success') + '">' + batch.current_stock + ' unit</div>' +
                                                (disabled ? '<small class="text-danger">Stok habis</small>' : '<button type="button" class="btn btn-sm ' + btnClass + ' btn-select-batch mt-1">Pilih</button>') +
                                            '</div>' +
                                        '</div>'
                                    );
                                    if (!disabled) {
                                        $item.find('.btn-select-batch').on('click', function(e) {
                                            e.stopPropagation();
                                            addBatchRow(batch);
                                            addedBatchIds.push(batch.id);
                                            $(this).closest('div[style]').remove();
                                            Swal.close();
                                        });
                                    }
                                    $results.append($item);
                                });
                            }
                        });
                    }, 300);
                });

                setTimeout(() => $input.focus(), 100);
            }
        });
    }

    function addBatchRow(batch) {
        hideEmptyRow();
        let rowIndex = rowCount++;
        let rowNum = $('#tbody-items tr:visible').not('#empty-row').length + 1;

        let html = `
        <tr data-batch-id="${batch.id}">
            <td class="text-center font-weight-bold text-muted">${rowNum}</td>
            <td>
                <div class="font-weight-bold text-dark" style="font-size:13px;">${escHtml(batch.product_name)}</div>
                <input type="hidden" name="items[${rowIndex}][product_batch_id]" value="${batch.id}" class="batch-id-input">
            </td>
            <td><span class="badge badge-info">${escHtml(batch.batch_no)}</span></td>
            <td><small>${escHtml(batch.warehouse_name)}</small></td>
            <td class="text-center font-weight-bold">${formatNum(batch.current_stock)}</td>
            <td>
                <input type="number" name="items[${rowIndex}][qty]" value="${batch.current_stock}" min="1" max="${batch.current_stock}" class="form-control qty-input text-center font-weight-bold" required>
            </td>
            <td><small>${batch.expiry_date}</small></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" data-batch-id="${batch.id}" title="Hapus">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>`;

        $('#tbody-items').append(html);
        updateIndex();
    }

    function updateIndex() {
        let i = 1;
        $('#tbody-items tr:visible').not('#empty-row').each(function() {
            $(this).find('td:first').text(i++);
        });
    }

    function escHtml(str) {
        if (!str) return '';
        return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
</script>
@endpush
