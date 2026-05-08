@extends('master')

@section('title', 'Detail Transaksi')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Transaksi</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ url('admin/transactions') }}">Data Transaksi</a></div>
                    <div class="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Detail Transaksi #{{ $transaction->id }}</h2>
                <p class="section-lead">Informasi lengkap tentang transaksi.</p>

                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session()->get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Transaksi</h4>
                        <div class="card-header-action">
                            <a href="{{ url('admin/transactions') }}" class="btn btn-primary">Kembali</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-primary border shadow-sm">
                                    <div class="card-header">
                                        <h4><i class="fas fa-info-circle"></i> Informasi Umum</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">ID Transaksi</th>
                                                <td><span class="badge badge-light">#{{ $transaction->id }}</span></td>
                                            </tr>
                                            @if($transaction->invoice_number)
                                            <tr>
                                                <th>Nomor Invoice</th>
                                                <td><span class="badge badge-primary">{{ $transaction->invoice_number }}</span></td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>Tanggal</th>
                                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kasir / Pengguna</th>
                                                <td>{{ $transaction->user->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Sumber / Channel</th>
                                                <td><span class="badge badge-info text-uppercase">{{ $transaction->source ?? 'Offline' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Catatan</th>
                                                <td>{{ $transaction->notes ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="card card-warning border shadow-sm mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-user text-warning"></i> Informasi Customer</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">Nama</th>
                                                <td>{{ $transaction->customer_name ?? 'Guest' }}</td>
                                            </tr>
                                            <tr>
                                                <th>WA / Phone</th>
                                                <td>{{ $transaction->customer_phone ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $transaction->customer_email ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-success border shadow-sm">
                                    <div class="card-header">
                                        <h4><i class="fas fa-money-bill-wave text-success"></i> Pembayaran</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">Status</th>
                                                <td>
                                                    @if($transaction->payment_status == 'paid')
                                                        <span class="badge badge-success">Lunas</span>
                                                    @else
                                                        <span class="badge badge-warning">{{ ucfirst($transaction->payment_status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Metode</th>
                                                <td><span class="badge badge-light text-uppercase">{{ $transaction->payment_method ?? 'Cash' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Subtotal</th>
                                                <td>Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kode Voucher</th>
                                                <td>{{ $transaction->voucher_code ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Potongan Diskon</th>
                                                <td class="text-danger">- Rp {{ number_format($transaction->discount ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <th>Total Tagihan</th>
                                                <td class="font-weight-bold text-primary">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                            </tr>
                                            @php
                                                $totalPaidActual = $transaction->payments->sum('amount');
                                                $isPaidStatus = $transaction->payment_status === 'paid';
                                                $remainingForAction = $transaction->total_amount - $totalPaidActual;
                                                
                                                // If paid, show full amount as paid and 0 remaining for UI purposes
                                                $displayPaid = $isPaidStatus ? $transaction->total_amount : $totalPaidActual;
                                                $displayRemaining = $isPaidStatus ? 0 : $remainingForAction;
                                            @endphp
                                            <tr>
                                                <th>Total Terbayar</th>
                                                <td class="text-success font-weight-bold">Rp {{ number_format($displayPaid, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <th>Sisa Tagihan</th>
                                                <td class="{{ $displayRemaining > 0 ? 'text-danger font-weight-bold h6' : 'text-success font-weight-bold' }}">
                                                    {{ $displayRemaining > 0 ? 'Rp ' . number_format($displayRemaining, 0, ',', '.') : 'LUNAS' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="card card-dark border shadow-sm mt-4">
                                    <div class="card-header">
                                        <h4><i class="fas fa-truck"></i> Pengiriman & Trace</h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped mb-0">
                                            <tr>
                                                <th width="40%">Tipe</th>
                                                <td><span class="badge badge-secondary text-uppercase">{{ $transaction->delivery_type ?? 'Pickup' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Deskripsi</th>
                                                <td>{{ $transaction->delivery_desc ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Midtrans Order</th>
                                                <td><small class="text-muted">{{ $transaction->midtrans_order_id ?? '-' }}</small></td>
                                            </tr>
                                            <tr>
                                                <th>Midtrans Trx ID</th>
                                                <td><small class="text-muted">{{ $transaction->midtrans_transaction_id ?? '-' }}</small></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-info border shadow-sm">
                                    <div class="card-header">
                                        <h4><i class="fas fa-history"></i> Riwayat Pembayaran</h4>
                                        @if($isPaidStatus)
                                            <div class="card-header-action">
                                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#quickUploadModal">
                                                    <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                                                </button>
                                            </div>
                                        @else
                                            @if($remainingForAction > 0)
                                                <div class="card-header-action">
                                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#receiptModal">
                                                        <i class="fas fa-plus"></i> Tambah Pembayaran
                                                    </button>
                                                    @if($transaction->payment_status === 'credit')
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#settleModal">
                                                            <i class="fas fa-check-circle"></i> Pelunasan
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Tgl Bayar</th>
                                                        <th>Metode</th>
                                                        <th>Bank/Provider</th>
                                                        <th class="text-right">Nominal Diinput</th>
                                                        <th class="text-right">Jumlah Bayar (Real)</th>
                                                        <th class="text-center">Bukti</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($transaction->payments as $payment)
                                                        <tr>
                                                            <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : $transaction->created_at->format('d/m/Y') }}</td>
                                                            <td><span class="badge badge-light text-uppercase">{{ $payment->payment_method }}</span></td>
                                                            <td>{{ $payment->bank_name ?? '-' }}</td>
                                                            <td class="text-right">Rp {{ number_format($payment->cash_received ?? $payment->amount, 0, ',', '.') }}</td>
                                                            <td class="text-right font-weight-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                                            <td class="text-center">
                                                                @if($payment->payment_receipt)
                                                                    <a href="{{ asset($payment->payment_receipt) }}" target="_blank" class="badge badge-info">
                                                                        <i class="fas fa-image"></i> Lihat
                                                                    </a>
                                                                @else
                                                                    <button class="btn btn-sm btn-outline-primary btn-upload-receipt" 
                                                                            data-id="{{ $payment->id }}"
                                                                            data-amount="{{ number_format($payment->amount, 0, ',', '.') }}"
                                                                            data-date="{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}">
                                                                        <i class="fas fa-upload"></i> Upload
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td><span class="badge badge-success">Sukses</span></td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center py-3">Belum ada rincian riwayat pembayaran.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-5">📦 Item Transaksi</div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mt-2">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produk</th>
                                    <th>Batch</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-right">Harga Satuan</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @php $no = 1; @endphp
                                    @foreach ($transaction->items->where('parent_item_id', null) as $item)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>
                                                <div class="font-weight-bold">{{ $item->product->name }}</div>
                                                @if($item->product->merek)
                                                    <small class="text-muted">{{ $item->product->merek->name }}</small>
                                                @endif
                                            </td>
                                            <td><span class="badge badge-light">{{ $item->batch ? $item->batch->batch_no : '-' }}</span></td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Subtotal</th>
                                        <th class="text-right">Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</th>
                                    </tr>
                                    @if ($transaction->discount > 0)
                                        <tr>
                                            <th colspan="5" class="text-right text-danger">Diskon</th>
                                            <th class="text-right text-danger">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="5" class="text-right h5">Grand Total</th>
                                        <th class="text-right h5 text-primary">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Tambah Pembayaran -->
    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.transactions.upload-receipt', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="receiptModalLabel">Tambah Pembayaran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nominal Pembayaran (Rp)</label>
                            <input type="number" name="amount" class="form-control" value="{{ $remainingForAction }}" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Bayar</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Pilih Bukti (PNG, JPG, JPEG, PDF) - Opsional</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                            <small class="text-muted">Maksimal 2MB.</small>
                        </div>
                        <div class="form-group mb-0">
                            <label>Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Pembayaran DP, Cicilan #1, dll"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pelunasan -->
    <div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-labelledby="settleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.transactions.settle', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="settleModalLabel">Konfirmasi Pelunasan</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin memproses pelunasan untuk transaksi <strong>#{{ $transaction->id }}</strong>?</p>
                        <div class="alert alert-warning py-2 mb-3 shadow-sm border-0">
                            <div class="d-flex justify-content-between">
                                <span>Sisa yang harus dibayar:</span>
                                <span class="h5 mb-0 font-weight-bold text-dark">Rp {{ number_format($remainingForAction, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Upload Bukti Pelunasan (Opsional)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                            <small class="text-muted">Bukti ini akan dicatat sebagai pembayaran pelunasan.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning font-weight-bold px-4">PROSES LUNAS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Quick Upload (Lunas Saja) -->
    <div class="modal fade" id="quickUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.transactions.quick-upload-receipt', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Bukti Pembayaran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Transaksi ini sudah berstatus <strong>Lunas</strong>. Silakan upload bukti bayar untuk keperluan validasi.</p>
                        <div class="form-group mb-0">
                            <label>Pilih Bukti (PNG, JPG, JPEG, PDF)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf" required>
                            <small class="text-muted">Maksimal 2MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info">Upload Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Update Bukti Saja -->
    <div class="modal fade" id="updateReceiptModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.transactions.update-payment-receipt') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="payment_id" id="update_payment_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Bukti Pembayaran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2">
                            Melihat rincian: <strong id="update_payment_info"></strong>
                        </div>
                        <div class="form-group mb-0">
                            <label>Pilih Bukti (PNG, JPG, JPEG, PDF)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf" required>
                            <small class="text-muted">Maksimal 2MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload Bukti</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-upload-receipt').on('click', function() {
                const id = $(this).data('id');
                const amount = $(this).data('amount');
                const date = $(this).data('date');
                
                $('#update_payment_id').val(id);
                $('#update_payment_info').text('Rp' + amount + ' (' + date + ')');
                $('#updateReceiptModal').modal('show');
            });
        });
    </script>
    @endpush
@endsection