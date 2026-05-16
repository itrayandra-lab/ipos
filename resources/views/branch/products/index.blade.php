@extends('master')
@section('title', 'Katalog Produk')

@push('styles')
<link rel="stylesheet" href="{{ asset('dist/datatables/datatables.min.css') }}">
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Katalog Produk Pusat</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-products">
                            <thead>
                                <tr>
                                    <th width="10px">No</th>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    <th>Merek</th>
                                    <th>Kategori</th>
                                    <th>Varian & Harga Jual</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('dist/datatables/datatables.min.js') }}"></script>
<script>
    $(function() {
        $('#table-products').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('branch.products.getall') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'merek_name', name: 'merek_name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'prices', name: 'prices', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
