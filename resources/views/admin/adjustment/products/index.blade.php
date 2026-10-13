@extends('master')
@section('title', 'Adjustment Data - List Produk')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Adjustment Data - List Produk</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('admin') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Adjustment</div>
                    <div class="breadcrumb-item">List Produk</div>
                </div>
            </div>

            <div class="section-body">
                <div class="alert alert-warning">
                    <strong>Cara merge duplikat:</strong> klik <span class="badge badge-warning">Merge</span> pada produk yang akan
                    <strong>dihapus (source)</strong>, lalu pilih <strong>produk tujuan (target)</strong> yang dipertahankan.
                    Semua relasi (transaksi, batch, PO, penerimaan, return, movement, opname, bundle, voucher, foto, dsb)
                    dipindah ke target, netto yang sama digabung, netto yang beda dipindah utuh. Stok target disinkron ulang.
                </div>
                @if($noNetto->count())
                <div class="alert alert-danger">
                    <strong>{{ $noNetto->count() }} produk belum punya netto/varian</strong> (tidak muncul di tabel bawah):
                    <ul class="mb-2">
                        @foreach($noNetto as $nn)
                        <li>{{ $nn->merek->name ?? '-' }} — {{ $nn->name }} (ID: {{ $nn->id }})
                            @if(auth()->user()->canEdit('access_adjustment_products') || auth()->user()->canEdit('access_products'))
                            <button class="btn btn-sm btn-danger btn-make-netto ml-2" data-id="{{ $nn->id }}" data-name="{{ $nn->name }}">Buatkan Netto+Varian</button>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Produk (sesuai database)</h4>
                        <div class="card-header-form d-flex align-items-center" style="gap: 10px;">
                            <label class="mb-0 mr-2"><input type="checkbox" id="filter-duplicates"> Hanya duplikat</label>
                            <select id="filter-merek" class="form-control" style="min-width: 200px;">
                                <option value="">-- Semua Merek --</option>
                                @foreach($merek as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped" id="adjustment-products-table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th width="10px">#</th>
                                    <th>Merek</th>
                                    <th>Nama Produk</th>
                                    <th>Netto</th>
                                    <th>Harga Produk</th>
                                    <th>Stok</th>
                                    <th width="160px">Action</th>
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

    <!-- Modal Merge -->
    <div class="modal fade" id="mergeModal" tabindex="-1" role="dialog" aria-labelledby="mergeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mergeModalLabel">Merge Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Akan menghapus (source): <strong id="merge-source-name"></strong></p>
                    <div id="merge-preview" class="mb-3"><em>Memuat relasi...</em></div>
                    <div class="form-group">
                        <label>Gabung ke produk tujuan (target, dipertahankan):</label>
                        <select id="merge-target" class="form-control" style="width:100%;"></select>
                    </div>
                    <div class="alert alert-danger mb-0">
                        Tindakan ini tidak bisa dibatalkan. Pastikan produk tujuan sudah benar.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="btn-do-merge">Ya, Gabung & Hapus Source</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Buat Netto+Varian -->
    <div class="modal fade" id="nettoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buatkan Netto + Varian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Produk: <strong id="nv-product"></strong></p>
                    <div class="form-row">
                        <div class="form-group col-6"><label>Netto</label><input type="text" id="nv-netto" class="form-control" placeholder="mis. 100"></div>
                        <div class="form-group col-6"><label>Satuan</label><input type="text" id="nv-satuan" class="form-control" placeholder="mis. ML"></div>
                    </div>
                    <div class="form-group"><label>Harga (opsional)</label><input type="number" id="nv-price" class="form-control" min="0" value="0"></div>
                    <small class="text-muted">Nama varian + SKU dibuat otomatis (Merek-Kategori-Kode-Netto).</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btn-do-netto">Buatkan</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#adjustment-products-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.adjustment.products.all') }}",
                    type: "GET",
                    data: function(d) {
                        d.merek_id = $('#filter-merek').val();
                        d.only_duplicates = $('#filter-duplicates').is(':checked') ? 1 : 0;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'merek_name', name: 'm.name' },
                    { data: 'nama_produk', name: 'p.name' },
                    { data: 'netto', name: 'pn.netto_value' },
                    { data: 'harga', name: 'harga', orderable: false, searchable: false },
                    { data: 'stok', name: 'stok', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[2, 'asc']]
            });

            $('#filter-merek').on('change', function() { table.ajax.reload(); });
            $('#filter-duplicates').on('change', function() { table.ajax.reload(); });

            var sourceId = null;

            $('#adjustment-products-table').on('click', '.btn-merge', function() {
                sourceId = $(this).data('id');
                var sourceName = $(this).data('name');
                $('#merge-source-name').text('#' + sourceId + ' ' + sourceName);
                $('#merge-preview').html('<em>Memuat relasi...</em>');
                $('#merge-target').empty();
                $('#mergeModal').modal('show');

                $.get("{{ url('admin/adjustment/products') }}/" + sourceId + "/relations", function(res) {
                    if (res.status === 'success') {
                        var rows = Object.entries(res.counts)
                            .filter(([k, v]) => v > 0)
                            .map(([k, v]) => '<li>' + k + ': <strong>' + v + '</strong></li>')
                            .join('');
                        var nettoRows = (res.nettos || []).map(function(n) {
                            return '<li>' + $('<div>').text(n.netto).html() + ' <small class="text-muted">' + $('<div>').text((n.skus || []).join(', ')).html() + '</small></li>';
                        }).join('');
                        $('#merge-preview').html(
                            (nettoRows ? '<p class="mb-1">Netto source:</p><ul>' + nettoRows + '</ul>' : '') +
                            '<p>Total relasi yang akan dipindah: <strong>' + res.total + '</strong></p>' +
                            (rows ? '<ul>' + rows + '</ul>' : '<p class="text-muted">Tidak ada relasi (aman dihapus langsung).</p>')
                        );
                    }
                });

                $('#merge-target').select2({
                    dropdownParent: $('#mergeModal'),
                    placeholder: 'Ketik nama / kode produk tujuan...',
                    minimumInputLength: 1,
                    ajax: {
                        url: "{{ route('admin.adjustment.products.search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return { q: params.term, except_id: sourceId };
                        },
                        processResults: function(data) {
                            return { results: data };
                        }
                    }
                });
            });

            $('#btn-do-merge').on('click', function() {
                var targetId = $('#merge-target').val();
                if (!targetId) {
                    Swal.fire('Peringatan', 'Pilih dulu produk tujuan.', 'warning');
                    return;
                }
                Swal.fire({
                    title: 'Yakin merge?',
                    text: 'Relasi source dipindah ke target, lalu source dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f0ad4e',
                    confirmButtonText: 'Ya, gabung!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.adjustment.products.merge') }}",
                        type: 'POST',
                        data: { source_id: sourceId, target_id: targetId, _token: "{{ csrf_token() }}" },
                        beforeSend: function() { $.LoadingOverlay('show'); },
                        complete: function() { $.LoadingOverlay('hide'); },
                        success: function(res) {
                            $('#mergeModal').modal('hide');
                            Swal.fire('Berhasil', res.message, 'success').then(() => table.ajax.reload());
                        },
                        error: function(err) {
                            Swal.fire('Gagal', err.responseJSON?.message || 'Gagal merge produk', 'error');
                        }
                    });
                });
            });
            // ---- Buatkan Netto+Varian ----
            var nvProductId = null;
            $('.btn-make-netto').on('click', function() {
                nvProductId = $(this).data('id');
                $('#nv-product').text('#' + nvProductId + ' ' + $(this).data('name'));
                $('#nv-netto').val(''); $('#nv-satuan').val(''); $('#nv-price').val(0);
                $('#nettoModal').modal('show');
            });
            $('#btn-do-netto').on('click', function() {
                if (!$('#nv-netto').val().trim()) {
                    Swal.fire('Peringatan', 'Isi dulu nilai netto.', 'warning');
                    return;
                }
                $.ajax({
                    url: "{{ route('admin.adjustment.netto.create') }}",
                    type: 'POST',
                    data: { product_id: nvProductId, netto: $('#nv-netto').val(), satuan: $('#nv-satuan').val(), price: $('#nv-price').val(), _token: "{{ csrf_token() }}" },
                    beforeSend: function() { $.LoadingOverlay('show'); },
                    complete: function() { $.LoadingOverlay('hide'); },
                    success: function(res) {
                        $('#nettoModal').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    },
                    error: function(err) {
                        Swal.fire('Gagal', err.responseJSON?.message || 'Gagal membuat netto', 'error');
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
