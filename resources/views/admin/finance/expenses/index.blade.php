@extends('master')
@section('title', 'Pengeluaran (Expenses)')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pengeluaran (Expenses)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Finance</div>
                <div class="breadcrumb-item">Expenses</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Data Pengeluaran Operasional</h4>
                    <div class="card-header-form">
                        <a href="{{ route('admin.finance.expense_categories.index') }}" class="btn btn-info mr-2">
                            <i class="fas fa-tags mr-1"></i> Kelola Kategori
                        </a>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddExpense">
                            <i class="fas fa-plus mr-1"></i> Tambah Pengeluaran
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="filter-row mb-4">
                        <form id="filter-form" class="row">
                            <div class="col-md-3">
                                <label>Mulai Tanggal</label>
                                <input type="date" class="form-control" id="start_date">
                            </div>
                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date" class="form-control" id="end_date">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="expenses-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Keterangan</th>
                                    <th>User</th>
                                    <th>Action</th>
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

<!-- Modal Add Expense -->
<div class="modal fade" id="modalAddExpense" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formAddExpense" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengeluaran Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori Biaya</label>
                                <select class="form-control" name="expense_category_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Transaksi</label>
                                <input type="date" class="form-control" name="transaction_date" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jumlah (Rp)</label>
                                <input type="number" class="form-control" name="amount" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Metode Pembayaran</label>
                                <select class="form-control" name="payment_method" id="payment_method" required>
                                    <option value="petty_cash">Kas Kecil (Petty Cash)</option>
                                    <option value="bank_transfer">Transfer Bank</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12" id="bank_account_div" style="display: none;">
                            <div class="form-group">
                                <label>Rekening Bank</label>
                                <select class="form-control" name="bank_account_id">
                                    <option value="">-- Pilih Rekening --</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }} ({{ $account->account_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea class="form-control" name="description" required rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Foto Bukti / Struk (Optional)</label>
                                <input type="file" class="form-control" name="receipt_photo">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#expenses-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.finance.expenses.all') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'category.name', name: 'category.name' },
                { data: 'amount', name: 'amount' },
                { data: 'payment_method', name: 'payment_method' },
                { data: 'description', name: 'description' },
                { data: 'user.name', name: 'user.name' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        $('#payment_method').on('change', function() {
            if ($(this).val() == 'bank_transfer') {
                $('#bank_account_div').show();
                $('select[name="bank_account_id"]').prop('required', true);
            } else {
                $('#bank_account_div').hide();
                $('select[name="bank_account_id"]').prop('required', false);
            }
        });

        $('#formAddExpense').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: "{{ route('admin.finance.expenses.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalAddExpense').modal('hide');
                        $('#formAddExpense')[0].reset();
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: response.message, position: 'topRight' });
                    } else {
                        iziToast.error({ title: 'Gagal', message: response.message, position: 'topRight' });
                    }
                }
            });
        });

        window.deleteExpense = function(id) {
            if (confirm('Yakin ingin menghapus data pengeluaran ini? Jika dibayar dengan kas kecil, saldo akan dikembalikan.')) {
                $.ajax({
                    url: "{{ url('admin/finance/expenses') }}/" + id,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            iziToast.success({ title: 'Berhasil', message: 'Data berhasil dihapus', position: 'topRight' });
                        } else {
                            iziToast.error({ title: 'Gagal', message: response.message, position: 'topRight' });
                        }
                    }
                });
            }
        };
    });
</script>
@endsection
