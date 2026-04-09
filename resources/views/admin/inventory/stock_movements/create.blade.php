@extends('master')

@section('title', 'Tambah Stock Movement')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.stock_movements.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Buat Pengiriman Stok</h1>
        </div>

        <form id="form-movement" action="{{ route('admin.stock_movements.store') }}" method="POST">
            @csrf
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gudang Asal <span class="text-danger">*</span></label>
                                            <select name="from_warehouse_id" id="from_warehouse_id" class="form-control selectric" required>
                                                <option value="">Pilih Gudang Asal</option>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gudang Tujuan <span class="text-danger">*</span></label>
                                            <select name="to_warehouse_id" id="to_warehouse_id" class="form-control selectric" required>
                                                <option value="">Pilih Gudang Tujuan</option>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Catatan</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan pengiriman..."></textarea>
                                </div>

                                <hr>
                                <h6 class="font-weight-bold">Item Barang yang Dikirim</h6>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="table-items">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="30">#</th>
                                                <th>Cari Batch / Produk</th>
                                                <th width="140">Qty Dikirim</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-row">
                                                        <i class="fas fa-plus mr-1"></i> Tambah Item
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-whitesmoke text-right">
                                <a href="{{ route('admin.stock_movements.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-paper-plane mr-1"></i> Buat Movement
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

    $(document).ready(function() {
        $('#btn-add-row').on('click', function() {
            if (!$('#from_warehouse_id').val()) {
                swal('Peringatan', 'Pilih gudang asal terlebih dahulu!', 'warning');
                return;
            }
            addRow();
        });

        $('#from_warehouse_id').on('change', function() {
            $('#table-items tbody').empty();
            rowCount = 0;
        });

        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
            reindexTable();
        });

        $('#form-movement').on('submit', function(e) {
            e.preventDefault();
            if ($('#table-items tbody tr').length === 0) {
                swal('Error', 'Minimal 1 item harus ditambahkan!', 'error');
                return;
            }
            let btn = $(this).find('button[type=submit]');
            btn.addClass('btn-progress').attr('disabled', true);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        swal('Berhasil', res.message, 'success').then(() => location.href = res.redirect);
                    } else {
                        btn.removeClass('btn-progress').attr('disabled', false);
                        swal('Error', res.message, 'error');
                    }
                },
                error: function(err) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    swal('Error', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
        });
    });

    function addRow() {
        let index = rowCount++;
        let whId = $('#from_warehouse_id').val();
        let num = $('#table-items tbody tr').length + 1;
        let html = `
            <tr>
                <td class="align-middle text-center">${num}</td>
                <td>
                    <select name="items[${index}][product_batch_id]" class="form-control select-batch" style="width:100%" required>
                        <option value=""></option>
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${index}][qty]" class="form-control" min="1" step="1" required placeholder="0">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        $('#table-items tbody').append(html);
        initBatchSelect2($('#table-items tbody tr:last .select-batch'), whId);
    }

    function initBatchSelect2(el, whId) {
        el.select2({
            placeholder: 'Cari produk/batch di gudang...',
            width: '100%',
            ajax: {
                url: "{{ route('admin.pos.products') }}",
                dataType: 'json',
                delay: 300,
                data: params => ({ search: params.term, warehouse_id: whId }),
                processResults: function(data) {
                    let results = [];
                    data.forEach(p => {
                        let merekName = p.merek ? p.merek.name + ' ' : '';
                        p.batches.forEach(b => {
                            results.push({ id: b.id, text: `[${b.batch_no}] ${merekName}${p.name} — Stok: ${b.qty}` });
                        });
                    });
                    return { results };
                },
                cache: true
            }
        });
    }

    function reindexTable() {
        $('#table-items tbody tr').each((i, tr) => $(tr).find('td:first').text(i + 1));
    }
</script>
@endpush
