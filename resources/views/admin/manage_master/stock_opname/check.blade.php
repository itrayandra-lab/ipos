@extends('master')
@section('title', 'Cek Selisih SO')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Cek Selisih SO vs Sistem</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Cek Selisih SO</div>
            </div>
        </div>

        <div class="section-body">
            @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="form-inline">
                        @csrf
                        <input type="file" name="file" class="form-control-file mr-3" accept=".xlsx,.xls,.csv" required>
                        <button class="btn btn-primary"><i class="fas fa-search mr-1"></i> Cek</button>
                        <small class="text-muted ml-3">Hanya membandingkan, tidak mengubah stok. Stok sistem = total semua gudang per produk + no batch.</small>
                    </form>
                </div>
            </div>

            @if($result)
            @php $s = $result['summary']; @endphp
            <div class="alert alert-info">
                File <b>{{ $fileName }}</b>: {{ $s['so_rows'] }} baris SO.
                Cocok <b>{{ $s['matched'] }}</b> (selisih: <b>{{ $s['with_diff'] }}</b>),
                ada di sistem tapi tidak di SO <b>{{ $s['not_in_so'] }}</b> ({{ $s['not_in_so_qty'] }} pcs),
                SO tidak cocok <b>{{ $s['so_unmatched'] }}</b> ({{ $s['so_unmatched_qty'] }} pcs).
                Total sistem (baris cocok) {{ $s['total_system_matched'] }} vs SO {{ $s['total_so_matched'] }}.
            </div>

            <div class="card">
                <div class="card-header"><h4>1. Cocok: stok sistem vs SO ({{ $s['matched'] }})</h4>
                    <div class="card-header-action">
                        <label class="mb-0"><input type="checkbox" id="onlyDiff"> Hanya yang selisih</label>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped" id="tblMatched">
                        <thead><tr><th>Produk</th><th>No Batch</th><th>Exp</th><th class="text-right">Stok Sistem</th><th class="text-right">Stok SO</th><th class="text-right">Selisih (SO - Sistem)</th><th>Ket</th></tr></thead>
                        <tbody>
                        @foreach($result['matched'] as $r)
                            <tr data-diff="{{ $r['diff'] != 0 ? 1 : 0 }}">
                                <td>{{ $r['name'] }}</td>
                                <td>{{ $r['batch_no'] }}</td>
                                <td>{{ $r['expiry'] }}</td>
                                <td class="text-right">{{ $r['system_qty'] }}</td>
                                <td class="text-right">{{ $r['so_qty'] }}</td>
                                <td class="text-right font-weight-bold {{ $r['diff'] < 0 ? 'text-danger' : ($r['diff'] > 0 ? 'text-success' : '') }}">{{ $r['diff'] > 0 ? '+' : '' }}{{ $r['diff'] }}</td>
                                <td>{{ $r['note'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4>2. Ada di sistem, tidak ada di SO ({{ $s['not_in_so'] }})</h4></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Produk</th><th>No Batch</th><th>Exp</th><th class="text-right">Stok Sistem</th><th>Rincian Gudang</th><th class="text-right">Nilai HPP</th></tr></thead>
                        <tbody>
                        @forelse($result['notInSo'] as $r)
                            <tr>
                                <td>{{ $r['name'] }}</td>
                                <td>{{ $r['batch_no'] }}</td>
                                <td>{{ $r['expiry'] }}</td>
                                <td class="text-right">{{ $r['qty'] }}</td>
                                <td>@foreach($r['warehouses'] as $w => $q){{ $w }}: {{ $q }}@if(!$loop->last), @endif @endforeach</td>
                                <td class="text-right">{{ $r['hpp_value'] ? number_format($r['hpp_value'], 0, ',', '.') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4>3. Ada di SO, tidak cocok dengan sistem ({{ $s['so_unmatched'] }})</h4></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Baris</th><th>Merek</th><th>Nama di SO</th><th>Netto</th><th>No Batch</th><th>Exp</th><th class="text-right">Stok SO</th><th>Kandidat produk sistem</th></tr></thead>
                        <tbody>
                        @forelse($result['soUnmatched'] as $r)
                            <tr>
                                <td>{{ $r['line'] }}</td>
                                <td>{{ $r['brand'] }}</td>
                                <td>{{ $r['name'] }}</td>
                                <td>{{ $r['netto'] }}</td>
                                <td>{{ $r['batch'] }}</td>
                                <td>{{ $r['exp'] }}</td>
                                <td class="text-right">{{ $r['qty'] }}</td>
                                <td>{{ $r['candidates'] ? implode(' | ', $r['candidates']) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Semua baris SO cocok</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $('#onlyDiff').on('change', function () {
        var only = this.checked;
        $('#tblMatched tbody tr').each(function () {
            $(this).toggle(!only || $(this).data('diff') == 1);
        });
    });
</script>
@endpush
