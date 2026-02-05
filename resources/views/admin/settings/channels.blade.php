@extends('master')

@section('title', 'Marketplace Channel Settings')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pengaturan Marketplace</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Channel Settings</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Biaya & Margin per Channel</h2>
            <p class="section-lead">Atur persentase atau nominal biaya dan target margin untuk setiap kanal penjualan.</p>

            <div class="row">
                @foreach($channels as $channel)
                <div class="col-12 col-md-6">
                    <form action="{{ route('admin.settings.channels.update') }}" method="POST" class="card">
                        @csrf
                        <input type="hidden" name="id" value="{{ $channel->id }}">
                        <div class="card-header">
                            <h4>{{ $channel->name }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Tipe Margin</label>
                                        <select name="margin_type" class="form-control">
                                            <option value="percentage" {{ $channel->margin_type == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                            <option value="fixed" {{ $channel->margin_type == 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Nilai Margin</label>
                                        <input type="number" name="margin_value" class="form-control" value="{{ $channel->margin_value }}" required min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Tipe Biaya Admin/Fee</label>
                                        <select name="fee_type" class="form-control">
                                            <option value="percentage" {{ $channel->fee_type == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                            <option value="fixed" {{ $channel->fee_type == 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Nilai Biaya Admin</label>
                                        <input type="number" name="fee_value" class="form-control" value="{{ $channel->fee_value }}" required min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Biaya Operasional Tetap</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                            <input type="number" name="fixed_cost" class="form-control" value="{{ $channel->fixed_cost }}" required min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Subsidi Ongkir (Nominal)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                            <input type="number" name="shipping_subsidy" class="form-control" value="{{ $channel->shipping_subsidy }}" required min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan {{ $channel->name }}</button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection
