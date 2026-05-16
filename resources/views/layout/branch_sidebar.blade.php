@php
    $user = Auth::user();
    $warehouse = $user->warehouse;
@endphp
<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li>
                <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
    </form>
    <div class="navbar-text mr-3 text-white small">
        <i class="fas fa-warehouse mr-1"></i> 
        @if($user->warehouses->count() > 1)
            <select class="form-control form-control-sm d-inline-block w-auto" onchange="window.location.href='/branch/switch/' + this.value">
                @foreach($user->warehouses as $w)
                    <option value="{{ $w->id }}" {{ $user->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        @else
            <strong>{{ $warehouse->name ?? 'Gudang Cabang' }}</strong>
        @endif
    </div>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img alt="image" src="{{ asset('assets/img/avatar.png') }}" class="rounded-circle mr-1">
                <div class="d-sm-none d-lg-inline-block">Hi, {{ $user->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">Gudang Cabang</div>
                <div class="dropdown-divider"></div>
                <a href="{{ url('logout') }}" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('branch.dashboard') }}">IPOS CABANG</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('branch.dashboard') }}">CB</a>
        </div>
        <ul class="sidebar-menu">
            {{-- Dashboard --}}
            @if($user->hasPermission('access_branch_dashboard'))
            <li {{ $sb == 'BranchDashboard' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('branch.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            </li>
            @endif

            {{-- Stok Cabang --}}
            @if($user->hasPermission('access_branch_stock'))
            <li {{ $sb == 'BranchStock' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('branch.stock.index') }}">
                    <i class="fas fa-boxes"></i> <span>Stok Cabang Saya</span>
                </a>
            </li>
            @endif

            {{-- Katalog Produk (Read-Only) --}}
            @if($user->hasPermission('access_product_catalog'))
            <li {{ $sb == 'ProductCatalog' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('branch.products.index') }}">
                    <i class="fas fa-book"></i> <span>Katalog Produk</span>
                </a>
            </li>
            @endif

            {{-- Pengajuan Barang --}}
            @if($user->hasPermission('access_stock_request'))
            <li {{ $sb == 'BranchStockRequest' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('branch.stock_requests.index') }}">
                    <i class="fas fa-paper-plane"></i> <span>Pengajuan Barang</span>
                </a>
            </li>
            @endif

            {{-- Penjualan Harian --}}
            @if($user->hasPermission('access_branch_sales'))
            <li {{ $sb == 'BranchSale' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('branch.sales.index') }}">
                    <i class="fas fa-cash-register"></i> <span>Penjualan Harian</span>
                </a>
            </li>
            @endif

            {{-- Return Barang --}}
            @if($user->hasPermission('access_branch_return'))
            <li {{ $sb == 'BranchReturn' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('branch.returns.index') }}">
                    <i class="fas fa-undo-alt"></i> <span>Return ke Pusat</span>
                </a>
            </li>
            @endif
        </ul>
    </aside>
</div>
