# Alur Baru Manajemen Stok & Settlement Beautylatory

Sistem iPOS telah disesuaikan dengan logika bisnis Beautylatory untuk mengelola stok pusat dan penagihan penjualan cabang.

---

## 1. Manajemen Stok Pusat (HQ)
Halaman **Stok Barang** secara default hanya menampilkan stok yang ada di **Gudang Utama (Beautylatory)**.
- **Batched Stock**: Barang dikelola per batch berdasarkan kedatangan.
- **Detail Audit**: Klik tombol "Detail" untuk melihat:
    - **Penerimaan Barang**: Informasi nomor **Surat Jalan (SJ) Supplier** dan link ke **Purchase Order (PO)** asal barang.
    - **Riwayat Penjualan**: Daftar penjualan langsung (Invoice/POS) yang memotong stok batch tersebut.

## 2. Operasional Kasir & Penjualan Online
- **Stok Real-time**: Transaksi melalui POS atau Invoice otomatis terhubung dan memotong stok di **Gudang Utama (Beautylatory)**.
- **Keamanan Stok**: Sistem mencegah penjualan jika stok di gudang utama tidak mencukupi.

## 3. Alur Perpindahan Barang (Stock Movement)
Digunakan saat Pusat mengirimkan stok fisik ke Cabang (Store Novus, Store Apotek, dll).
1. **Ship**: Stok di **Gudang Utama berkurang** dan berstatus `TRANSIT`.
2. **Receive**: Begitu Cabang mengonfirmasi penerimaan, stok fisik bertambah di **Gudang Cabang** dan status menjadi `COMPLETED`.

## 4. Settlement (Rekap & Penagihan Cabang)
Gudang Cabang/Admin melakukan rekapitulasi berkala untuk produk yang terjual di lokasi mereka.
1. **Input Penjualan**: Admin masuk ke menu `Settlement > Buat Settlement`. Pilih Cabang dan Periode.
2. **Daftar Barang**: Masukkan daftar produk dan jumlah (Qty) yang terjual (misal: total penjualan Shopee/Marketplace dalam seminggu).
3. **Submit**: Simpan dan Submit laporan tersebut.
4. **Tagihan HQ**: Pusat (HQ) akan memverifikasi laporan tersebut dan menagih pembayaran hanya untuk produk yang dilaporkan terjual.

---
> [!IMPORTANT]
> - Menu **Stok Barang** fokus pada inventaris fisik di Pusat.
> - Menu **Settlement** fokus pada pelaporan penjualan dan administrasi keuangan antar cabang.
