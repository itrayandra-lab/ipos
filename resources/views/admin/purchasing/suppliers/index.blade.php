@extends('master')

@section('title', 'Supplier Management')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Supplier</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Pembelian</a></div>
                <div class="breadcrumb-item">Supplier</div>
            </div>
            <div class="section-header-button">
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add">
                    <i class="fas fa-plus"></i> Tambah Supplier
                </button>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Daftar Supplier</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-supplier">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode</th>
                                            <th>Nama Supplier</th>
                                            <th>Contact Person</th>
                                            <th>Telepon</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Add -->
<div class="modal fade" id="modal-add" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Supplier Baru</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-add">
                @csrf
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="supplierTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">Informasi Umum</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab">Alamat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="bank-tab" data-toggle="tab" href="#bank" role="tab">Informasi Bank</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="supplierTabContent">
                        <div class="tab-pane fade show active py-3" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Supplier <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Contact Person</label>
                                        <input type="text" name="contact_person" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Telepon</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Pajak</label>
                                        <select name="tax_status" class="form-control">
                                            <option value="Non-PKP">Non-PKP</option>
                                            <option value="PKP">PKP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NPWP</label>
                                        <input type="text" name="npwp" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Payment Terms</label>
                                <input type="text" name="payment_terms" class="form-control" placeholder="Contoh: Net 30, COD">
                            </div>
                        </div>
                        <div class="tab-pane fade py-3" id="address" role="tabpanel">
                            <div class="form-group">
                                <label>Alamat Lengkap</label>
                                <textarea name="address" class="form-control" style="height: 100px;"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kota</label>
                                        <input type="text" name="city" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Provinsi</label>
                                        <input type="text" name="province" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kode Pos</label>
                                        <input type="text" name="postal_code" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade py-3" id="bank" role="tabpanel">
                            <div class="form-group">
                                <label>Nama Bank</label>
                                <input type="text" name="bank_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Nomor Rekening</label>
                                <input type="text" name="account_number" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Nama Pemegang Rekening</label>
                                <input type="text" name="account_holder_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Catatan Tambahan</label>
                                <textarea name="notes" class="form-control" style="height: 80px;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="form-edit">
                @csrf
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="supplierTabEdit" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab-edit" data-toggle="tab" href="#general-edit" role="tab">Informasi Umum</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="address-tab-edit" data-toggle="tab" href="#address-edit" role="tab">Alamat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="bank-tab-edit" data-toggle="tab" href="#bank-edit" role="tab">Informasi Bank</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="supplierTabContentEdit">
                        <div class="tab-pane fade show active py-3" id="general-edit" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Supplier <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="edit-name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Contact Person</label>
                                        <input type="text" name="contact_person" id="edit-contact-person" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" id="edit-email" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Telepon</label>
                                        <input type="text" name="phone" id="edit-phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Pajak</label>
                                        <select name="tax_status" id="edit-tax-status" class="form-control">
                                            <option value="Non-PKP">Non-PKP</option>
                                            <option value="PKP">PKP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NPWP</label>
                                        <input type="text" name="npwp" id="edit-npwp" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Terms</label>
                                        <input type="text" name="payment_terms" id="edit-payment-terms" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" id="edit-status" class="form-control">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade py-3" id="address-edit" role="tabpanel">
                            <div class="form-group">
                                <label>Alamat Lengkap</label>
                                <textarea name="address" id="edit-address" class="form-control" style="height: 100px;"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kota</label>
                                        <input type="text" name="city" id="edit-city" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Provinsi</label>
                                        <input type="text" name="province" id="edit-province" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kode Pos</label>
                                        <input type="text" name="postal_code" id="edit-postal-code" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade py-3" id="bank-edit" role="tabpanel">
                            <div class="form-group">
                                <label>Nama Bank</label>
                                <input type="text" name="bank_name" id="edit-bank-name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Nomor Rekening</label>
                                <input type="text" name="account_number" id="edit-account-number" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Nama Pemegang Rekening</label>
                                <input type="text" name="account_holder_name" id="edit-account-holder-name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Catatan Tambahan</label>
                                <textarea name="notes" id="edit-notes" class="form-control" style="height: 80px;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table;

    $(document).ready(function() {
        table = $('#table-supplier').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.purchasing.suppliers.getall') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'contact_person', name: 'contact_person' },
                { data: 'phone', name: 'phone' },
                { data: 'email', name: 'email' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        let badge = data === 'active' ? 'success' : 'danger';
                        return `<span class="badge badge-${badge}">${data.toUpperCase()}</span>`;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Add Supplier
        $('#form-add').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');

            btn.addClass('btn-progress').attr('disabled', true);

            $.ajax({
                url: "{{ route('admin.purchasing.suppliers.create') }}",
                method: "POST",
                data: form.serialize(),
                success: function(res) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    if (res.status === 'success') {
                        $('#modal-add').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                    }
                },
                error: function(err) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    iziToast.error({ title: 'Error', message: err.responseJSON.message || 'Terjadi kesalahan', position: 'topRight' });
                }
            });
        });

        // Edit Supplier
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $.ajax({
                url: "{{ route('admin.purchasing.suppliers.get') }}",
                method: "POST",
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function(res) {
                    $('#edit-id').val(res.id);
                    $('#edit-name').val(res.name);
                    $('#edit-contact-person').val(res.contact_person);
                    $('#edit-email').val(res.email);
                    $('#edit-phone').val(res.phone);
                    $('#edit-tax-status').val(res.tax_status);
                    $('#edit-npwp').val(res.npwp);
                    $('#edit-payment-terms').val(res.payment_terms);
                    $('#edit-status').val(res.status);
                    $('#edit-address').val(res.address);
                    $('#edit-city').val(res.city);
                    $('#edit-province').val(res.province);
                    $('#edit-postal-code').val(res.postal_code);
                    $('#edit-bank-name').val(res.bank_name);
                    $('#edit-account-number').val(res.account_number);
                    $('#edit-account-holder-name').val(res.account_holder_name);
                    $('#edit-notes').val(res.notes);
                    $('#modal-edit').modal('show');
                }
            });
        });

        $('#form-edit').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');

            btn.addClass('btn-progress').attr('disabled', true);

            $.ajax({
                url: "{{ route('admin.purchasing.suppliers.update') }}",
                method: "POST",
                data: form.serialize(),
                success: function(res) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    if (res.status === 'success') {
                        $('#modal-edit').modal('hide');
                        table.ajax.reload();
                        iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                    }
                },
                error: function(err) {
                    btn.removeClass('btn-progress').attr('disabled', false);
                    iziToast.error({ title: 'Error', message: err.responseJSON.message || 'Terjadi kesalahan', position: 'topRight' });
                }
            });
        });

        // Delete Supplier
        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            swal({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus supplier ini?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('admin.purchasing.suppliers.delete') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", id: id },
                        success: function(res) {
                            if (res.status === 'success') {
                                table.ajax.reload();
                                iziToast.success({ title: 'Berhasil', message: res.message, position: 'topRight' });
                            }
                        },
                        error: function(err) {
                            iziToast.error({ title: 'Error', message: err.responseJSON.message || 'Terjadi kesalahan', position: 'topRight' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
