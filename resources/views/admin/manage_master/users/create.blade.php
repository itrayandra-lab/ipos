@extends('master')
@section('title', 'Tambah User')
@push('styles')
    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #6777ef !important;
            border: 1px solid #6777ef !important;
            color: #fff !important;
            padding: 2px 10px !important;
            margin-top: 5px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 5px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ff9800 !important;
        }
    </style>
@endpush
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah User</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ url('admin/manage-master/users') }}">Data User</a></div>
                    <div class="breadcrumb-item active">Tambah</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <form action="{{ url('admin/manage-master/users') }}" method="POST" class="needs-validation" novalidate="">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name <span class="text-danger">*</span></label>
                                        <input type="text" placeholder="Masukkan Nama" class="form-control" name="name" required="" value="{{ old('name') }}">
                                        <div class="invalid-feedback">Masukkan Nama User</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" placeholder="Masukkan Email" class="form-control" name="email" required="" value="{{ old('email') }}">
                                        <div class="invalid-feedback">Masukkan Email User</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password <span class="text-danger">*</span></label>
                                        <input type="password" placeholder="Masukkan Password" class="form-control" name="password" required="">
                                        <div class="invalid-feedback">Masukkan Password (Min 8 Karakter)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Role <span class="text-danger">*</span></label>
                                        <select name="role" class="form-control selectric" required="">
                                            <option value="super_admin">Super Admin</option>
                                            <option value="store_manager">Store Manager</option>
                                            <option value="finance">Finance</option>
                                            <option value="admin">Admin (Operations)</option>
                                            <option value="sales">Sales (Kasir)</option>
                                            <option value="branch">Kepala Cabang</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Akses Cabang (Bisa pilih lebih dari satu)</label>
                                <select name="warehouse_ids[]" class="form-control select2" multiple="" style="width: 100%;">
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <hr>
                            <label class="d-block font-weight-bold border-bottom pb-2 mb-3">Hak Akses Menu</label>
                            @foreach($permissions as $group => $items)
                                <div class="permission-group mb-4 p-3 border rounded shadow-sm bg-white">
                                    <div class="d-flex justify-content-between align-items-center bg-light p-2 border-left border-primary mb-3" style="border-left-width: 5px !important;">
                                        <span class="font-weight-bold text-primary"><i class="fas fa-folder mr-2"></i>{{ $group }}</span>
                                        @php
                                            $parent = $items->filter(fn($i) => str_ends_with($i->slug, '_menu'))->first();
                                        @endphp
                                        @if($parent)
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $parent->id }}" 
                                                       class="custom-control-input parent-checkbox" 
                                                       id="perm_{{ $parent->id }}" data-group="{{ Str::slug($group) }}">
                                                <label class="custom-control-label font-weight-bold" for="perm_{{ $parent->id }}">Pilih Semua</label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row px-3">
                                        @foreach($items as $p)
                                            @if(!str_ends_with($p->slug, '_menu'))
                                                <div class="col-md-3 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="permission_ids[]" value="{{ $p->id }}" 
                                                               class="custom-control-input child-checkbox" 
                                                               id="perm_{{ $p->id }}" data-group="{{ Str::slug($group) }}">
                                                        <label class="custom-control-label" for="perm_{{ $p->id }}">{{ $p->name }}</label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ url('admin/manage-master/users') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan User</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            // Hierarchical Permission Toggle
            $(document).on('change', '.parent-checkbox', function() {
                let group = $(this).data('group');
                let isChecked = $(this).is(':checked');
                $(`.child-checkbox[data-group="${group}"]`).prop('checked', isChecked);
            });

            $(document).on('change', '.child-checkbox', function() {
                let group = $(this).data('group');
                let totalChildren = $(`.child-checkbox[data-group="${group}"]`).length;
                let checkedChildren = $(`.child-checkbox[data-group="${group}"]:checked`).length;
                $(`.parent-checkbox[data-group="${group}"]`).prop('checked', totalChildren === checkedChildren);
            });
        });
    </script>
    @endpush
@endsection
