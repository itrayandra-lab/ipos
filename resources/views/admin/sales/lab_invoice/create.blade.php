@extends('master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Buat Invoice Kelas Formulasi</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.lab_invoices.index') }}">Invoice Lab</a></div>
                <div class="breadcrumb-item active">Buat Baru</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-8">
                    <div class="card">
                        <form action="{{ route('admin.sales.lab_invoices.store') }}" method="POST">
                            @csrf
                            <div class="card-header">
                                <h4>Form Pendaftaran Kelas Formulasi Lab</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Customer (Pilih jika sudah terdaftar)</label>
                                    <select name="customer_id" class="form-control select2">
                                        <option value="">-- Sertakan Nama Manual di Bawah Jika Tidak Ada --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nama Lengkap Peserta (Jika bukan customer terdaftar)</label>
                                    <input type="text" name="customer_name" class="form-control" placeholder="Masukkan nama peserta">
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label>Nama Kelas / Kegiatan</label>
                                    <input type="text" name="class_name" class="form-control" required placeholder="Contoh: Kelas Formulasi Serum Antiging">
                                </div>
                                <div class="form-group">
                                    <label>Biaya Pendaftaran (Rp)</label>
                                    <input type="number" name="amount" class="form-control" required placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label>Catatan Tambahan</label>
                                    <textarea name="notes" class="form-control" style="height: 100px;"></textarea>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">Simpan & Terbitkan Invoice</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
