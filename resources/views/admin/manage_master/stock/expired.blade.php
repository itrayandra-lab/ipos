@extends('master')
@section('title', 'Stok Kadaluarsa - ')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Stok Kadaluarsa</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="/admin/manage-master/stock">Stok</a></div>
                <div class="breadcrumb-item active">Kadaluarsa</div>
            </div>
            <div class="section-header-button">
                <a href="/admin/manage-master/stock" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Stok
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Produk dengan Stok Kadaluarsa</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-expired">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Produk</th>
                                            <th>Variant</th>
                                            <th>Batch</th>
                                            <th>Gudang</th>
                                            <th class="text-right">Stok</th>
                                            <th>Tgl Kadaluarsa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($batches as $batch)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if($batch->product)
                                                <strong>{{ $batch->product->merek->name ?? '' }} {{ $batch->product->name }}</strong>
                                                @else
                                                <span class="text-muted">Produk dihapus</span>
                                                @endif
                                            </td>
                                            <td>{{ $batch->variant->variant_name ?? '-' }}</td>
                                            <td><code>{{ $batch->batch_number ?? '-' }}</code></td>
                                            <td>{{ $batch->warehouse->name ?? '-' }}</td>
                                            <td class="text-right font-weight-bold text-danger">{{ number_format($batch->qty, 0) }}</td>
                                            <td>
                                                <span class="badge badge-danger">{{ $batch->expiry_date ? $batch->expiry_date->format('d M Y') : '-' }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-check-circle mr-1" style="color:#16a34a;"></i> Tidak ada stok kadaluarsa
                                            </td>
                                        </tr>
                                        @endforelse
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
@endsection
