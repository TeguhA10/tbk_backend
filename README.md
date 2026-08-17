# Backend API - PT. Trans Berjaya Khatulistiwa

Backend RESTful API untuk Sistem Manajemen Keuangan PT. Trans Berjaya Khatulistiwa. Dibangun menggunakan framework **Laravel 13** dan database **PostgreSQL 16**.

## 🛠️ Stack Teknologi

- **Framework**: Laravel 13.x
- **Bahasa**: PHP 8.4
- **Database**: PostgreSQL 16
- **Containerization**: Docker (PHP 8.4 CLI Alpine + PostgreSQL Alpine)

## 🚀 Menjalankan Backend

### Menggunakan Docker Compose (Direkomendasikan)

Dari direktori root proyek:

```bash
docker compose up --build
```

### Menjalankan Secara Lokal (Manual)

1. Pastikan PostgreSQL berjalan dan database `db_tbk` telah dibuat.
2. Salin environment file:
   ```bash
   cp .env.example .env
   ```
3. Install dependensi composer:
   ```bash
   composer install
   ```
4. Generate key aplikasi:
   ```bash
   php artisan key:generate
   ```
5. Jalankan migrasi & seeder data:
   ```bash
   php artisan migrate --seed
   ```
6. Jalankan dev server:
   ```bash
   php artisan serve --port=8000
   ```

Backend API dapat diakses di `http://localhost:8000/api`.

Untuk panduan lengkap endpoints dan dokumentasi sistem, silakan merujuk ke [README Utama](../README.md).
