# Sistem Manajemen Kost (KostApp)

Aplikasi sistem manajemen kost modern yang dibangun menggunakan **Laravel 11**, **Livewire 3**, dan template dashboard **Tabler**. Sistem ini dirancang untuk memberikan pengalaman pengguna yang responsif dengan fitur real-time yang didukung oleh **Laravel Reverb**.

## Fitur Utama

-   **Dashboard Full Custom**: Menggunakan template [Tabler](https://tabler.io/) yang dimodifikasi untuk kebutuhan manajemen kost.
-   **Multi-Role Management**: Sistem role yang fleksibel (Developer, Owner, Admin, dan Tenant) menggunakan [Spatie Permission](https://spatie.be/docs/laravel-permission/v6/).
-   **Real-time Ready**: Notifikasi dan update data dashboard secara instan tanpa refresh halaman menggunakan **Laravel Reverb** (WebSockets) dan **Livewire**.
-   **Manajemen Kamar & Pemesanan**: Struktur database yang siap untuk pengelolaan data kamar, harga, dan status hunian.
-   **Login System Custom**: Autentikasi yang dibangun dari awal dengan tampilan yang konsisten menggunakan komponen Tabler.

## Persyaratan Sistem

Pastikan perangkat Anda memenuhi spesifikasi berikut:
-   **PHP** >= 8.2
-   **Composer**
-   **Node.js** (LTS recommended) & **NPM**
-   **SQLite** (Default untuk pengembangan) atau MySQL

## Panduan Instalasi Detail

Ikuti langkah-langkah di bawah ini untuk menyiapkan lingkungan pengembangan di komputer lokal Anda:

### 1. Clone Repositori
Masuk ke direktori proyek Anda:
```bash
cd kostapp
```

### 2. Instal Dependensi Backend (PHP)
Jalankan perintah berikut untuk menginstal semua library Laravel yang diperlukan:
```bash
composer install
```

### 3. Konfigurasi Lingkungan (.env)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Setelah itu, buka file `.env` dan pastikan konfigurasi database dan Reverb sudah sesuai. Secara default, proyek ini menggunakan SQLite.

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Setup Database
Pastikan file database SQLite sudah ada (jika menggunakan SQLite):
```bash
touch database/database.sqlite
```
Kemudian jalankan migrasi tabel dan pengisian data awal (seeder):
```bash
php artisan migrate:fresh --seed
```
*Catatan: Perintah ini akan menghapus data lama dan mengisi ulang dengan akun demo.*

> **Tips Error SQLite**: Jika Anda mendapatkan error "Database file at path [kostapp] does not exist", pastikan variabel `DB_DATABASE` di file `.env` dikosongkan atau dikomentari agar Laravel menggunakan path default `database/database.sqlite`.

### 6. Instal Dependensi Frontend & Build Aset
Proyek ini menggunakan Vite untuk manajemen aset. Jalankan perintah berikut:
```bash
npm install
npm run build
```

---

## Cara Menjalankan Aplikasi

Sistem ini memiliki fitur real-time, sehingga Anda perlu menjalankan beberapa proses secara bersamaan:

### 1. Menjalankan Server Utama
Gunakan perintah artisan untuk menjalankan server lokal:
```bash
php artisan serve
```
Akses di browser: `http://127.0.0.1:8000`

### 2. Menjalankan Server WebSocket (Reverb)
Agar fitur real-time (notifikasi dan update otomatis) berjalan, Anda harus menjalankan server Reverb di terminal terpisah:
```bash
php artisan reverb:start
```

### 3. Menjalankan Queue Worker (Opsional)
Jika Anda menggunakan fitur yang memerlukan antrean (seperti pengiriman email masal):
```bash
php artisan queue:listen
```

---

## Akun Demo (Default Password: `password`)

| Role | Email | Deskripsi |
|------|-------|-----------|
| **Developer** | `developer@example.com` | Akses penuh ke sistem dan konfigurasi. |
| **Owner** | `owner@example.com` | Pemilik kost, melihat laporan dan statistik. |
| **Admin** | `admin@example.com` | Pengelola operasional harian dan kamar. |
| **Tenant** | `tenant@example.com` | Penghuni kost, melihat status pemesanan. |

---

## Struktur Data Real-time (Demo)

Di dalam Dashboard, Anda akan menemukan bagian **"Test Real-time Features"**. Fitur ini disediakan untuk mendemonstrasikan kecanggihan integrasi Reverb & Livewire:
-   **Trigger Update Stats**: Mengirimkan event ke semua user yang sedang online untuk memperbarui angka di widget dashboard.
-   **Tambah Kamar**: Menambahkan data kamar baru ke database dan langsung memunculkan notifikasi di pojok kanan atas serta memperbarui grafik/statistik secara instan.

## Catatan Pengembangan
-   Aplikasi ini **tidak** menggunakan Laravel Breeze atau Jetstream agar desain Tabler tetap bersih dan fleksibel.
-   Aset Tabler disimpan secara lokal di `public/assets/tabler/` untuk performa loading yang lebih cepat tanpa ketergantungan CDN.
-   Broadcasting menggunakan driver `reverb` yang dikonfigurasi di `config/broadcasting.php`.
