@extends('master')
@section('title', 'Adjustment PO')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Adjustment Data - Hubungkan Produk ke PO</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Adjustment Data</div>
                <div class="breadcrumb-item">Adjustment PO</div>
            </div>
        </div>
        <div class="section-body">
            <div class="alert alert-info">
                Hubungkan batch atau transaksi lama yang belum memiliki referensi PO asal ke item PO yang benar.
                Pelunasan supplier tetap dialokasikan otomatis per produk dari PO tertua yang sudah diterima dan masih memiliki sisa qty.
            </div>
            <div class="card">
                <div class="card-header"><h4>Produk Terjual Belum Terhubung ke PO</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="unlinked-po-table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sumber Stok</th>
                                    <th>Produk</th>
                                    <th>Supplier Produk</th>
                                    <th class="text-right">Qty Terjual Belum Lunas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="link-po-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hubungkan ke Item PO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong id="link-product-name"></strong></p>
                <div class="form-group">
                    <label for="link-po-item">Item PO</label>
                    <select id="link-po-item" class="form-control" required>
                        <option value="">Memuat PO...</option>
                    </select>
                </div>
                <div class="alert alert-warning mb-0">
                    Penautan ini hanya memperbaiki referensi asal barang. Pastikan item PO memang sesuai dengan barang yang diterima/terjual.
                </div>
                <div class="text-danger small mt-2" id="link-po-error" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                @if($canAdjust)
                <button type="button" class="btn btn-primary" id="save-po-link">Simpan Penautan</button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    const table = $('#unlinked-po-table').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.adjustment.purchase_orders.data') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'batch_no', name: 'batch_no' },
            { data: 'product_label', name: 'product_name' },
            { data: 'supplier_name', name: 'supplier_name', defaultContent: '-' },
            { data: 'sold_qty', name: 'sold_qty', className: 'text-right' },
            { data: 'action', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: 'Semua produk terjual sudah memiliki referensi PO.',
            processing: 'Memuat data...'
        }
    });

    let activeSource = null;
    $(document).on('click', '.btn-link-po', function() {
        const button = $(this);
        activeSource = {
            type: button.data('type'),
            id: button.data('id')
        };
        $('#link-product-name').text(button.closest('tr').find('td').eq(2).text().trim());
        $('#link-po-item').html('<option value="">Memuat PO...</option>');
        $('#link-po-error').hide().text('');
        $('#link-po-modal').modal('show');

        $.get("{{ route('admin.adjustment.purchase_orders.candidates') }}", {
            source_type: activeSource.type,
            source_id: activeSource.id
        }).done(function(response) {
            const options = ['<option value="">-- Pilih PO --</option>'];
            (response.items || []).forEach(function(item) {
                const qtyLeft = Math.max(0, parseFloat(item.quantity) - parseFloat(item.paid_qty || 0));
                const label = `${item.po_number} — ${item.supplier_name} — ${item.product_name || 'Item PO'} (sisa ${new Intl.NumberFormat('id-ID').format(qtyLeft)})`;
                options.push(`<option value="${item.purchase_order_item_id}" ${qtyLeft <= 0 ? 'disabled' : ''}>${$('<div>').text(label).html()}</option>`);
            });
            if (options.length === 1) {
                options.push('<option value="" disabled>Tidak ada PO yang cocok untuk produk/supplier ini</option>');
            }
            $('#link-po-item').html(options.join(''));
        }).fail(function(xhr) {
            $('#link-po-item').html('<option value="">Gagal memuat PO</option>');
            const message = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Gagal memuat daftar PO.';
            $('#link-po-error').text(message).show();
        });
    });

    $('#save-po-link').on('click', function() {
        const poItemId = $('#link-po-item').val();
        if (!activeSource || !poItemId) {
            $('#link-po-error').text('Pilih item PO terlebih dahulu.').show();
            return;
        }

        const button = $(this).prop('disabled', true);
        $.ajax({
            url: "{{ route('admin.adjustment.purchase_orders.update') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                source_type: activeSource.type,
                source_id: activeSource.id,
                purchase_order_item_id: poItemId
            }
        }).done(function(response) {
            $('#link-po-modal').modal('hide');
            table.ajax.reload(null, false);
            if (typeof showToast === 'function') {
                showToast('success', response.message || 'PO berhasil ditautkan.');
            }
        }).fail(function(xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Gagal menyimpan penautan PO.';
            $('#link-po-error').text(message).show();
        }).always(function() {
            button.prop('disabled', false);
        });
    });
});
</script>
@endpush
@endsection
