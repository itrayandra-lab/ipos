@extends('master')

@push('styles')
<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        border-color: #e4e6fc !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        padding-left: 15px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .form-group label {
        font-weight: 700 !important;
        color: #34395e !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-size: 11px;
        margin-bottom: 8px;
    }
    .card-header h4 {
        color: #6777ef !important;
    }
    .custom-checkbox-wrapper {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 5px;
        border: 1px solid #e4e6fc;
    }

    /* Fixed Table Layout matching PO style */
    #items-table {
        table-layout: fixed !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }
    #items-table th, #items-table td {
        overflow: visible !important;
        vertical-align: middle !important;
        word-wrap: break-word !important;
        border-color: #f2f2f2 !important;
    }
    #items-table th {
        background-color: #ffffff !important;
        color: #34395e !important;
        border-bottom: 2px solid #eee !important;
        text-align: center;
    }
    #items-table th:nth-child(1), #items-table td:nth-child(1) { width: 50px !important; text-align: center; }
    #items-table th:nth-child(2), #items-table td:nth-child(2) { width: 300px !important; }
    #items-table th:nth-child(3), #items-table td:nth-child(3) { width: 150px !important; text-align: center; }
    #items-table th:nth-child(4), #items-table td:nth-child(4) { width: 200px !important; }
    #items-table th:nth-child(5), #items-table td:nth-child(5) { width: 50px !important; text-align: center; }
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
                
                <div class="card shadow-sm">
                    <div class="card-header border-bottom">
                        <h4><i class="fas fa-info-circle mr-2"></i> Info Surat Jalan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label><i class="fas fa-calendar-alt mr-1"></i> Tanggal</label>
                                    <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label><i class="fas fa-shipping-fast mr-1"></i> Metode Pengiriman</label>
                                    <select name="delivery_type" class="form-control selectric" required>
                                        <option value="pickup">Pickup</option>
                                        <option value="kurir">Kurir</option>
                                        <option value="ekspedisi">Ekspedisi</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="mb-0"><i class="fas fa-user-tag mr-1"></i> Nama Customer</label>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_registered_customer">
                                            <label class="custom-control-label font-weight-bold" for="is_registered_customer" style="text-transform: none; font-size: 11px; letter-spacing: 0;">Customer Terdaftar</label>
                                        </div>
                                    </div>
                                    <div id="customer-select-wrapper" style="display:none;">
                                        <select name="customer_id" class="form-control select2" id="customer-select" style="width: 100%;">
                                            <option value="">-- Pilih Customer --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address }}">
                                                    {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="customer-text-wrapper">
                                        <input type="text" name="customer_name" id="customer-name" class="form-control" placeholder="Masukkan nama customer manual" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label><i class="fas fa-phone mr-1"></i> No. Telepon</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><i class="fas fa-phone"></i></div>
                                        </div>
                                        <input type="text" name="customer_phone" id="customer-phone" class="form-control" placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label><i class="fas fa-map-marker-alt mr-1"></i> Alamat Pengiriman</label>
                                    <textarea name="delivery_address" id="delivery-address" class="form-control" rows="3" placeholder="Masukkan alamat tujuan pengiriman lengkap" style="height: 100px;"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label><i class="fas fa-sticky-note mr-1"></i> Catatan / Keterangan</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan" style="height: 100px;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header border-bottom">
                        <h4><i class="fas fa-boxes mr-2"></i> Daftar Barang</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0" id="items-table">
                                <thead class="text-uppercase" style="font-size: 11px; letter-spacing: 1px;">
                                    <tr>
                                        <th class="py-3">#</th>
                                        <th class="py-3 text-left">Nama Barang (Batch)</th>
                                        <th class="py-3">Qty</th>
                                        <th class="py-3 text-left">Keterangan</th>
                                        <th class="py-3"></th>
                                    </tr>
                                </thead>
                                <tbody id="item-rows">
                                    {{-- Rows added via JS --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary" id="add-row">
                                <i class="fas fa-plus"></i> Tambah Baris
                            </button>
                        </div>
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


@endsection

@push('scripts')
<script>
    const batchData = @json($batchList);
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
        const rowCount = $('#item-rows tr').length + 1;
        const row = `
        <tr>
            <td class="text-center font-weight-bold">${rowCount}</td>
            <td>
                <select name="items[${idx}][product_batch_id]" class="form-control select2-items" required>
                    ${buildBatchOptions()}
                </select>
            </td>
            <td>
                <input type="number" name="items[${idx}][qty]" class="form-control text-center" value="1" min="1" required>
            </td>
            <td>
                <input type="text" name="items[${idx}][description]" class="form-control" placeholder="Keterangan (opsional)">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row" title="Hapus"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
        $('#item-rows').append(row);
        $('.select2-items').last().select2({ width: '100%' });
    }

    $(document).ready(function() {
        addRow();

        $('#add-row').on('click', addRow);
        $(document).on('click', '.remove-row', function() { 
            $(this).closest('tr').remove();
            updateRowNumbers();
        });

        function updateRowNumbers() {
            $('#item-rows tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

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
                $('#delivery-address').val(opt.data('address'));
            }
        });
    });
</script>
@endpush
