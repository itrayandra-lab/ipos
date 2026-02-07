@extends('master')

@section('title', 'Manage Batches - ' . $product->name)

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Manage Batches: {{ $product->name }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="/admin/manage-master/products">Products</a></div>
                <div class="breadcrumb-item active">Batches</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Batch Produk: {{ $product->name }}</h2>
            <p class="section-lead">Kelola batch, tanggal kadaluarsa, dan stok masuk di sini.</p>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Batch</h4>
                            <div class="card-header-action">
                                <a href="{{ url('admin/manage-master/products') }}" class="btn btn-warning mr-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBatchModal">
                                    Tambah Batch Baru
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(session('message'))
                                <div class="alert alert-success">{{ session('message') }}</div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>No. Batch</th>
                                            <th>Tgl Kadaluarsa</th>
                                            <th>Harga Beli (HPP)</th>
                                            <th>Qty Masuk</th>
                                            <th>Stok Sekarang</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($batches as $batch)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $batch->batch_no }}</td>
                                            <td>{{ $batch->expiry_date->format('d M Y') }}</td>
                                            <td>Rp{{ number_format($batch->buy_price, 0, ',', '.') }}</td>
                                            <td>{{ $batch->qty }}</td>
                                            <td>
                                                <span class="badge badge-{{ $batch->current_stock <= 5 ? 'danger' : 'success' }}">
                                                    {{ $batch->current_stock }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info edit-batch" 
                                                    data-id="{{ $batch->id }}"
                                                    data-no="{{ $batch->batch_no }}"
                                                    data-expiry="{{ $batch->expiry_date->format('Y-m-d') }}"
                                                    data-qty="{{ $batch->qty }}"
                                                    data-buy="{{ $batch->buy_price }}">Edit</button>
                                                <button class="btn btn-sm btn-danger delete-batch" data-id="{{ $batch->id }}">Hapus</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Batch Modal -->
<div class="modal fade" id="addBatchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.batches.store') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Batch Baru</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nomor Batch</label>
                    <input type="text" name="batch_no" class="form-control" required placeholder="Contoh: BATCH-001">
                </div>
                <div class="form-group">
                    <label>Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli (HPP Satuan)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="number" name="buy_price" class="form-control" required min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Kuantitas Masuk</label>
                    <input type="number" name="qty" class="form-control" required min="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Batch</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Batch Modal (Simple Opname) -->
<div class="modal fade" id="editBatchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.batches.update') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="id" id="edit-batch-id">
            <div class="modal-header">
                <h5 class="modal-title">Edit Batch / Stock Opname</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nomor Batch</label>
                    <input type="text" name="batch_no" id="edit-batch-no" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" id="edit-batch-expiry" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga Beli (HPP Satuan)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="number" name="buy_price" id="edit-batch-buy" class="form-control" required min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Kuantitas Masuk (Basis total disesuaikan)</label>
                    <input type="number" name="qty" id="edit-batch-qty" class="form-control" required min="0">
                    <small class="text-muted">Gunakan ini untuk Stock Opname hasil perhitungan fisik total masuk.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $('.edit-batch').on('click', function() {
        $('#edit-batch-id').val($(this).data('id'));
        $('#edit-batch-no').val($(this).data('no'));
        $('#edit-batch-expiry').val($(this).data('expiry'));
        $('#edit-batch-qty').val($(this).data('qty'));
        $('#edit-batch-buy').val($(this).data('buy'));
        $('#editBatchModal').modal('show');
    });

    $('.delete-batch').on('click', function() {
        if(confirm('Hapus batch ini? Tindakan ini tidak dapat dibatalkan.')) {
            let id = $(this).data('id');
            $.ajax({
                url: '{{ route('admin.batches.delete') }}',
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}', id: id },
                success: function(res) {
                    location.reload();
                }
            });
        }
    });
</script>
@endpush
