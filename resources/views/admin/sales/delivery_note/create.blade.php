@extends('master')

@push('styles')
<style>
    .select2-container .select2-selection--single {
        height: auto !important;
        min-height: 38px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        white-space: normal !important;
        word-wrap: break-word !important;
        line-height: 1.4 !important;
        padding: 8px 12px !important;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Buat Surat Jalan Manual</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ url('admin') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></div>
                <div class="breadcrumb-item active">Buat Baru</div>
            </div>
        </div>

        <div class="section-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.sales.delivery_notes.store') }}" method="POST">
                @csrf
                
                <div class="card">
                    <div class="card-header">
                        <h4>Info Surat Jalan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Metode Pengiriman</label>
                                    <select name="delivery_type" class="form-control" required>
                                        <option value="pickup">Pickup</option>
                                        <option value="kurir">Kurir</option>
                                        <option value="ekspedisi">Ekspedisi</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <div id="customer-select-wrapper" style="display:none;">
                                        <select name="customer_id" class="form-control select2" id="customer-select" style="width: 100%;">
                                            <option value="">-- Pilih Customer --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}">
                                                    {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="customer-text-wrapper">
                                        <input type="text" name="customer_name" id="customer-name" class="form-control" placeholder="Nama manual" required>
                                    </div>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" id="is_registered_customer">
                                        <label class="form-check-label" for="is_registered_customer">Customer Terdaftar</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No. Telepon</label>
                                    <input type="text" name="customer_phone" id="customer-phone" class="form-control" placeholder="08xx">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Catatan / Keterangan</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Barang</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 70%">Nama Barang (Batch)</th>
                                        <th style="width: 20%">Qty</th>
                                        <th style="width: 10%"></th>
                                    </tr>
                                </thead>
                                <tbody id="item-rows">
                                    {{-- Rows added via JS --}}
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>
                </div>

                <div class="mb-5 text-right">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                        <i class="fas fa-save"></i> Simpan Surat Jalan
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
const batchData = @json($batchList);
</script>
@endsection

@push('scripts')
<script>
    let rowIndex = 0;

    function buildBatchOptions() {
        let html = '<option value="">-- Pilih Barang --</option>';
        batchData.forEach(function(b) {
            html += `<option value="${b.id}" data-stock="${b.stock}">${b.text}</option>`;
        });
        return html;
    }

    function addRow() {
        const idx = rowIndex++;
        const row = `
        <tr>
            <td>
                <select name="items[${idx}][product_batch_id]" class="form-control select2-items" required>
                    ${buildBatchOptions()}
                </select>
            </td>
            <td>
                <input type="number" name="items[${idx}][qty]" class="form-control" value="1" min="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
        $('#item-rows').append(row);
        $('.select2-items').last().select2({ width: '100%' });
    }

    $(document).ready(function() {
        addRow();

        $('#add-row').on('click', addRow);
        $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); });

        $('#is_registered_customer').on('change', function() {
            if ($(this).is(':checked')) {
                $('#customer-select-wrapper').show();
                $('#customer-text-wrapper').hide();
                $('#customer-name').removeAttr('required');
            } else {
                $('#customer-select-wrapper').hide();
                $('#customer-text-wrapper').show();
                $('#customer-name').attr('required', 'required');
            }
        });

        $('#customer-select').on('change', function() {
            const opt = $(this).find(':selected');
            if (opt.val()) {
                $('#customer-name').val(opt.data('name'));
                $('#customer-phone').val(opt.data('phone'));
            }
        });
    });
</script>
@endpush
