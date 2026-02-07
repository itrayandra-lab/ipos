@extends('master')
@section('title', 'Data Voucher')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Data Voucher</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Data Voucher</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Data Voucher</h2>
                <p class="section-lead">Berikut adalah Data Voucher.</p>
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
                        <h4>Data Seluruh Voucher</h4>
                        <div class="card-header-form">
                            <a href="{{ url('admin/manage-master/voucher/create') }}" class="btn btn-primary btn-sm">
                                Tambah
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped mt-5" id="voucherTable">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Nama</th>
                                    <th>Kode</th>
                                    <th>Nilai Diskon</th>
                                    <th>Masa Berlaku</th>
                                    <th>Terpakai / Limit</th>
                                    <th>Status</th>
                                    <th width="10px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModal">Update Voucher</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="/admin/manage-master/voucher/update" method="POST" class="needs-validation" novalidate="">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" placeholder="Masukkan Nama Voucher" class="form-control" name="name" required="" id="name">
                            <div class="invalid-feedback">
                                Masukkan Nama Voucher
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Kode</label>
                            <input type="text" placeholder="Masukkan Kode Voucher" class="form-control" name="code" required="" id="code">
                            <div class="invalid-feedback">
                                Masukkan Kode Voucher
                            </div>
                        </div>
                        <div class="form-group">
                             <label>Masa Berlaku (Opsional)</label>
                             <div class="row">
                                 <div class="col-md-6">
                                     <label><small>Mulai</small></label>
                                     <input type="datetime-local" class="form-control" name="start_date" id="start_date">
                                 </div>
                                 <div class="col-md-6">
                                     <label><small>Selesai</small></label>
                                     <input type="datetime-local" class="form-control" name="end_date" id="end_date">
                                 </div>
                             </div>
                        </div>
                        <div class="form-group">
                            <label>Batas Penggunaan (Opsional)</label>
                            <input type="number" class="form-control" name="usage_limit" id="usage_limit" min="1">
                            <small class="form-text text-muted">Kosongkan jika voucher dapat digunakan tanpa batas.</small>
                        </div>
                        <div class="form-group">
                            <label class="d-block">Tipe Diskon</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="discount_type" id="edit_type_percent" value="PERCENT">
                                <label class="form-check-label" for="edit_type_percent">Persentase (%)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="discount_type" id="edit_type_nominal" value="NOMINAL">
                                <label class="form-check-label" for="edit_type_nominal">Nominal (Rp)</label>
                            </div>
                        </div>
                        <div class="form-group" id="edit_percent_wrapper">
                            <label>Persentase (%)</label>
                            <input type="number" placeholder="Masukkan Persentase Diskon" class="form-control" name="percent" id="percent" min="0" max="100">
                            <div class="invalid-feedback">
                                Masukkan Persentase Diskon (0-100)
                            </div>
                        </div>
                        <div class="form-group" id="edit_nominal_wrapper" style="display:none;">
                            <label>Nominal (Rp)</label>
                            <input type="number" placeholder="Masukkan Nominal Diskon" class="form-control" name="nominal" id="nominal" min="0">
                            <div class="invalid-feedback">
                                Masukkan Nominal Diskon
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" required="" id="status">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="NON ACTIVE">NON ACTIVE</option>
                            </select>
                            <div class="invalid-feedback">
                                Pilih Status Voucher
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Produk <small class="text-info">(Opsional - Kosongkan jika untuk semua produk)</small></label>
                            <select class="form-control select2" name="products[]" id="product_id" multiple="multiple">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name ?? $product->title }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Pilih Produk
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk multiple select di add modal dan single di update
            $('.select2').select2({
                placeholder: "Pilih Product",
                allowClear: true
            });

            $('#voucherTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('admin/manage-master/voucher/all') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'percent', name: 'percent' },
                    { data: 'validity', name: 'validity' },
                    { data: 'usage', name: 'usage' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

          
            $('#voucherTable').on('click', '.edit[data-id]', function(e) {
                e.preventDefault();
                $.ajax({
                    data: {
                        'id': $(this).data('id'),
                        '_token': "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    url: "{{ url('admin/manage-master/voucher/get') }}",
                    beforeSend: function() {
                        $.LoadingOverlay("show", {
                            image: "",
                            fontawesome: "fa fa-cog fa-spin"
                        });
                    },
                    complete: function() {
                        $.LoadingOverlay("hide");
                    },
                    success: function(data) {
                        $('#id').val(data.id);
                        $('#name').val(data.name);
                        $('#code').val(data.code);
                        
                        // Handle discount type population
                        if (data.discount_type == 'NOMINAL') {
                            $('#edit_type_nominal').prop('checked', true);
                            $('#edit_percent_wrapper').hide();
                            $('#edit_nominal_wrapper').show();
                            $('#nominal').val(data.nominal);
                            $('#percent').val('');
                        } else {
                            $('#edit_type_percent').prop('checked', true);
                            $('#edit_percent_wrapper').show();
                            $('#edit_nominal_wrapper').hide();
                            $('#percent').val(data.percent);
                            $('#nominal').val('');
                        }

                        $('#status').val(data.status);
                        $('#start_date').val(data.start_date ? data.start_date.replace(' ', 'T') : '');
                        $('#end_date').val(data.end_date ? data.end_date.replace(' ', 'T') : '');
                        $('#usage_limit').val(data.usage_limit);
                        
                        // Populate multiple products
                        var productIds = data.products.map(p => p.id);
                        $('#product_id').val(productIds).trigger('change');
                        
                        $('#updateModal').modal('show');
                    },
                    error: function(err) {
                        alert('Error: ' + err.responseText);
                        console.log(err);
                    }
                });
            });

            $('input[name="discount_type"]').change(function() {
                if ($('#edit_type_nominal').is(':checked')) {
                    $('#edit_percent_wrapper').hide();
                    $('#edit_nominal_wrapper').show();
                } else {
                    $('#edit_percent_wrapper').show();
                    $('#edit_nominal_wrapper').hide();
                }
            });

            $('#voucherTable').on('click', '.hapus[data-id]', function(e) {
                e.preventDefault();
                swal({
                    title: "Hapus Voucher?",
                    text: "Data Voucher ini akan dihapus secara permanen!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            data: {
                                'id': $(this).data('id'),
                                '_token': "{{ csrf_token() }}"
                            },
                            type: 'DELETE',
                            url: "{{ url('admin/manage-master/voucher') }}",
                            beforeSend: function() {
                                $.LoadingOverlay("show", {
                                    image: "",
                                    fontawesome: "fa fa-cog fa-spin"
                                });
                            },
                            complete: function() {
                                $.LoadingOverlay("hide");
                            },
                            success: function(data) {
                                swal(data.message).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(err) {
                                alert('Error: ' + err.responseText);
                                console.log(err);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection