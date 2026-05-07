@extends('master')

@section('title', 'Edit Return Barang')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.purchasing.returns.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Edit Return Barang</h1>
        </div>

        <div class="section-body">
            <form id="form-return">
                @csrf
                @method('PUT')
                <div class="row">
                    <!-- Informasi Return (Top) -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Informasi Return #{{ $return->return_number }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Supplier</label>
                                            <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                                <option value="">Pilih Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ $return->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Gudang</label>
                                            <select name="warehouse_id" id="warehouse_id" class="form-control select2" required>
                                                <option value="">Pilih Gudang</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}" {{ $return->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tanggal Return</label>
                                            <input type="date" name="return_date" class="form-control" value="{{ $return->return_date }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Catatan</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="Alasan return secara umum...">{{ $return->notes }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item Barang (Bottom) -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Item Barang</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-primary" id="btn-add-item">
                                        <i class="fas fa-plus"></i> Tambah Item dari Stok
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-items">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th width="150">Batch No</th>
                                                <th width="100" class="text-center">Stok Tersedia</th>
                                                <th width="120" class="text-center">Qty Return</th>
                                                <th>Keterangan</th>
                                                <th width="50"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($return->items as $item)
                                                @php
                                                    $merek = $item->product->merek ? $item->product->merek->name . ' ' : '';
                                                    $netto = ($item->variant && $item->variant->netto) ? ' ' . $item->variant->netto->netto_value . ' ' . $item->variant->netto->satuan : '';
                                                    $effectiveStock = $item->batch->current_stock + $item->qty;
                                                @endphp
                                                <tr id="row-{{ $item->product_batch_id }}">
                                                    <td>
                                                        <strong>{{ $merek }}{{ $item->product->name }}{{ $netto }}</strong>
                                                        <input type="hidden" name="items[{{ $item->product_batch_id }}][product_batch_id]" value="{{ $item->product_batch_id }}">
                                                    </td>
                                                    <td><span class="badge badge-info">{{ $item->batch->batch_no }}</span></td>
                                                    <td class="text-center">{{ $effectiveStock }}</td>
                                                    <td>
                                                        <input type="number" name="items[{{ $item->product_batch_id }}][qty]" class="form-control text-center" min="1" max="{{ $effectiveStock }}" value="{{ $item->qty }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[{{ $item->product_batch_id }}][reason]" class="form-control" placeholder="Keterangan return (ex: Expired)" value="{{ $item->reason }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger btn-sm btn-remove-item" data-id="{{ $item->product_batch_id }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('admin.purchasing.returns.index') }}" class="btn btn-secondary mr-2">Batal</a>
                                <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- Modal Add Item -->
<div class="modal fade" id="modalAddItem" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Barang dari Stok Gudang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="search-product" class="form-control" placeholder="Cari Nama Produk, Merek, atau Nomor Batch...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="table-select-batch">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Batch No</th>
                                <th>Expiry</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="batch-list">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let availableBatches = [];
    let selectedItemIds = {!! json_encode($return->items->pluck('product_batch_id')->toArray()) !!}.map(id => parseInt(id));

    $(document).ready(function() {
        // Load initial batches for the selected warehouse
        loadBatches($('#warehouse_id').val());

        $('#warehouse_id').on('change', function() {
            let warehouseId = $(this).val();
            if (warehouseId) {
                $('#btn-add-item').prop('disabled', false);
                loadBatches(warehouseId);
            } else {
                $('#btn-add-item').prop('disabled', true);
            }
            // Optional: warning if items already exist when warehouse changes
            if (selectedItemIds.length > 0) {
                if(confirm('Mengubah gudang akan mereset daftar item. Lanjutkan?')) {
                    $('#table-items tbody').html('<tr id="empty-row"><td colspan="6" class="text-center">Belum ada item yang ditambahkan.</td></tr>');
                    selectedItemIds = [];
                } else {
                    // Revert selection if possible or just handle logic
                }
            }
        });

        $('#btn-add-item').on('click', function() {
            $('#search-product').val(''); 
            renderBatchList();
            $('#modalAddItem').modal('show');
        });

        $('#search-product').on('keyup', function() {
            renderBatchList($(this).val());
        });

        function loadBatches(warehouseId) {
            if (!warehouseId) return;
            $.get("{{ route('admin.purchasing.returns.get_batches') }}", { 
                warehouse_id: warehouseId,
                exclude_return_id: "{{ $return->id }}"
            }, function(data) {
                availableBatches = data;
            });
        }

        function renderBatchList(filter = '') {
            let html = '';
            let filteredBatches = availableBatches;

            if (filter) {
                let search = filter.toLowerCase();
                filteredBatches = availableBatches.filter(batch => {
                    let productName = batch.product.name.toLowerCase();
                    let merekName = batch.product.merek ? batch.product.merek.name.toLowerCase() : '';
                    let batchNo = batch.batch_no.toLowerCase();
                    let netto = batch.variant && batch.variant.netto ? (batch.variant.netto.netto_value + ' ' + batch.variant.netto.satuan).toLowerCase() : '';
                    return productName.includes(search) || merekName.includes(search) || batchNo.includes(search) || netto.includes(search);
                });
            }

            filteredBatches.forEach(batch => {
                if (!selectedItemIds.some(id => id == batch.id)) {
                    let expiry = batch.expiry_date ? new Date(batch.expiry_date).toLocaleDateString('id-ID') : '-';
                    let merek = batch.product.merek ? `${batch.product.merek.name} ` : '';
                    let netto = batch.variant && batch.variant.netto ? ` ${batch.variant.netto.netto_value} ${batch.variant.netto.satuan}` : '';
                    
                    html += `
                        <tr>
                            <td><strong>${merek}${batch.product.name}${netto}</strong></td>
                            <td><span class="badge badge-light">${batch.batch_no}</span></td>
                            <td>${expiry}</td>
                            <td class="text-center"><strong>${batch.current_stock}</strong></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm btn-select-batch" data-id="${batch.id}">
                                    <i class="fas fa-check"></i> Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                }
            });
            $('#batch-list').html(html || '<tr><td colspan="5" class="text-center">Tidak ada stok tersedia yang cocok dengan pencarian.</td></tr>');
        }

        $(document).on('click', '.btn-select-batch', function() {
            let batchId = $(this).data('id');
            let batch = availableBatches.find(b => b.id == batchId);
            
            if (batch) {
                addItemToTable(batch);
                selectedItemIds.push(batch.id);
                $('#modalAddItem').modal('hide');
            }
        });

        function addItemToTable(batch) {
            $('#empty-row').remove();
            let merek = batch.product.merek ? `${batch.product.merek.name} ` : '';
            let netto = batch.variant && batch.variant.netto ? ` ${batch.variant.netto.netto_value} ${batch.variant.netto.satuan}` : '';
            
            let html = `
                <tr id="row-${batch.id}">
                    <td>
                        <strong>${merek}${batch.product.name}${netto}</strong>
                        <input type="hidden" name="items[${batch.id}][product_batch_id]" value="${batch.id}">
                    </td>
                    <td><span class="badge badge-info">${batch.batch_no}</span></td>
                    <td class="text-center">${batch.current_stock}</td>
                    <td>
                        <input type="number" name="items[${batch.id}][qty]" class="form-control text-center" min="1" max="${batch.current_stock}" value="1" required>
                    </td>
                    <td>
                        <input type="text" name="items[${batch.id}][reason]" class="form-control" placeholder="Keterangan return (ex: Expired)">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item" data-id="${batch.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#table-items tbody').append(html);
        }

        $(document).on('click', '.btn-remove-item', function() {
            let id = parseInt($(this).data('id'));
            $(`#row-${id}`).remove();
            selectedItemIds = selectedItemIds.filter(i => i != id);
            if ($('#table-items tbody tr').length === 0) {
                $('#table-items tbody').append('<tr id="empty-row"><td colspan="6" class="text-center">Belum ada item yang ditambahkan.</td></tr>');
            }
        });

        $('#form-return').on('submit', function(e) {
            e.preventDefault();
            if (selectedItemIds.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tambahkan minimal 1 item.' });
                return;
            }

            let btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).addClass('btn-progress');

            $.ajax({
                url: "{{ route('admin.purchasing.returns.update', $return->id) }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message }).then(() => {
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
                        btn.prop('disabled', false).removeClass('btn-progress');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    btn.prop('disabled', false).removeClass('btn-progress');
                }
            });
        });
    });
</script>
@endpush
