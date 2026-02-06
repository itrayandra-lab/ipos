@extends('master')
@section('title', 'Kelola Komisi Produk')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Kelola Komisi Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">Master Data</div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.affiliates.index') }}">Affiliate Users</a></div>
                    <div class="breadcrumb-item">Komisi Produk</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Komisi Spesifik: {{ $affiliate->name }}</h2>
                <p class="section-lead">Atur tarif komisi khusus untuk produk tertentu. Jika tidak diatur, akan menggunakan tarif default ({{ $affiliate->fee_value }} {{ $affiliate->fee_method == 'percent' ? '%' : 'Rupiah' }}).</p>

                <div class="card">
                    <div class="card-header">
                        <h4>Tambah Komisi Produk</h4>
                    </div>
                    <div class="card-body">
                        <form id="addForm">
                            @csrf
                            <input type="hidden" name="affiliate_id" value="{{ $affiliate->id }}">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Cari Produk</label>
                                        <select id="product_id" name="product_id" class="form-control" style="width: 100%;"></select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Metode Fee</label>
                                        <select name="fee_method" class="form-control">
                                            <option value="percent">Persentase (%)</option>
                                            <option value="nominal">Nominal (Rp)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nilai Fee</label>
                                        <input type="number" name="fee_value" class="form-control" required placeholder="Contoh: 10 atau 5000">
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-center pt-3">
                                    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Komisi Spesifik</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="commissionTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produk</th>
                                    <th>Metode</th>
                                    <th>Nilai</th>
                                    <th>Action</th>
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

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Select2 Product Search
            $('#product_id').select2({
                placeholder: 'Cari Produk...',
                ajax: {
                    url: '{{ route("admin.pos.products") }}', // Reuse POS search
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name + ' (Stok: ' + item.batches.reduce((a, b) => a + b.qty, 0) + ')',
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            var table = $('#commissionTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.affiliates.commissions.data', $affiliate->id) }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'product.name', name: 'product.name' },
                    { data: 'fee_method', name: 'fee_method' },
                    { data: 'fee_value', name: 'fee_value' },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false,
                        render: function(data, type, row) {
                            return `<button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}">Hapus</button>`;
                        }
                    }
                ]
            });

            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.affiliates.commissions.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $.LoadingOverlay("show");
                    },
                    success: function(resp) {
                        $.LoadingOverlay("hide");
                        if(resp.status) {
                            table.ajax.reload();
                            $('#addForm')[0].reset();
                            $('#product_id').val(null).trigger('change');
                            iziToast.success({ title: 'Berhasil', message: resp.message, position: 'topRight' });
                        } else {
                            iziToast.error({ title: 'Gagal', message: resp.message, position: 'topRight' });
                        }
                    },
                    error: function(err) {
                        $.LoadingOverlay("hide");
                        iziToast.error({ title: 'Error', message: err.responseJSON?.message || 'Terjadi kesalahan sistem', position: 'topRight' });
                    }
                });
            });

            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                swal({
                    title: "Hapus Komisi?",
                    text: "Data akan dihapus permanen",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: "{{ route('admin.affiliates.commissions.delete') }}",
                            method: "DELETE",
                            data: { id: id, _token: "{{ csrf_token() }}" },
                            success: function(resp) {
                                if(resp.status) {
                                    table.ajax.reload();
                                    iziToast.success({ title: 'Berhasil', message: resp.message, position: 'topRight' });
                                } else {
                                    iziToast.error({ title: 'Gagal', message: resp.message, position: 'topRight' });
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
