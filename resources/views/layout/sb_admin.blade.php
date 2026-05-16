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
            @if($user->hasPermission('access_dashboard_pusat'))
            <li {{ $sb == 'Dashboard' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ url('admin') }}">
                    <i class="fas fa-fire"></i> <span>Dashboard</span>
                </a>
            </li>
            @endif

            {{-- Cabang Group --}}
            @if($user->warehouses->count() > 0 || $user->hasPermission('access_branch_dashboard') || $user->hasPermission('access_branch_stock') || $user->hasPermission('access_product_catalog') || $user->hasPermission('access_stock_request') || $user->hasPermission('access_branch_sales') || $user->hasPermission('access_branch_return'))
            <li class="nav-item dropdown {{ ($sb == 'BranchDashboard' || $sb == 'BranchStock' || $sb == 'ProductCatalog' || $sb == 'BranchStockRequest' || $sb == 'BranchSale' || $sb == 'BranchReturn') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-store"></i> <span>Cabang</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_branch_dashboard'))
                    <li class="{{ $sb == 'BranchDashboard' ? 'active' : '' }}"><a class="nav-link" href="{{ route('branch.dashboard') }}">Dashboard Cabang</a></li>
                    @endif

                    @if($user->hasPermission('access_branch_stock'))
                    <li class="{{ $sb == 'BranchStock' ? 'active' : '' }}"><a class="nav-link" href="{{ route('branch.stock.index') }}">Stok Cabang Saya</a></li>
                    @endif

                    @if($user->hasPermission('access_product_catalog'))
                    <li class="{{ $sb == 'ProductCatalog' ? 'active' : '' }}"><a class="nav-link" href="{{ route('branch.products.index') }}">Katalog Produk</a></li>
                    @endif

                    @if($user->hasPermission('access_stock_request'))
                    <li class="{{ $sb == 'BranchStockRequest' ? 'active' : '' }}"><a class="nav-link" href="{{ route('branch.stock_requests.index') }}">Pengajuan Barang</a></li>
                    @endif

                    @if($user->hasPermission('access_branch_sales'))
                    <li class="{{ $sb == 'BranchSale' ? 'active' : '' }}"><a class="nav-link" href="{{ route('branch.sales.index') }}">Penjualan Harian</a></li>
                    @endif

                    @if($user->hasPermission('access_branch_return'))
                    <li class="{{ $sb == 'BranchReturn' ? 'active' : '' }}"><a class="nav-link" href="{{ route('branch.returns.index') }}">Return ke Pusat</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Produk Group --}}
            @if($user->hasPermission('access_products_menu') || $user->hasPermission('access_products') || $user->hasPermission('access_merek') || $user->hasPermission('access_pricing'))
            <li class="nav-item dropdown {{ ($sb == 'Product' || $sb == 'Merek' || $sb == 'ProductPricing') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-box"></i> <span>Produk</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_products_menu') || $user->hasPermission('access_merek'))
                    <li class="{{ $sb == 'Merek' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/merek') }}">Merek</a></li>
                    @endif

                    @if($user->hasPermission('access_products_menu') || $user->hasPermission('access_products'))
                    <li class="{{ $sb == 'Product' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/products') }}">Produk</a></li>
                    @endif

                    @if($user->hasPermission('access_products_menu') || $user->hasPermission('access_pricing'))
                    <li class="{{ $sb == 'ProductPricing' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.products.pricing') }}">Harga Produk</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Manajemen Stok Group --}}
            @if($user->hasPermission('access_stock_menu') || $user->hasPermission('access_stock_pusat') || $user->hasPermission('access_stock_movement') || $user->hasPermission('access_stock_settlement') || $user->hasPermission('access_warehouses'))
            <li class="nav-item dropdown {{ ($sb == 'Stock' || $sb == 'StockMovement' || $sb == 'Settlement' || $sb == 'Warehouse') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-boxes"></i> <span>Manajemen Stok</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_stock_menu') || $user->hasPermission('access_stock_pusat'))
                    <li class="{{ $sb == 'Stock' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/stock') }}">Stok Barang</a></li>
                    @endif

                    @if($user->hasPermission('access_stock_menu') || $user->hasPermission('access_stock_movement'))
                    <li class="{{ $sb == 'StockMovement' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.stock_movements.index') }}">Stock Movement</a></li>
                    @endif

                    @if($user->hasPermission('access_stock_menu') || $user->hasPermission('access_stock_settlement'))
                    <li class="{{ $sb == 'Settlement' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settlements.index') }}">Settlement Gudang</a></li>
                    @endif

                    @if($user->hasPermission('access_stock_menu') || $user->hasPermission('access_warehouses'))
                    <li class="{{ $sb == 'Warehouse' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.warehouses.index') }}">Data Gudang</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Branch Management Group (Pusat) --}}
            {{-- Branch Management Group (Pusat) --}}
            @if($user->hasPermission('access_branch_management_menu') || $user->hasPermission('access_branch_stock_approval') || $user->hasPermission('access_branch_return_approval'))
            <li class="nav-item dropdown {{ ($sb == 'BranchApproval' || $sb == 'BranchReturnApproval') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-warehouse"></i> <span>Gudang Cabang</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_branch_management_menu') || $user->hasPermission('access_branch_stock_approval'))
                    <li class="{{ $sb == 'BranchApproval' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.branch.stock_requests.index') }}">Pengajuan Barang</a>
                    </li>
                    @endif

                    @if($user->hasPermission('access_branch_management_menu') || $user->hasPermission('access_branch_return_approval'))
                    <li class="{{ $sb == 'BranchReturnApproval' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.branch.returns.index') }}">Return dari Cabang</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Pembelian Group --}}
            @if($user->hasPermission('access_purchasing_menu') || $user->hasPermission('access_suppliers') || $user->hasPermission('access_purchase_orders') || $user->hasPermission('access_goods_receipts') || $user->hasPermission('access_purchasing_returns'))
            <li class="nav-item dropdown @if ($sb == 'PurchaseOrder' || $sb == 'GoodsReceipt' || $sb == 'Supplier' || $sb == 'ReturnToSupplier') active @endif">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-shopping-cart"></i>
                    <span>Pembelian</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_purchasing_menu') || $user->hasPermission('access_purchase_orders'))
                    <li class="@if ($sb == 'PurchaseOrder') active @endif"><a class="nav-link"
                            href="{{ route('admin.purchasing.purchase_orders.index') }}">Order Pembelian</a></li>
                    @endif

                    @if($user->hasPermission('access_purchasing_menu') || $user->hasPermission('access_goods_receipts'))
                    <li class="@if ($sb == 'GoodsReceipt') active @endif"><a class="nav-link"
                            href="{{ route('admin.purchasing.goods_receipts.index') }}">Penerimaan Barang</a></li>
                    @endif

                    @if($user->hasPermission('access_purchasing_menu') || $user->hasPermission('access_purchasing_returns'))
                    <li class="@if ($sb == 'ReturnToSupplier') active @endif"><a class="nav-link"
                            href="{{ route('admin.purchasing.returns.index') }}">Return Barang</a></li>
                    @endif

                    @if($user->hasPermission('access_purchasing_menu') || $user->hasPermission('access_suppliers'))
                    <li class="@if ($sb == 'Supplier') active @endif"><a class="nav-link"
                            href="{{ route('admin.purchasing.suppliers.index') }}">Supplier</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Penjualan Group --}}
            @if($user->hasPermission('access_sales_menu') || $user->hasPermission('access_sales_pusat') || $user->hasPermission('access_sales_invoices') || $user->hasPermission('access_sales_delivery_notes') || $user->hasPermission('access_sales_receipts') || $user->hasPermission('access_vouchers') || $user->hasPermission('access_online_sales'))
            <li class="nav-item dropdown {{ ($sb == 'Transaction' || $sb == 'SalesInvoices' || $sb == 'SalesDeliveryNotes' || $sb == 'SalesReceipts' || $sb == 'Voucher' || $sb == 'OnlineSale') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-money-bill-wave"></i> <span>Penjualan</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_sales_menu') || $user->hasPermission('access_sales_pusat'))
                    <li class="{{ $sb == 'Transaction' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/transactions') }}">Data Transaksi</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_sales_menu') || $user->hasPermission('access_sales_invoices'))
                    <li class="{{ $sb == 'SalesInvoices' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.invoices.index') }}">Invoice Penjualan</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_sales_menu') || $user->hasPermission('access_sales_delivery_notes'))
                    <li class="{{ $sb == 'SalesDeliveryNotes' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.delivery_notes.index') }}">Surat Jalan</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_sales_menu') || $user->hasPermission('access_sales_receipts'))
                    <li class="{{ $sb == 'SalesReceipts' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.sales.receipts.index') }}">Kuitansi</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_sales_menu') || $user->hasPermission('access_vouchers'))
                    <li class="{{ $sb == 'Voucher' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/voucher') }}">Voucher / Diskon</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_sales_menu') || $user->hasPermission('access_online_sales'))
                    <li class="{{ $sb == 'OnlineSale' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.online_sale.index') }}">Penjualan Online</a></li>
                    @endif
                </ul>
            </li>
            @endif
            
            {{-- Finance Group --}}
            @if($user->hasPermission('access_finance_menu') || $user->hasPermission('access_petty_cash') || $user->hasPermission('access_expense_categories') || $user->hasPermission('access_finance_settlement') || $user->hasPermission('access_fund_requests') || $user->hasPermission('access_finance_reports'))
            <li class="nav-item dropdown {{ ($sb == 'PettyCash' || $sb == 'ExpenseCategory' || $sb == 'FinanceReport' || $sb == 'FinanceSettlement' || $sb == 'FinanceFundRequest') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-wallet"></i> <span>Finance</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_finance_menu') || $user->hasPermission('access_petty_cash'))
                    <li class="{{ $sb == 'PettyCash' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.petty_cash.index') }}">Petty Cash</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_finance_menu') || $user->hasPermission('access_expense_categories'))
                    <li class="{{ $sb == 'ExpenseCategory' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.expense_categories.index') }}">Kategori Pengeluaran</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_finance_menu') || $user->hasPermission('access_finance_settlement'))
                    <li class="{{ $sb == 'FinanceSettlement' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.settlement.index') }}">Pelunasan Supplier</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_finance_menu') || $user->hasPermission('access_fund_requests'))
                    <li class="{{ $sb == 'FinanceFundRequest' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.fund_requests.index') }}">Pengajuan Dana</a></li>
                    @endif
                    
                    @if($user->hasPermission('access_finance_menu') || $user->hasPermission('access_finance_reports'))
                    <li class="{{ $sb == 'FinanceReport' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.finance.reports.index') }}">Laporan Keuangan</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Mitra Group --}}
            @if($user->hasPermission('access_mitra_menu') || $user->hasPermission('access_affiliate_users') || $user->hasPermission('access_customers'))
            <li class="nav-item dropdown {{ ($sb == 'Affiliate Users' || $sb == 'Customers') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-users-cog"></i> <span>Mitra</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_mitra_menu') || $user->hasPermission('access_affiliate_users'))
                    <li class="{{ $sb == 'Affiliate Users' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.affiliates.index') }}">Affiliate</a></li>
                    @endif

                    @if($user->hasPermission('access_mitra_menu') || $user->hasPermission('access_customers'))
                    <li class="{{ $sb == 'Customers' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.customers.index') }}">Customer</a></li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Pengaturan Group --}}
            @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_categories') || $user->hasPermission('access_sub_categories') || $user->hasPermission('access_product_types') || $user->hasPermission('access_product_tiers') || $user->hasPermission('access_attribute_groups') || $user->hasPermission('access_attributes') || $user->hasPermission('access_channels') || $user->hasPermission('access_store_settings') || $user->hasPermission('access_bank_accounts') || $user->hasPermission('access_user_management'))
            <li class="nav-item dropdown {{ ($sb == 'Settings' || $sb == 'Attribute Groups' || $sb == 'Attributes' || $sb == 'Categories' || $sb == 'Sub Categories' || $sb == 'ProductTypes' || $sb == 'ProductTier' || $sb == 'Warehouse' || $sb == 'User') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-cogs"></i><span>Pengaturan</span></a>
                <ul class="dropdown-menu">
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_categories'))
                    <li class="{{ $sb == 'Categories' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/categories') }}">Kategori Utama</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_sub_categories'))
                    <li class="{{ $sb == 'Sub Categories' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/sub-categories') }}">Sub Kategori</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_product_types'))
                    <li class="{{ $sb == 'ProductTypes' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/product-types') }}">Tipe Produk</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_product_tiers'))
                    <li class="{{ $sb == 'ProductTier' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/product-tiers') }}">Tier Harga</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_attribute_groups'))
                    <li class="{{ $sb == 'Attribute Groups' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.manage_master.attribute_groups.index') }}">Grup Atribut</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_attributes'))
                    <li class="{{ $sb == 'Attributes' ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.manage_master.attributes.index') }}">Data Atribut</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_channels'))
                    <li class="{{ Request::is('admin/settings/channels') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.channels') }}">Saluran Penjualan</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_store_settings'))
                    <li class="{{ Request::is('admin/settings/store') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.store') }}">Toko</a></li>
                    @endif
                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_bank_accounts'))
                    <li class="{{ Request::is('admin/settings/bank-accounts*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.settings.bank_accounts') }}">Rekening Bank</a></li>
                    @endif

                    @if($user->hasPermission('access_settings_menu') || $user->hasPermission('access_user_management'))
                    <li class="{{ $sb == 'User' ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/manage-master/users') }}">Data User</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if($user->hasPermission('access_pos_pusat'))
            <li {{ $sb == 'POS' ? 'class=active' : '' }}>
                <a class="nav-link" href="{{ route('admin.pos.index') }}">
                    <i class="fas fa-desktop"></i> <span>Tampilan Kasir</span>
                </a>
            </li>
            @endif
        </ul>
    </aside>
</div>
