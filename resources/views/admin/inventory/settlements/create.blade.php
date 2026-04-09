@extends('master')

@section('title', 'Buat Settlement Gudang')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.settlements.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Buat Settlement (Rekap Penjualan Cabang)</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header"><h4>Form Input Penjualan Cabang</h4></div>
                <form id="form-settlement" action="{{ route('admin.settlements.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Gudang Cabang <span class="text-danger">*</span></label>
                                    <select name="warehouse_id" class="form-control selectric" required>
                                        <option value="">Pilih Cabang</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Mulai Periode <span class="text-danger">*</span></label>
                                    <input type="date" name="period_start" class="form-control" required value="{{ date('Y-m-01') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Akhir Periode <span class="text-danger">*</span></label>
                                    <input type="date" name="period_end" class="form-control" required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6>Daftar Produk Terjual</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="table-items">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="40%">Produk</th>
                                        <th width="20%">Harga Unit (Rp)</th>
                                        <th width="15%">Qty Terjual</th>
                                        <th width="20%">Subtotal</th>
                                        <th width="5%">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="empty-row">
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada produk ditambahkan</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right text-uppercase">Total Tagihan</th>
                                        <th id="total-amount-display" class="text-primary font-weight-bold">Rp 0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-toggle="modal" data-target="#modal-add-product">
                            <i class="fas fa-plus mr-1"></i> Tambah Produk
                        </button>

                        <div class="form-group mt-4">
                            <label>Catatan (Metode Penjualan, dll)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Rekap penjualan Marketplace Shopee minggu ke-1..."></textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-whitesmoke text-right">
                        <a href="{{ route('admin.settlements.index') }}" class="btn btn-secondary mr-2">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5">Kirim Settlement</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<!-- Modal Add Product -->
<div class="modal fade" id="modal-add-product" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cari & Tambah Produk</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Pilih Produk</label>
                    <select id="select-product" class="form-control" style="width: 100%"></select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Qty Terjual</label>
                            <input type="number" id="input-qty" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Harga Jual (Pusat)</label>
                            <input type="number" id="input-price" class="form-control" value="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-add-to-list">Tambah ke Daftar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        let itemCount = 0;

        // Initialize Select2 for product search
        $('#select-product').select2({
            dropdownParent: $('#modal-add-product'),
            ajax: {
                url: "{{ route('admin.products.all') }}",
                delay: 250,
                data: function(params) {
                    return { search: params.term };
                },
                processResults: function(data) {
                    return {
                        results: data.map(i => ({
                            id: i.id,
                            text: `${i.merek ? i.merek.name + ' ' : ''}${i.name}`,
                            price: i.price_real || i.price || 0
                        }))
                    };
                }
            }
        });

        $('#select-product').on('select2:select', function(e) {
            $('#input-price').val(e.params.data.price);
        });

        $('#btn-add-to-list').on('click', function() {
            let p = $('#select-product').select2('data')[0];
            let qty = $('#input-qty').val();
            let price = $('#input-price').val();
            
            if (!p) return swal('Error', 'Pilih produk terlebih dahulu', 'error');

            $('.empty-row').hide();
            
            let id = p.id;
            let subtotal = qty * price;
            
            let html = `
                <tr id="row-${itemCount}">
                    <td>
                        <input type="hidden" name="items[${itemCount}][product_id]" value="${id}">
                        <strong>${p.text}</strong>
                    </td>
                    <td>
                        <input type="number" name="items[${itemCount}][price]" class="form-control form-control-sm item-price" value="${price}" readonly>
                    </td>
                    <td>
                        <input type="number" name="items[${itemCount}][qty]" class="form-control form-control-sm item-qty" value="${qty}" required>
                    </td>
                    <td class="item-subtotal-text text-right font-weight-bold">
                        Rp ${new Intl.NumberFormat('id-ID').format(subtotal)}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove" data-id="${itemCount}"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;

            $('#table-items tbody').append(html);
            itemCount++;
            
            $('#modal-add-product').modal('hide');
            $('#select-product').val(null).trigger('change');
            $('#input-qty').val(1);
            
            updateTotal();
        });

        $(document).on('change', '.item-qty', function() {
            let row = $(this).closest('tr');
            let qty = $(this).val();
            let price = row.find('.item-price').val();
            let subtotal = qty * price;
            row.find('.item-subtotal-text').text('Rp ' + new Intl.NumberFormat('id-ID').format(subtotal));
            updateTotal();
        });

        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#table-items tbody tr:not(.empty-row)').length === 0) {
                $('.empty-row').show();
            }
            updateTotal();
        });

        function updateTotal() {
            let total = 0;
            $('#table-items tbody tr:not(.empty-row)').each(function() {
                let qty = $(this).find('.item-qty').val();
                let price = $(this).find('.item-price').val();
                total += (qty * price);
            });
            $('#total-amount-display').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
        }

        $('#form-settlement').on('submit', function(e) {
            e.preventDefault();
            if ($('#table-items tbody tr:not(.empty-row)').length === 0) {
                return swal('Error', 'Tambahkan minimal satu produk terjual', 'error');
            }

            let btn = $(this).find('button[type=submit]');
            btn.addClass('btn-progress').attr('disabled', true);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        swal('Berhasil', res.message, 'success').then(() => {
                            window.location.href = res.redirect;
                        });
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
</script>
@endpush
