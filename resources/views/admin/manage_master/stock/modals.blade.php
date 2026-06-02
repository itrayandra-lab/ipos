<!-- Modal Add -->
<div class="modal fade" id="modal-add" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Batch Stok</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-add">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Gudang <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="warehouse_id" required>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $wh->type == 'main' ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Produk <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="product_id" id="product-add" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->merek ? $product->merek->name . ' ' : '' }}{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Varian (Netto)</label>
                        <select class="form-control select2" name="product_variant_id" id="variant-add">
                            <option value="">Pilih Varian</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Batch <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="batch_no" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tgl Kadaluarsa</label>
                                <input type="date" class="form-control" name="expiry_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Qty Masuk <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="qty" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Batch</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="form-edit">
                @csrf
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>No Batch</label>
                        <input type="text" class="form-control" name="batch_no" id="edit-batch-no" required>
                    </div>
                    <div class="form-group">
                        <label>Tgl Kadaluarsa</label>
                        <input type="date" class="form-control" name="expiry_date" id="edit-expiry">
                    </div>
                    <div class="form-group">
                        <label>Qty Awal</label>
                        <input type="number" class="form-control" name="qty" id="edit-qty" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
