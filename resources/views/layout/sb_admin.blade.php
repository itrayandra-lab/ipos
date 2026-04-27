@php
    $user = Auth::user();
    $role = $user->role;
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
                <a href="#" class="dropdown-item has-icon edit-profile">
                    <i class="far fa-user"></i> Profile
                </a>
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
            <a href="#">IPOS ADMIN</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="#">POS</a>
        </div>
        <ul class="sidebar-menu">
            {{-- Dashboard --}}
            @if($user->isSuperAdmin() || $user->isStoreManager() || $user->isFinance() || $user->isOperations() || $user->isSales())
            <li {{ $sb == 'Dashboard' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ url('admin') }}">
                    <i class="fas fa-fire"></i> <span>Dashboard</span>
                </a>
            </li>
            @endif

            {{-- Produk Group --}}
            @if(in_array($role, ['super_admin', 'store_manager', 'admin', 'sales']))
            <li class="nav-item dropdown {{ ($sb == 'Product' || $sb == 'Merek') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-box"></i> <span>Produk</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'Merek' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/merek') }}">Merek</a></li>
                    <li class="{{ $sb == 'Product' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/products') }}">Produk</a></li>
                </ul>
            </li>
            @endif

            {{-- Manajemen Stok Group --}}
            @if(in_array($role, ['super_admin', 'store_manager', 'admin', 'sales']))
            <li class="nav-item dropdown {{ ($sb == 'Stock' || $sb == 'StockMovement' || $sb == 'Settlement') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-boxes"></i> <span>Manajemen Stok</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'Stock' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/stock') }}">Stok Barang</a></li>
                    @if(in_array($role, ['super_admin', 'store_manager', 'admin']))
                    <li class="{{ $sb == 'StockMovement' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.stock_movements.index') }}">Stock Movement</a></li>
                    <li class="{{ $sb == 'Settlement' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settlements.index') }}">Settlement Gudang</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Pembelian Group --}}
            @if(in_array($role, ['super_admin', 'store_manager', 'admin', 'sales']))
            <li class="nav-item dropdown @if ($sb == 'PurchaseOrder' || $sb == 'GoodsReceipt' || $sb == 'Supplier') active @endif">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-shopping-cart"></i>
                    <span>Pembelian</span></a>
                <ul class="dropdown-menu">
                    <li class="@if ($sb == 'PurchaseOrder') active @endif"><a class="nav-link"
                            href="{{ route('admin.purchasing.purchase_orders.index') }}">Order Pembelian</a></li>
                    <li class="@if ($sb == 'GoodsReceipt') active @endif"><a class="nav-link"
                            href="{{ route('admin.purchasing.goods_receipts.index') }}">Penerimaan Barang</a></li>
                    <li class="@if ($sb == 'Supplier') active @endif"><a class="nav-link"
                            href="{{ route('admin.purchasing.suppliers.index') }}">Supplier</a></li>
                </ul>
            </li>
            @endif

            {{-- Penjualan Group --}}
            @if(in_array($role, ['super_admin', 'store_manager', 'finance', 'admin', 'sales']))
            <li class="nav-item dropdown {{ ($sb == 'Transaction' || $sb == 'SalesInvoices' || $sb == 'SalesDeliveryNotes' || $sb == 'SalesReceipts' || $sb == 'SalesLabInvoices' || $sb == 'Voucher' || $sb == 'OnlineSale') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-money-bill-wave"></i> <span>Penjualan</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'Transaction' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/transactions') }}">Data Transaksi</a></li>
                    <li class="{{ $sb == 'SalesInvoices' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.invoices.index') }}">Invoice Penjualan</a></li>
                    @if(in_array($role, ['super_admin', 'store_manager', 'admin']))
                    <li class="{{ $sb == 'SalesDeliveryNotes' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></li>
                    @endif
                    <li class="{{ $sb == 'SalesReceipts' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.receipts.index') }}">Kuitansi</a></li>
                    @if(in_array($role, ['super_admin', 'store_manager', 'admin']))
                    <li class="{{ $sb == 'OnlineSale' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.online_sale.index') }}">Marketplace</a></li>
                    <li class="{{ $sb == 'Voucher' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/voucher') }}">Voucher / Diskon</a></li>
                    @endif
                </ul>
            </li>
            @endif
            
            {{-- Finance Group --}}
            @if(in_array($role, ['super_admin', 'store_manager', 'finance']))
            <li class="nav-item dropdown {{ ($sb == 'PettyCash' || $sb == 'Expense' || $sb == 'FinanceReport') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-wallet"></i> <span>Finance</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'PettyCash' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.petty_cash.index') }}">Petty Cash</a></li>
                    <li class="{{ $sb == 'Expense' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.expenses.index') }}">Pengeluaran (Expenses)</a></li>
                    <li class="{{ $sb == 'FinanceReport' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.reports.index') }}">Laporan Keuangan</a></li>
                </ul>
            </li>
            @endif

            {{-- Mitra Group --}}
            @if(in_array($role, ['super_admin', 'store_manager', 'admin', 'sales']))
            <li class="nav-item dropdown {{ ($sb == 'Affiliate Users' || $sb == 'Customers') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-users-cog"></i> <span>Mitra</span></a>
                <ul class="dropdown-menu">
                    @if(in_array($role, ['super_admin', 'store_manager', 'admin']))
                    <li class="{{ $sb == 'Affiliate Users' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.affiliates.index') }}">Affiliate</a></li>
                    @endif
                    <li class="{{ $sb == 'Customers' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.customers.index') }}">Customer</a></li>
                </ul>
            </li>
            @endif

            {{-- Pengaturan Group --}}
            @if(in_array($role, ['super_admin', 'store_manager', 'finance', 'admin']))
            <li class="nav-item dropdown {{ ($sb == 'Settings' || $sb == 'Attribute Groups' || $sb == 'Attributes' || $sb == 'Categories' || $sb == 'Sub Categories' || $sb == 'ProductTypes' || $sb == 'ProductTier' || $sb == 'Warehouse' || $sb == 'User') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-cogs"></i><span>Pengaturan</span></a>
                <ul class="dropdown-menu">
                    @if(in_array($role, ['super_admin', 'store_manager', 'admin']))
                    <li class="{{ $sb == 'Categories' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/categories') }}">Kategori Utama</a></li>
                    <li class="{{ $sb == 'Sub Categories' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/sub-categories') }}">Sub Kategori</a></li>
                    <li class="{{ $sb == 'ProductTypes' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/product-types') }}">Tipe Produk</a></li>
                    <li class="{{ $sb == 'ProductTier' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/product-tiers') }}">Tier Harga</a></li>
                    <li class="{{ $sb == 'Attribute Groups' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.manage_master.attribute_groups.index') }}">Grup Atribut</a></li>
                    <li class="{{ $sb == 'Attributes' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.manage_master.attributes.index') }}">Data Atribut</a></li>
                    <li class="{{ $sb == 'Warehouse' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.warehouses.index') }}">Data Gudang</a></li>
                    <li class="{{ Request::is('admin/settings/channels') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.channels') }}">Saluran Penjualan</a></li>
                    <li class="{{ Request::is('admin/settings/store') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.store') }}">Toko</a></li>
                    @if(in_array($role, ['super_admin', 'store_manager']))
                    <li class="{{ $sb == 'User' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/users') }}">Data User</a></li>
                    @endif
                    @endif
                    
                    @if(in_array($role, ['super_admin', 'finance']))
                    <li class="{{ Request::is('admin/settings/bank-accounts*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.bank_accounts') }}">Rekening Bank</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if(in_array($role, ['super_admin', 'store_manager', 'admin', 'sales']))
            <li {{ $sb == 'POS' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('admin.pos.index') }}">
                    <i class="fas fa-desktop"></i> <span>Tampilan Kasir</span>
                </a>
            </li>
            @endif
        </ul>
        <div class="hide-sidebar-mini mt-4 mb-4 p-3">
            <a href="/"
                class="btn btn-primary btn-lg btn-block btn-icon-split">
                <i class="fas fa-rocket"></i> Back To Home
            </a>
        </div>
    </aside>
</div>
