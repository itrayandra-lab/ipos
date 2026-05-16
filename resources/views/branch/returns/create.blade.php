@extends('master')
@section('title', 'Buat Return Barang')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('branch.returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Buat Pengajuan Return Barang</h1>
        </div>
        <form id="form-return" action="{{ route('branch.returns.store') }}" method="POST">
            @csrf
            <div class="section-body">
                <div class="card">
                    <div class="card-header"><h4>Informasi Return</h4></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Alasan Return</label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="Jelaskan alasan return barang ke pusat..."></textarea>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0">Barang yang Akan Di-Return</h6>
                            <button type="button" class="btn btn-sm btn-outline-warning" id="btn-add-item">
                                <i class="fas fa-plus mr-1"></i> Tambah Item
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="table-items">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>Batch / Produk</th><th>Stok Tersedia</th><th width="120">Qty Return</th><th>Alasan per Item</th><th width="50"></th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('branch.returns.index') }}" class="btn btn-secondary mr-2">Batal</a>
                        <button type="submit" id="btn-submit" class="btn btn-warning btn-lg px-5">
                            <i class="fas fa-undo-alt mr-1"></i> Kirim Pengajuan Return
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
@push('scripts')
<script>
let rowIdx = 0;
const batches = @json($batches);

$(document).ready(function() {
    $('#btn-add-item').on('click', addRow);
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        reindex();
    });
    $(document).on('change', '.select-batch', function() {
        const batchId = $(this).val();
        const batch   = batches.find(b => b.id == batchId);
        $(this).closest('tr').find('.stok-available').text(batch ? batch.qty : '-');
    });

    $('#form-return').on('submit', function(e) {
        e.preventDefault();
        if ($('#table-items tbody tr').length === 0) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Tambahkan minimal 1 item barang!' });
            return;
        }
        const btn = $('#btn-submit');
        const ori = btn.html();
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');
        $.ajax({
            url: $(this).attr('action'), method: 'POST', data: $(this).serialize(),
            success: res => {
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1800, showConfirmButton: false })
                        .then(() => location.href = res.redirect);
                } else {
                    btn.attr('disabled', false).html(ori);
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: err => {
                btn.attr('disabled', false).html(ori);
                Swal.fire({ icon: 'error', title: 'Error', text: err.responseJSON?.message || 'Terjadi kesalahan' });
            }
        });
    });
});

function addRow() {
    const idx = rowIdx++;
    let opts = '<option value="">— Pilih Batch —</option>';
    batches.forEach(b => {
        const merek   = b.product?.merek?.name ? b.product.merek.name + ' ' : '';
        const variant = b.variant?.variant_name ? ' / ' + b.variant.variant_name : '';
        opts += `<option value="${b.id}">[${b.batch_no}] ${merek}${b.product?.name || ''}${variant} (Stok: ${b.qty})</option>`;
    });
    const html = `
        <tr>
            <td class="align-middle text-center">${$('#table-items tbody tr').length + 1}</td>
            <td><select name="items[${idx}][product_batch_id]" class="form-control form-control-sm select-batch" required>${opts}</select></td>
            <td class="align-middle text-center stok-available text-muted">—</td>
            <td><input type="number" name="items[${idx}][qty]" class="form-control form-control-sm" min="1" required placeholder="0"></td>
            <td><input type="text" name="items[${idx}][reason]" class="form-control form-control-sm" placeholder="Alasan return item ini..."></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
    $('#table-items tbody').append(html);
}

function reindex() {
    $('#table-items tbody tr').each((i, tr) => $(tr).find('td:first').text(i + 1));
}
</script>
@endpush
