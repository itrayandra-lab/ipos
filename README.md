# IPOS - Point of Sale System

Sistem Point of Sale (POS) berbasis web menggunakan Laravel untuk mengelola penjualan, inventory, dan laporan bisnis.

## Instalasi dan Setup

### 1. Clone Repository

```bash
git clone https://github.com/itrayandra-lab/ipos.git
cd ipos
```

### 2. Set Remote URL (jika diperlukan)

```bash
git remote set-url origin https://github.com/itrayandra-lab/ipos.git
```

### 3. Install Dependencies

```bash
composer install
```

### 4. Setup Environment

```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi database dan Midtrans:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ipos_db
DB_USERNAME=root
DB_PASSWORD=

# Midtrans Configuration
MIDTRANS_MERCHANT_ID=G840*******
MIDTRANS_CLIENT_KEY=Mid-client-U3C*******
MIDTRANS_SERVER_KEY=Mid-server-s0*******
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_URL="https://app.midtrans.com/snap/snap.js"
```

**Catatan Midtrans:**
- Untuk testing/development, ubah `MIDTRANS_IS_PRODUCTION=false` dan gunakan sandbox URL
- Untuk production, gunakan `MIDTRANS_IS_PRODUCTION=true` dan production URL seperti di atas

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Setup Database

Buat database baru dengan nama `ipos_db` (atau sesuai dengan konfigurasi di `.env`).

Jalankan migration:

```bash
php artisan migrate
```

### 7. Run Database Seeder

```bash
php artisan db:seed
```

### 8. Create Storage Link

```bash
php artisan storage:link
```

### 9. Start Development Server

```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## Requirements

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Web Server (Apache/Nginx)
