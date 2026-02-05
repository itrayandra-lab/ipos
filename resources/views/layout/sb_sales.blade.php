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
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" data-toggle="dropdown"
               class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img alt="image" src="{{ asset('assets/img/avatar.png') }}" class="rounded-circle mr-1">
                <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">Tetap Semangat</div>
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
            <a href="#">IPOS SALES</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="#">POS</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li {{ $sb == 'Dashboard' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ url('sales') }}">
                    <i class="fas fa-fire"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="menu-header">Master Data</li>
            <li {{ $sb == 'Product' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ url('sales/manage-master/products') }}">
                    <i class="fas fa-box"></i> <span>Produk</span>
                </a>
            </li>

            <li class="menu-header">Kasir</li>
            <li {{ $sb == 'POS' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('sales.pos.index') }}">
                    <i class="fas fa-desktop"></i> <span>Tampilan Kasir</span>
                </a>
            </li>

            <li class="menu-header">Transaksi</li>
            <li {{ $sb == 'Transaction' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ url('sales/transactions') }}">
                    <i class="fas fa-cash-register"></i> <span>Transaksi</span>
                </a>
            </li>
        </ul>
        <div class="hide-sidebar-mini mt-4 mb-4 p-3">
            <a href="/"
                class="btn btn-primary btn-lg btn-block btn-icon-split">
                <i class="fas fa-rocket"></i> Back To Home
            </a>
        </div>
    </aside>
</div>
