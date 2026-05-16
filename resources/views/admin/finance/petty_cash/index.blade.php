@extends('master')
@section('title', 'Petty Cash')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Petty Cash (Kas Kecil)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item active">Petty Cash</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Summary Cards --}}
            <div class="row">
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Saldo Kas Kecil</h4></div>
                            <div class="card-body">Rp {{ number_format($balance, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Masuk (Bulan Ini)</h4></div>
                            <div class="card-body">Rp {{ number_format($monthIn, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Keluar (Bulan Ini)</h4></div>
                            <div class="card-body">Rp {{ number_format($monthOut, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card">
                <div class="card-header">
                    <h4>Riwayat Transaksi Kas Kecil</h4>
                    <div class="card-header-action">
                        @if(in_array(auth()->user()->role, ['finance', 'super_admin']))
                        <button class="btn btn-success btn-sm mr-2" onclick="openTopUpModal()">
                            <i class="fas fa-arrow-alt-circle-down mr-1"></i> Top Up Saldo
                        </button>
                        @endif
                        <button class="btn btn-primary btn-sm" onclick="openTransactionModal()">
                            <i class="fas fa-plus mr-1"></i> Tambah Transaksi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter Bar --}}
                    <div class="row mb-3">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Dari Tanggal</label>
                            <input type="date" id="filter-start" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted">Sampai Tanggal</label>
                            <input type="date" id="filter-end" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 col-sm-4 mb-2">
                            <label class="small font-weight-bold text-muted">Tipe</label>
                            <select id="filter-type" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="in">Kas Masuk</option>
                                <option value="out">Kas Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 mb-2">
                            <label class="small font-weight-bold text-muted">Metode</label>
                            <select id="filter-method" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-4 mb-2">
                            <label class="small font-weight-bold text-muted">Kategori</label>
                            <select id="filter-category" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-1">
                            <button id="btn-filter" class="btn btn-primary btn-sm mr-1">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <button id="btn-reset-filter" class="btn btn-light btn-sm">
                                <i class="fas fa-times mr-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="petty-cash-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Metode</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Keterangan</th>
                                    <th>User</th>
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

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="modalAddPettyCash" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formAddPettyCash" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPettyCashTitle">Tambah Transaksi Kas Kecil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipe Transaksi <span class="text-danger">*</span></label>
                                <select class="form-control" name="type" id="pc-type" required>
                                    @if(in_array(auth()->user()->role, ['finance', 'super_admin']))
                                    <option value="in">Kas Masuk (Top Up Saldo)</option>
                                    @endif
                                    <option value="out" selected>Kas Keluar (Pengeluaran)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="transaction_date" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="payment-method-group">
                                <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-control" name="payment_method" id="pc-payment-method" required>
                                    <option value="cash">💵 Cash (Potong Saldo Kas)</option>
                                    <option value="transfer">🏦 Transfer Bank</option>
                                    <option value="qris">📱 QRIS</option>
                                </select>
                                <small class="text-muted" id="payment-method-hint">Cash akan memotong saldo kas kecil.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="category-group">
                                <label>Kategori Biaya</label>
                                <select class="form-control" name="expense_category_id">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @if($categories->isEmpty())
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Belum ada kategori. <a href="{{ route('admin.finance.expense_categories.index') }}" target="_blank">Tambah dulu</a>.
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" required min="1" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label>Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" required rows="3"
                            placeholder="Tuliskan keterangan transaksi..."></textarea>
                    </div>

                    <div class="form-group" id="receipt-group">
                        <label>Foto Bukti / Struk <span class="text-muted">(Opsional)</span></label>
                        <input type="file" class="form-control" name="receipt_photo" accept=".jpg,.jpeg,.png">
                        <small class="text-muted">Format: JPG, PNG — Maks. 2MB</small>
                    </div>

                    {{-- Saldo info --}}
                    <div class="alert alert-light border" id="balance-info" style="font-size:0.85rem;">
                        <i class="fas fa-info-circle text-primary mr-1"></i>
                        Saldo kas kecil saat ini: <strong>Rp {{ number_format($balance, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye mr-2 text-info"></i> Detail Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless">
                    <tr><th width="140">Tipe</th><td id="det-type">-</td></tr>
                    <tr><th>Tanggal</th><td id="det-date">-</td></tr>
                    <tr><th>Metode</th><td id="det-method">-</td></tr>
                    <tr><th>Kategori</th><td id="det-category">-</td></tr>
                    <tr><th>Jumlah</th><td id="det-amount" class="font-weight-bold text-primary">-</td></tr>
                    <tr><th>Saldo Akhir</th><td id="det-balance">-</td></tr>
                    <tr><th>Keterangan</th><td id="det-desc">-</td></tr>
                    <tr><th>Dicatat Oleh</th><td id="det-user">-</td></tr>
                    <tr><th>Bukti</th><td id="det-receipt">-</td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formEdit" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="edit-id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2 text-warning"></i> Edit Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipe Transaksi <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit-type" name="type" required>
                                    @if(in_array(auth()->user()->role, ['finance', 'super_admin']))
                                    <option value="in">Kas Masuk (Top Up Saldo)</option>
                                    @endif
                                    <option value="out">Kas Keluar (Pengeluaran)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit-date" name="transaction_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="edit-payment-method-group">
                                <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit-payment-method" name="payment_method" required>
                                    <option value="cash">💵 Cash</option>
                                    <option value="transfer">🏦 Transfer Bank</option>
                                    <option value="qris">📱 QRIS</option>
                                </select>
                                <small id="edit-payment-hint" class="text-muted">Cash akan memotong saldo kas kecil.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="edit-category-group">
                                <label>Kategori Biaya</label>
                                <select class="form-control" id="edit-category" name="expense_category_id">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit-amount" name="amount" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit-description" name="description" required rows="3"></textarea>
                    </div>
                    <div class="form-group" id="edit-receipt-group">
                        <label>Ganti Foto Bukti <span class="text-muted">(Opsional)</span></label>
                        <input type="file" class="form-control" name="receipt_photo" accept=".jpg,.jpeg,.png">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah bukti.</small>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#petty-cash-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.finance.petty_cash.all') }}",
                data: function(d) {
                    d.start_date    = $('#filter-start').val();
                    d.end_date      = $('#filter-end').val();
                    d.type          = $('#filter-type').val();
                    d.payment_method = $('#filter-method').val();
                    d.category_id   = $('#filter-category').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex',          name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'transaction_date',      name: 'transaction_date' },
                { data: 'type',                  name: 'type' },
                { data: 'payment_method_label',  name: 'payment_method_label', orderable: false, searchable: false },
                { data: 'category_name',         name: 'category_name', orderable: false },
                { data: 'amount',                name: 'amount' },
                { data: 'description',           name: 'description' },
                { data: 'user.name',             name: 'user.name' },
                { data: 'action',                name: 'action', orderable: false, searchable: false },
            ]
        });

        $('#btn-filter').on('click', function() {
            table.ajax.reload();
        });

        $('#btn-reset-filter').on('click', function() {
            $('#filter-start').val('');
            $('#filter-end').val('');
            $('#filter-type').val('');
            $('#filter-method').val('');
            $('#filter-category').val('');
            table.ajax.reload();
        });

        // Show/hide fields based on type
        function updateFormFields() {
            var type = $('#pc-type').val();
            var pm   = $('#pc-payment-method').val();

            if (type === 'in') {
                $('#payment-method-group').hide();
                $('#category-group').hide();
                $('#receipt-group').hide();
            } else {
                $('#payment-method-group').show();
                $('#category-group').show();
                $('#receipt-group').show();

                // Update hint text
                if (pm === 'cash') {
                    $('#payment-method-hint').text('Cash akan memotong saldo kas kecil.').removeClass('text-info text-primary').addClass('text-muted');
                } else {
                    $('#payment-method-hint').text('Transfer/QRIS tidak memotong saldo kas kecil, hanya dicatat sebagai pengeluaran.').removeClass('text-muted').addClass('text-info');
                }
            }
        }

        $('#pc-type').on('change', updateFormFields);
        $('#pc-payment-method').on('change', updateFormFields);

        window.openTopUpModal = function() {
            $('#formAddPettyCash')[0].reset();
            $('input[name="transaction_date"]').val('{{ date("Y-m-d") }}');
            $('#pc-type').val('in');
            $('#pc-payment-method').val('cash');
            updateFormFields();
            $('#modalPettyCashTitle').text('Top Up Saldo Kas Kecil');
            $('#modalAddPettyCash').modal('show');
        };

        window.openTransactionModal = function() {
            $('#formAddPettyCash')[0].reset();
            $('input[name="transaction_date"]').val('{{ date("Y-m-d") }}');
            $('#pc-type').val('out');
            $('#pc-payment-method').val('cash');
            updateFormFields();
            $('#modalPettyCashTitle').text('Tambah Transaksi Kas Kecil');
            $('#modalAddPettyCash').modal('show');
        };

        // Init on load
        updateFormFields();

        // Detail
        window.showDetail = function(id) {
            $.get('{{ url("admin/finance/petty-cash") }}/' + id, function(res) {
                if (!res.success) return;
                var d = res.data;
                var pmMap = { cash: '💵 Cash', transfer: '🏦 Transfer Bank', qris: '📱 QRIS' };
                var typeLabel = d.type == 'in'
                    ? '<span class="badge badge-success">Kas Masuk</span>'
                    : '<span class="badge badge-danger">Kas Keluar</span>';
                $('#det-type').html(typeLabel);
                $('#det-date').text(d.transaction_date || '-');
                $('#det-method').text(pmMap[d.payment_method] || d.payment_method);
                $('#det-category').text(d.category ? d.category.name : '-');
                $('#det-amount').text('Rp ' + parseInt(d.amount).toLocaleString('id-ID'));
                $('#det-balance').text('Rp ' + parseInt(d.balance_after).toLocaleString('id-ID'));
                $('#det-desc').text(d.description);
                $('#det-user').text(d.user ? d.user.name : '-');
                if (d.receipt_photo) {
                    $('#det-receipt').html('<a href="{{ asset("") }}' + d.receipt_photo + '" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-image mr-1"></i> Lihat Bukti</a>');
                } else {
                    $('#det-receipt').text('-');
                }
                $('#modalDetail').modal('show');
            });
        };

        // Edit
        window.editTransaction = function(id) {
            $.get('{{ url("admin/finance/petty-cash") }}/' + id, function(res) {
                if (!res.success) return;
                var d = res.data;
                $('#edit-id').val(d.id);
                $('#edit-type').val(d.type);
                $('#edit-payment-method').val(d.payment_method || 'cash');
                $('#edit-category').val(d.expense_category_id || '');
                $('#edit-date').val(d.transaction_date || '');
                $('#edit-amount').val(d.amount);
                $('#edit-description').val(d.description);
                updateEditFormFields();
                $('#modalEdit').modal('show');
            });
        };

        function updateEditFormFields() {
            var type = $('#edit-type').val();
            var pm   = $('#edit-payment-method').val();
            if (type === 'in') {
                $('#edit-payment-method-group').hide();
                $('#edit-category-group').hide();
                $('#edit-receipt-group').hide();
            } else {
                $('#edit-payment-method-group').show();
                $('#edit-category-group').show();
                $('#edit-receipt-group').show();
                if (pm === 'cash') {
                    $('#edit-payment-hint').text('Cash akan memotong saldo kas kecil.').removeClass('text-info').addClass('text-muted');
                } else {
                    $('#edit-payment-hint').text('Transfer/QRIS tidak memotong saldo kas kecil.').removeClass('text-muted').addClass('text-info');
                }
            }
        }

        $('#edit-type').on('change', updateEditFormFields);
        $('#edit-payment-method').on('change', updateEditFormFields);

        $('#formEdit').on('submit', function(e) {
            e.preventDefault();
            var id = $('#edit-id').val();
            var formData = new FormData(this);
            $.ajax({
                url: '{{ url("admin/finance/petty-cash") }}/' + id + '/update',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        $('#modalEdit').modal('hide');
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        iziToast.error({ title: 'Gagal', message: res.message, position: 'topRight' });
                    }
                },
                error: function(xhr) {
                    iziToast.error({ title: 'Error', message: xhr.responseJSON?.message || 'Terjadi kesalahan', position: 'topRight' });
                }
            });
        });

        // Delete
        window.deleteTransaction = function(id) {
            swal({
                title: 'Hapus Transaksi?',
                text: 'Data ini akan dihapus dan saldo akan direcalculate.',
                icon: 'warning',
                buttons: ['Batal', 'Hapus'],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: '{{ url("admin/finance/petty-cash") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.success) {
                                iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                                table.ajax.reload();
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                iziToast.error({ title: 'Gagal', message: res.message, position: 'topRight' });
                            }
                        }
                    });
                }
            });
        };

        $('#formAddPettyCash').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('admin.finance.petty_cash.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalAddPettyCash').modal('hide');
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: response.message, position: 'topRight' });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        iziToast.error({ title: 'Gagal', message: response.message, position: 'topRight' });
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                    iziToast.error({ title: 'Error', message: msg, position: 'topRight' });
                }
            });
        });
    });
</script>
@endpush
@endsection
