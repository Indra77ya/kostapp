# Sistem Manajemen Kost

Aplikasi sistem manajemen kost yang dibangun menggunakan Laravel 11, Livewire, dan template dashboard Tabler. Sistem ini mendukung fitur real-time melalui Laravel Reverb dan memiliki manajemen role yang fleksibel.

## Fitur Utama
- **Dashboard Full Custom**: Menggunakan template [Tabler](https://tabler.io/).
- **Multi-Role**: Developer, Owner, Admin, dan Tenant (menggunakan Spatie Permission).
- **Real-time Ready**: Terintegrasi dengan Laravel Reverb dan Livewire.
- **Login System**: Autentikasi kustom menggunakan template Tabler.

## Persyaratan Sistem
- PHP >= 8.2
- Composer
- SQLite (default) atau MySQL
- Node.js & NPM (untuk pengembangan aset jika diperlukan)

## Langkah-langkah Instalasi

Ikuti langkah berikut untuk menjalankan proyek di komputer lokal Anda:

### 1. Clone atau Persiapkan Folder Proyek
Pastikan Anda berada di direktori proyek `sistem-manajemen-kost`.

### 2. Instal Dependensi PHP
Jalankan perintah berikut untuk menginstal semua library yang dibutuhkan:
```bash
composer install
```

### 3. Konfigurasi Lingkungan (.env)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Konfigurasi Database
Secara default, proyek ini menggunakan **SQLite**.
- Jika menggunakan SQLite, pastikan file database sudah ada:
  ```bash
  touch database/database.sqlite
  ```
- Jika ingin menggunakan **MySQL**, buka file `.env` dan sesuaikan bagian `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.

### 6. Jalankan Migrasi dan Seeder
Langkah ini akan membuat tabel-tabel yang diperlukan dan mengisi data role serta user contoh:
```bash
php artisan migrate --seed --seeder=RoleSeeder
```

### 7. Jalankan Server
Jalankan server lokal Laravel:
```bash
php artisan serve
```
Aplikasi dapat diakses melalui browser di alamat: `http://127.0.0.1:8000`

---

## Akun Demo (Default Password: `password`)
| Role | Email |
|------|-------|
| **Developer** | `developer@example.com` |
| **Owner** | `owner@example.com` |
| **Admin** | `admin@example.com` |
| **Tenant** | `tenant@example.com` |

## Catatan Pengembangan
- Proyek ini tidak menggunakan Laravel Breeze/Jetstream/Filament sesuai permintaan user untuk menjaga fleksibilitas penuh pada tampilan Tabler.
- Aset Tabler (CSS/JS) disimpan secara lokal di folder `public/assets/tabler/`.
