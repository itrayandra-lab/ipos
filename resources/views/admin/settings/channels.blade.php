@extends('master')

@section('title', 'Saluran Penjualan')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Saluran Penjualan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Saluran Penjualan</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Kelola Saluran</h2>
            <p class="section-lead">Daftar saluran penjualan (Marketplace, Mitra, Toko Online) untuk pencatatan transaksi.</p>

            @if(session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <div class="mb-4">
                <button class="btn btn-primary" data-toggle="modal" data-target="#createChannelModal">
                    <i class="fas fa-plus"></i> Tambah Saluran Baru
                </button>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Saluran</th>
                                            <th>Slug</th>
                                            <th class="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($channels as $index => $channel)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <form action="{{ route('admin.settings.channels.update') }}" method="POST" class="d-flex align-items-center">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $channel->id }}">
                                                    <input type="text" name="name" class="form-control form-control-sm mr-2" value="{{ $channel->name }}" required>
                                                    <button type="submit" class="btn btn-sm btn-info" title="Simpan Nama">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td><code>{{ $channel->slug }}</code></td>
                                            <td class="text-right">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $channel->id }}, '{{ $channel->name }}')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
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

<!-- Modal Create -->
<div class="modal fade" id="createChannelModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.settings.channels.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Saluran Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Saluran</label>
                    <input type="text" name="name" class="form-control" required placeholder="Contoh: Tokopedia, WhatsApp, Mitra A">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Saluran</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete Hidden -->
<form id="deleteForm" action="{{ route('admin.settings.channels.delete') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
    function confirmDelete(id, name) {
        if(confirm('Apakah Anda yakin ingin menghapus saluran ' + name + '?')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endsection
