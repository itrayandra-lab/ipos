@extends('master')

@section('title', 'Marketplace Channel Settings')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Pengaturan Marketplace (Formula Engine)</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="/admin">Dashboard</a></div>
                <div class="breadcrumb-item active">Channel Settings</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Formula Harga & Channel</h2>
            <p class="section-lead">Atur urutan perhitungan harga jual untuk setiap channel menggunakan sistem blok faktor.</p>

            <div class="mb-4">
                <button class="btn btn-primary" data-toggle="modal" data-target="#createChannelModal">
                    <i class="fas fa-plus"></i> Tambah Channel Baru
                </button>
            </div>

            <div class="row">
                @foreach($channels as $channel)
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <form action="{{ route('admin.settings.channels.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $channel->id }}">
                            
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <input type="text" name="name" class="form-control form-control-sm font-weight-bold" value="{{ $channel->name }}" style="width: auto;">
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $channel->id }}, '{{ $channel->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="card-body">
                                <div class="form-group">
                                    <label>Komponen Biaya (Urutan Eksekusi)</label>
                                    <div class="factors-container" id="factors-{{ $channel->id }}">
                                        @if($channel->factors && is_array($channel->factors))
                                            @foreach($channel->factors as $index => $factor)
                                                <div class="factor-row input-group mb-2">
                                                    <input type="text" name="factors[{{ $index }}][label]" class="form-control" placeholder="Label (ex: Margin)" value="{{ $factor['label'] }}" required>
                                                    <select name="factors[{{ $index }}][operator]" class="custom-select" style="max-width: 100px;">
                                                        <option value="multiply" {{ $factor['operator'] == 'multiply' ? 'selected' : '' }}>x (Kali)</option>
                                                        <option value="percentage" {{ $factor['operator'] == 'percentage' ? 'selected' : '' }}>+ %</option>
                                                        <option value="add" {{ $factor['operator'] == 'add' ? 'selected' : '' }}>+ (Rp)</option>
                                                    </select>
                                                    <input type="number" name="factors[{{ $index }}][value]" class="form-control" placeholder="Nilai" value="{{ $factor['value'] }}" step="any" required>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger btn-remove-factor"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addFactor({{ $channel->id }})">
                                        <i class="fas fa-plus"></i> Tambah Faktor
                                    </button>
                                </div>

                                <div class="alert alert-light mt-4">
                                    <h6 class="alert-heading">Simulasi Harga</h6>
                                    <div class="row align-items-center">
                                        <div class="col-5">
                                            <input type="number" class="form-control" id="base-price-{{ $channel->id }}" placeholder="Harga Modal" onkeyup="calculateSimulation({{ $channel->id }})">
                                        </div>
                                        <div class="col-2 text-center">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                        <div class="col-5">
                                            <span class="font-weight-bold h5 text-success" id="final-price-{{ $channel->id }}">Rp 0</span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="simulation-log-{{ $channel->id }}">
                                        Masukkan harga modal untuk melihat detail perhitungan.
                                    </small>
                                </div>
                            </div>

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
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
                <h5 class="modal-title">Tambah Channel Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Channel</label>
                    <input type="text" name="name" class="form-control" required placeholder="Contoh: Tokopedia">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Channel</button>
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
        if(confirm('Apakah Anda yakin ingin menghapus channel ' + name + '?')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    function addFactor(channelId) {
        const container = document.getElementById('factors-' + channelId);
        const index = container.children.length;
        const html = `
            <div class="factor-row input-group mb-2">
                <input type="text" name="factors[${index}][label]" class="form-control" placeholder="Label" required>
                <select name="factors[${index}][operator]" class="custom-select" style="max-width: 100px;">
                    <option value="multiply">x (Kali)</option>
                    <option value="percentage">+ %</option>
                    <option value="add">+ (Rp)</option>
                </select>
                <input type="number" name="factors[${index}][value]" class="form-control" placeholder="Nilai" step="any" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger btn-remove-factor"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    document.addEventListener('click', function(e) {
        if(e.target && e.target.closest('.btn-remove-factor')) {
            e.target.closest('.factor-row').remove();
        }
    });

    function calculateSimulation(channelId) {
        const basePrice = parseFloat(document.getElementById('base-price-' + channelId).value) || 0;
        const container = document.getElementById('factors-' + channelId);
        const logContainer = document.getElementById('simulation-log-' + channelId);
        const finalDisplay = document.getElementById('final-price-' + channelId);
        
        let currentPrice = basePrice;
        let logHtml = `<ul class="list-unstyled mb-0"><li>Modal: Rp ${basePrice.toLocaleString('id-ID')}</li>`;

        const rows = container.querySelectorAll('.factor-row');
        rows.forEach(row => {
            const label = row.querySelector('input[name*="[label]"]').value || 'Faktor';
            const operator = row.querySelector('select[name*="[operator]"]').value;
            const value = parseFloat(row.querySelector('input[name*="[value]"]').value) || 0;
            
            let change = 0;
            let symbol = '';

            if(operator === 'multiply') {
                currentPrice *= value;
                symbol = `x ${value}`;
                logHtml += `<li>${label}: ${symbol} = Rp ${Math.ceil(currentPrice).toLocaleString('id-ID')}</li>`;
            } else if(operator === 'percentage') {
                change = currentPrice * (value / 100);
                currentPrice += change;
                symbol = `+ ${value}%`;
                logHtml += `<li>${label}: ${symbol} (+ Rp ${Math.ceil(change).toLocaleString('id-ID')}) = Rp ${Math.ceil(currentPrice).toLocaleString('id-ID')}</li>`;
            } else if(operator === 'add') {
                currentPrice += value;
                symbol = `+ Rp ${value.toLocaleString('id-ID')}`;
                logHtml += `<li>${label}: ${symbol} = Rp ${Math.ceil(currentPrice).toLocaleString('id-ID')}</li>`;
            }
        });

        logHtml += `</ul>`;
        logContainer.innerHTML = logHtml;
        finalDisplay.innerText = `Rp ${Math.ceil(currentPrice).toLocaleString('id-ID')}`;
    }
</script>
@endsection
