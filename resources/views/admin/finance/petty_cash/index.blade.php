@extends('master')
@section('title', 'Petty Cash')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Petty Cash (Kas Kecil)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item">Petty Cash</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Saldo Kas Kecil</h4>
                            </div>
                            <div class="card-body">
                                Rp {{ number_format($balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Riwayat Transaksi Kas Kecil</h4>
                    <div class="card-header-form">
                        <button class="btn btn-success mr-2" onclick="openTopUpModal()">
                            <i class="fas fa-arrow-alt-circle-down mr-1"></i> Top Up Saldo
                        </button>
                        <button class="btn btn-primary" onclick="openTransactionModal()">
                            <i class="fas fa-plus mr-1"></i> Tambah Transaksi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="petty-cash-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Keterangan</th>
                                    <th>Saldo Akhir</th>
                                    <th>User</th>
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

<!-- Modal Add Petty Cash -->
<div class="modal fade" id="modalAddPettyCash" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formAddPettyCash">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Transaksi Kas Kecil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tipe Transaksi</label>
                        <select class="form-control" name="type" required>
                            <option value="in">Kas Masuk (Pengisian Saldo)</option>
                            <option value="out">Kas Keluar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (Rp)</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea class="form-control" name="description" required rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#petty-cash-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.finance.petty_cash.all') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'type', name: 'type' },
                { data: 'amount', name: 'amount' },
                { data: 'description', name: 'description' },
                { data: 'balance_after', name: 'balance_after' },
                { data: 'user.name', name: 'user.name' },
            ]
        });

        window.openTopUpModal = function() {
            $('#modalAddPettyCash').modal('show');
            $('select[name="type"]').val('in').trigger('change');
            $('.modal-title').text('Top Up Saldo Kas Kecil');
        };

        window.openTransactionModal = function() {
            $('#modalAddPettyCash').modal('show');
            $('select[name="type"]').val('out').trigger('change');
            $('.modal-title').text('Tambah Transaksi Kas Kecil');
        };

        $('#formAddPettyCash').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('admin.finance.petty_cash.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#modalAddPettyCash').modal('hide');
                        $('#formAddPettyCash')[0].reset();
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: response.message, position: 'topRight' });
                        // Update the balance card value
                        location.reload(); 
                    } else {
                        iziToast.error({ title: 'Gagal', message: response.message, position: 'topRight' });
                    }
                }
            });
        });
    });
</script>
@endsection
