# KostApp - Sistem Manajemen Kost Real-Time

Selamat datang di **KostApp**! Sebuah platform manajemen rumah kost yang modern, cepat, dan interaktif. Aplikasi ini dibangun dengan teknologi terbaru di ekosistem Laravel untuk memastikan pengelolaan kost menjadi lebih mudah bagi pemilik dan nyaman bagi penghuni.

Aplikasi ini sangat spesial karena mendukung fitur **Real-Time**. Artinya, semua perubahan data (seperti penambahan kamar atau konfirmasi pembayaran) akan muncul secara instan di layar tanpa perlu menekan tombol refresh halaman (F5).

---

## 🚀 Fitur Unggulan

-   **Dashboard Interaktif**: Statistik jumlah kamar dan tenant yang terupdate otomatis secara instan.
-   **Notifikasi Instan**: Muncul lonceng notifikasi di pojok kanan atas setiap kali ada aktivitas penting.
-   **Multi-Role**: Sistem akses berbeda untuk Owner (Pemilik), Admin (Pengelola), dan Tenant (Penghuni).
-   **Tanpa Refresh**: Berkat teknologi Livewire dan Laravel Reverb, aplikasi terasa seperti aplikasi mobile yang responsif.
-   **Desain Modern**: Menggunakan framework Tabler yang bersih, profesional, dan nyaman di mata.

---

## 🛠️ Teknologi yang Digunakan

-   **Laravel 11**: Framework PHP tercanggih saat ini.
-   **Livewire 3**: Untuk membuat antarmuka yang dinamis tanpa menulis banyak kode JavaScript.
-   **Laravel Reverb**: Mesin utama di balik fitur real-time (WebSocket).
-   **Tabler Dashboard**: Template dashboard premium yang elegan.
-   **Spatie Permission**: Untuk mengelola hak akses setiap role.
-   **SQLite/MySQL**: Pilihan database yang fleksibel.

---

## 💻 Panduan Instalasi (Untuk Pemula)

Bagi Anda yang baru pertama kali menjalankan proyek Laravel, ikuti langkah-langkah detail berikut ini:

### 1. Persiapan Awal
Pastikan komputer Anda sudah terinstall:
-   **PHP** (Versi 8.2 atau lebih tinggi)
-   **Composer** (Manajer paket untuk PHP)
-   **Node.js & NPM** (Untuk memproses tampilan/frontend)
-   **Git** (Untuk mendownload kode)

### 2. Persiapkan Folder Proyek
Buka terminal atau Command Prompt (CMD), lalu masuk ke folder proyek Anda:
```bash
cd kostapp
```

### 3. Instal 'Otak' Aplikasi (Backend)
Perintah ini akan mendownload semua pustaka yang dibutuhkan oleh Laravel:
```bash
composer install
```

### 4. Setup File Konfigurasi (.env)
Buat file rahasia untuk pengaturan aplikasi:
```bash
cp .env.example .env
```
Setelah itu, buat "Kunci Keamanan" untuk aplikasi Anda:
```bash
php artisan key:generate
```

### 5. Setup Database
Buat file database kosong (jika menggunakan SQLite):
```bash
touch database/database.sqlite
```
Kemudian buat tabel-tabel dan isi data contoh (seperti akun login):
```bash
php artisan migrate:fresh --seed
```

### 6. Instal 'Tampilan' Aplikasi (Frontend)
Download dan bangun aset tampilan seperti CSS dan JavaScript:
```bash
npm install
npm run build
```

---

## 🚦 Cara Menjalankan Aplikasi di Lokal

Untuk melihat fitur real-time berjalan sempurna, Anda perlu membuka **TIGA TERMINAL** sekaligus:

### Terminal 1: Menjalankan Server Web
```bash
php artisan serve
```
Buka browser dan ketik: `http://127.0.0.1:8000`

### Terminal 2: Menjalankan Mesin Real-Time (Reverb)
```bash
php artisan reverb:start
```
*Pastikan terminal ini tetap terbuka agar notifikasi bisa muncul secara otomatis.*

### Terminal 3: Menjalankan Antrean (Queue)
```bash
php artisan queue:listen
```
*Gunakan terminal ini jika Anda memiliki proses latar belakang seperti pengiriman email atau pengolahan data berat.*

---

## ☁️ Panduan Deployment ke Server (Produksi)

Jika Anda ingin mengonlinekan aplikasi ini agar bisa diakses orang lain melalui internet, ikuti panduan ini:

### 1. Persiapan VPS
Gunakan VPS (Ubuntu 22.04 ke atas sangat direkomendasikan) dan install **Nginx**, **PHP-FPM**, **MySQL**, dan **Node.js**.

### 2. Konfigurasi .env (Sangat Penting!)
Ubah variabel berikut di server:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

# Reverb untuk Server
REVERB_HOST=domain-anda.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### 3. Konfigurasi Nginx (Reverse Proxy)
Agar Reverb bisa diakses melalui port 443 (HTTPS), Anda perlu menambahkan konfigurasi proxy di Nginx untuk path `/app`:
```nginx
location /app {
    proxy_http_version 1.1;
    proxy_set_header Host $http_host;
    proxy_set_header Scheme $scheme;
    proxy_set_header SERVER_PORT $server_port;
    proxy_set_header REMOTE_ADDR $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";

    proxy_pass http://127.0.0.1:8080;
}
```

### 4. Menjalankan Reverb sebagai Background Service
Gunakan **Supervisor** agar server Reverb tetap berjalan meskipun Anda keluar dari server:
1. Buat file `/etc/supervisor/conf.d/reverb.conf`:
```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/kostapp/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/kostapp/storage/logs/reverb.log
```
2. Update supervisor: `supervisorctl update && supervisorctl start reverb`.

### 5. Menjalankan Queue Worker di Server
Sama seperti Reverb, Queue juga harus dijalankan di latar belakang:
1. Buat file `/etc/supervisor/conf.d/queue.conf`:
```ini
[program:queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/kostapp/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/kostapp/storage/logs/queue.log
```
2. Jalankan: `supervisorctl update && supervisorctl start queue`.

---

## 🔑 Akun Demo Login
- **Email**: `admin@example.com`
- **Password**: `password`

---

## ❓ FAQ & Troubleshooting

- **Error: Database file does not exist**: Pastikan Anda sudah menjalankan `touch database/database.sqlite` dan variabel `DB_DATABASE` di `.env` sudah dikosongkan.
- **Notifikasi Tidak Muncul**: Pastikan perintah `php artisan reverb:start` sedang berjalan di terminal Anda.
- **Tampilan Berantakan**: Pastikan Anda sudah menjalankan `npm install` dan `npm run build`.

---
Dibuat dengan ❤️ untuk kemudahan manajemen kost Anda.
