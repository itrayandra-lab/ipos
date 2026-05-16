<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // List of permissions to REMOVE (the old duplicates)
        $oldSlugs = [
            'access_purchasing',
            'access_finance',
            'access_mitra',
            'access_settings',
            'access_branch_approval' // We will replace this with a better named one
        ];
        Permission::whereIn('slug', $oldSlugs)->delete();

        $permissions = [
            // Dashboard & POS
            ['name' => 'Akses Dashboard Pusat', 'slug' => 'access_dashboard_pusat', 'group' => 'Pusat'],
            ['name' => 'Akses POS Pusat', 'slug' => 'access_pos_pusat', 'group' => 'Pusat'],

            // Grup Produk
            ['name' => 'Grup Produk (Utama)', 'slug' => 'access_products_menu', 'group' => 'Produk'],
            ['name' => 'Merek', 'slug' => 'access_merek', 'group' => 'Produk'],
            ['name' => 'Produk', 'slug' => 'access_products', 'group' => 'Produk'],
            ['name' => 'Harga Produk', 'slug' => 'access_pricing', 'group' => 'Produk'],

            // Grup Manajemen Stok
            ['name' => 'Grup Manajemen Stok (Utama)', 'slug' => 'access_stock_menu', 'group' => 'Manajemen Stok'],
            ['name' => 'Stok Barang', 'slug' => 'access_stock_pusat', 'group' => 'Manajemen Stok'],
            ['name' => 'Stock Movement', 'slug' => 'access_stock_movement', 'group' => 'Manajemen Stok'],
            ['name' => 'Settlement Gudang', 'slug' => 'access_stock_settlement', 'group' => 'Manajemen Stok'],
            ['name' => 'Data Gudang', 'slug' => 'access_warehouses', 'group' => 'Manajemen Stok'],

            // Grup Gudang Cabang (Approval dari Pusat)
            ['name' => 'Grup Gudang Cabang (Utama)', 'slug' => 'access_branch_management_menu', 'group' => 'Gudang Cabang'],
            ['name' => 'Approval Pengajuan Barang', 'slug' => 'access_branch_stock_approval', 'group' => 'Gudang Cabang'],
            ['name' => 'Approval Return Cabang', 'slug' => 'access_branch_return_approval', 'group' => 'Gudang Cabang'],

            // Grup Pembelian
            ['name' => 'Grup Pembelian (Utama)', 'slug' => 'access_purchasing_menu', 'group' => 'Pembelian'],
            ['name' => 'Supplier', 'slug' => 'access_suppliers', 'group' => 'Pembelian'],
            ['name' => 'Pesanan Pembelian (PO)', 'slug' => 'access_purchase_orders', 'group' => 'Pembelian'],
            ['name' => 'Penerimaan Barang', 'slug' => 'access_goods_receipts', 'group' => 'Pembelian'],
            ['name' => 'Return Pembelian', 'slug' => 'access_purchasing_returns', 'group' => 'Pembelian'],

            // Grup Penjualan
            ['name' => 'Grup Penjualan (Utama)', 'slug' => 'access_sales_menu', 'group' => 'Penjualan'],
            ['name' => 'Data Transaksi', 'slug' => 'access_sales_pusat', 'group' => 'Penjualan'],
            ['name' => 'Invoice Penjualan', 'slug' => 'access_sales_invoices', 'group' => 'Penjualan'],
            ['name' => 'Surat Jalan', 'slug' => 'access_sales_delivery_notes', 'group' => 'Penjualan'],
            ['name' => 'Kuitansi', 'slug' => 'access_sales_receipts', 'group' => 'Penjualan'],
            ['name' => 'Voucher / Diskon', 'slug' => 'access_vouchers', 'group' => 'Penjualan'],
            ['name' => 'Penjualan Online', 'slug' => 'access_online_sales', 'group' => 'Penjualan'],

            // Grup Finance
            ['name' => 'Grup Finance (Utama)', 'slug' => 'access_finance_menu', 'group' => 'Finance'],
            ['name' => 'Petty Cash', 'slug' => 'access_petty_cash', 'group' => 'Finance'],
            ['name' => 'Kategori Pengeluaran', 'slug' => 'access_expense_categories', 'group' => 'Finance'],
            ['name' => 'Pelunasan Supplier', 'slug' => 'access_finance_settlement', 'group' => 'Finance'],
            ['name' => 'Pengajuan Dana', 'slug' => 'access_fund_requests', 'group' => 'Finance'],
            ['name' => 'Laporan Keuangan', 'slug' => 'access_finance_reports', 'group' => 'Finance'],

            // Grup Mitra
            ['name' => 'Grup Mitra (Utama)', 'slug' => 'access_mitra_menu', 'group' => 'Mitra'],
            ['name' => 'Affiliate', 'slug' => 'access_affiliate_users', 'group' => 'Mitra'],
            ['name' => 'Customer', 'slug' => 'access_customers', 'group' => 'Mitra'],

            // Grup Pengaturan
            ['name' => 'Grup Pengaturan (Utama)', 'slug' => 'access_settings_menu', 'group' => 'Pengaturan'],
            ['name' => 'Kategori Utama', 'slug' => 'access_categories', 'group' => 'Pengaturan'],
            ['name' => 'Sub Kategori', 'slug' => 'access_sub_categories', 'group' => 'Pengaturan'],
            ['name' => 'Tipe Produk', 'slug' => 'access_product_types', 'group' => 'Pengaturan'],
            ['name' => 'Tier Harga', 'slug' => 'access_product_tiers', 'group' => 'Pengaturan'],
            ['name' => 'Grup Atribut', 'slug' => 'access_attribute_groups', 'group' => 'Pengaturan'],
            ['name' => 'Data Atribut', 'slug' => 'access_attributes', 'group' => 'Pengaturan'],
            ['name' => 'Saluran Penjualan', 'slug' => 'access_channels', 'group' => 'Pengaturan'],
            ['name' => 'Pengaturan Toko', 'slug' => 'access_store_settings', 'group' => 'Pengaturan'],
            ['name' => 'Rekening Bank', 'slug' => 'access_bank_accounts', 'group' => 'Pengaturan'],
            ['name' => 'Manajemen User', 'slug' => 'access_user_management', 'group' => 'Pengaturan'],

            // Menu Cabang
            ['name' => 'Akses Dashboard Cabang', 'slug' => 'access_branch_dashboard', 'group' => 'Cabang'],
            ['name' => 'Akses Stok Cabang Saya', 'slug' => 'access_branch_stock', 'group' => 'Cabang'],
            ['name' => 'Akses Katalog Produk', 'slug' => 'access_product_catalog', 'group' => 'Cabang'],
            ['name' => 'Akses Pengajuan Barang', 'slug' => 'access_stock_request', 'group' => 'Cabang'],
            ['name' => 'Akses Penjualan Harian', 'slug' => 'access_branch_sales', 'group' => 'Cabang'],
            ['name' => 'Akses Return ke Pusat', 'slug' => 'access_branch_return', 'group' => 'Cabang'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
