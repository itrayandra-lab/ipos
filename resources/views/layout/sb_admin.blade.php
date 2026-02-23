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

            <li {{ $sb == 'Dashboard' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ url('admin') }}">
                    <i class="fas fa-fire"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item dropdown {{ ($sb == 'Product' || $sb == 'Stock' || $sb == 'Merek' || $sb == 'Categories' || $sb == 'Sub Categories' || $sb == 'ProductTypes') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-boxes"></i> <span>Produk</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'Merek' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/merek') }}">Merek</a></li>
                    <li class="{{ $sb == 'Product' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/products') }}">Produk</a></li>
                    <li class="{{ $sb == 'Stock' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/stock') }}">Stok</a></li>
                    <li class="{{ $sb == 'Categories' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/categories') }}">Kategori Utama</a></li>
                    <li class="{{ $sb == 'Sub Categories' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/sub-categories') }}">Sub Kategori</a></li>
                    <li class="{{ $sb == 'ProductTypes' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/product-types') }}">Tipe Produk</a></li>
                </ul>
            </li>

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

            <li class="nav-item dropdown {{ ($sb == 'Transaction' || $sb == 'SalesInvoices' || $sb == 'SalesDeliveryNotes' || $sb == 'SalesReceipts' || $sb == 'SalesLabInvoices' || $sb == 'Voucher') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-money-bill-wave"></i> <span>Penjualan</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'Transaction' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/transactions') }}">Data Transaksi</a></li>
                    <li class="{{ $sb == 'SalesInvoices' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.invoices.index') }}">Invoice Penjualan</a></li>
                    <li class="{{ $sb == 'SalesLabInvoices' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.lab_invoices.index') }}">Invoice Lab Formulasi</a></li>
                    <li class="{{ $sb == 'SalesDeliveryNotes' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></li>
                    <li class="{{ $sb == 'SalesReceipts' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.receipts.index') }}">Kuitansi</a></li>
                    <li class="{{ $sb == 'Voucher' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/voucher') }}">Voucher / Diskon</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown {{ ($sb == 'Affiliate Users' || $sb == 'Customers' || $sb == 'User') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-users-cog"></i> <span>Mitra</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'Affiliate Users' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.affiliates.index') }}">Affiliate</a></li>
                    <li class="{{ $sb == 'Customers' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.customers.index') }}">Customer</a></li>
                    <li class="{{ $sb == 'User' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/users') }}">User</a></li>
                </ul>
            </li>

            <li class="nav-item dropdown {{ ($sb == 'Settings' || $sb == 'Attribute Groups' || $sb == 'Attributes') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-cogs"></i><span>Pengaturan</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ $sb == 'Attribute Groups' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.manage_master.attribute_groups.index') }}">Grup Atribut</a></li>
                    <li class="{{ $sb == 'Attributes' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.manage_master.attributes.index') }}">Data Atribut</a></li>
                    <li class="{{ Request::is('admin/settings/channels') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.channels') }}">Saluran Penjualan</a></li>
                    <li class="{{ Request::is('admin/settings/store') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.store') }}">Toko</a></li>
                </ul>
            </li>

            <li {{ $sb == 'OnlineSale' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('admin.online_sale.index') }}">
                    <i class="fas fa-shopping-bag"></i> <span>Marketplace</span>
                </a>
            </li>

            <li {{ $sb == 'POS' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('admin.pos.index') }}">
                    <i class="fas fa-desktop"></i> <span>Tampilan Kasir</span>
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
