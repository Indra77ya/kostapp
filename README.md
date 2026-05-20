# KostApp - Sistem Manajemen Kost Modern & Real-Time

KostApp adalah solusi manajemen operasional rumah kost yang dirancang untuk memberikan kemudahan bagi pemilik (Owner) dan efisiensi bagi pengelola (Admin). Dibangun dengan teknologi terbaru, aplikasi ini menawarkan pengalaman pengguna yang interaktif dan responsif secara real-time.

---

## Fitur Utama (Modul Lengkap)

Aplikasi ini mencakup seluruh siklus manajemen kost, mulai dari manajemen aset hingga operasional harian:

### 1. Operasional Sewa (Sewa)
-   **Check In (Registrasi)**:
    -   Pendaftaran penghuni baru dengan data lengkap (Foto Diri, KTP, KK).
    -   Pencatatan data instansi/kampus dan kontak darurat.
    -   Otomatisasi pembuatan akun login untuk penghuni.
    -   Generasi nomor registrasi otomatis (REG-DDMMYYYY-XXXX).
    -   Sinkronisasi otomatis status kamar menjadi 'Terisi'.
-   **Check Out**:
    -   Proses keluar penghuni dengan pencatatan tanggal dan catatan tambahan.
    -   Otomatisasi pengembalian status kamar menjadi 'Tersedia'.
-   **Pindah Kamar**:
    -   Manajemen transisi penghuni antar kamar dalam satu lokasi atau antar lokasi.
    -   Riwayat perpindahan yang tercatat lengkap.
    -   Penyesuaian harga sewa dan diskon secara dinamis.
-   **Daftar Penghuni**:
    -   Direktori lengkap seluruh penghuni aktif maupun yang sudah check-out.
    -   Fitur 'Lihat Data' untuk meninjau profil lengkap penghuni.
    -   Cetak Invoice dan Data Diri penghuni.

### 2. Master Data (Aset & Konfigurasi)
-   **Manajemen Lokasi**: Pengelolaan gedung atau cabang kost di berbagai titik.
-   **Manajemen Kamar**: Pengaturan nomor kamar, harga, tipe, dan fasilitas per kamar.
-   **Manajemen Fasilitas**: Daftar fasilitas (AC, WiFi, Kamar Mandi Dalam, dll) yang bisa dikaitkan ke kamar.
-   **Manajemen Aturan (Rules)**: Pengaturan tata tertib kost yang akan muncul di dokumen printout penghuni.
-   **Metode Pembayaran**: Pengaturan rekening bank, e-wallet, atau tunai untuk pembayaran sewa.
-   **Manajemen Pengguna**: Pengelolaan staf (Admin & Owner) dengan sistem hak akses yang ketat.

### 3. Pembayaran & Penagihan (Billing)
-   **Otomatisasi Tagihan**: Penjanaan tagihan otomatis saat check-in, termasuk dukungan untuk sewa bulanan, mingguan, harian, dan tahunan.
-   **Dukungan Diskon**: Pengaturan diskon dengan durasi tertentu (misal: diskon 3 bulan pertama) atau diskon tetap (Hingga Keluar).
-   **Lapor Pembayaran (Tenant)**: Fitur bagi penghuni untuk melihat daftar tagihan dan mengunggah bukti pembayaran secara mandiri.
-   **Konfirmasi Pembayaran (Admin)**: Dashboard khusus bagi admin untuk memvalidasi bukti pembayaran dari penghuni.
-   **Riwayat & Cicilan**: Pencatatan riwayat pembayaran yang mendukung sistem cicilan (pelunasan bertahap).
-   **Cetak Invoice & Kuitansi**: Generasi dokumen formal untuk tagihan (Invoice) dan bukti bayar (Kuitansi) dengan tanda air 'LUNAS'.

### 4. Dashboard & Notifikasi
-   **Statistik Interaktif**: Pantau jumlah kamar total, kamar tersedia, dan pesanan aktif secara visual.
-   **Notifikasi Real-Time**: Pemberitahuan instan untuk aktivitas penting (seperti pendaftaran baru atau laporan pembayaran) tanpa perlu refresh halaman.

---

## Tampilan Aplikasi (Screenshots)

> Ganti placeholder di bawah ini dengan gambar asli aplikasi Anda untuk presentasi yang lebih baik.

| Dashboard Utama | Daftar Kamar | Konfirmasi Bayar |
| :---: | :---: | :---: |
| ![Dashboard](https://via.placeholder.com/400x250?text=Dashboard+Stats) | ![Kamar](https://via.placeholder.com/400x250?text=Manajemen+Kamar) | ![Pembayaran](https://via.placeholder.com/400x250?text=Konfirmasi+Pembayaran) |

---

## Arsitektur & Teknologi

### Logika Bisnis Utama
1.  **Sinkronisasi Status Kamar**: Sistem menjamin integritas data kamar. Saat pendaftaran (Check-in) berhasil, status kamar otomatis berubah menjadi `occupied`. Saat Check-out atau penghapusan data registrasi, status kembali ke `available`.
2.  **Real-Time Engine**: Menggunakan **Laravel Reverb** (WebSocket) untuk mendorong pembaruan data dan notifikasi ke browser pengguna secara instan melalui event broadcasting.
3.  **Sistem Keamanan & Role**: Menggunakan **Spatie Permission**.
    -   `Developer/Owner`: Akses penuh ke seluruh sistem termasuk pengaturan sistem.
    -   `Admin`: Akses operasional dan finansial (Check-in, Check-out, Pindah Kamar, Penghuni, Konfirmasi Pembayaran).
    -   `Tenant (Penghuni)`: Akses terbatas untuk melihat profil sendiri, daftar tagihan, dan melaporkan pembayaran.

### Struktur Database Utama
-   `users`: Menyimpan data staf dan penghuni (beserta `password_plain` untuk kemudahan admin).
-   `locations`: Data gedung kost.
-   `rooms`: Data kamar beserta status dan relasi ke lokasi.
-   `registrations`: Data transaksi sewa aktif beserta informasi diskon.
-   `bills`: Data tagihan periodik penghuni.
-   `payments`: Data transaksi pembayaran, riwayat cicilan, dan bukti bayar.
-   `room_moves`: Log riwayat perpindahan kamar.
-   `payment_methods`: Pilihan metode pembayaran.

---

## Pengaturan & Pemeliharaan Sistem

Aplikasi ini dilengkapi dengan modul Pengaturan Sistem (khusus Owner/Developer) untuk menjaga keberlangsungan data:

-   **Statistik Data**: Melihat jumlah total User, Lokasi, Kamar, Fasilitas, dan Aturan.
-   **Backup Data**: Membuat file `.zip` yang berisi database dan seluruh foto unggahan (KTP, Foto Kamar, dll).
-   **Restore Data**: Mengembalikan kondisi aplikasi dari file backup yang telah dibuat sebelumnya.
-   **Reset Sistem**: Menghapus seluruh data operasional (transaksi, kamar, lokasi) dan menyisakan akun Owner/Developer untuk memulai dari awal.

---

## Panduan Instalasi (Lokal)

### Persyaratan
- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (Default) atau MySQL

### Langkah-langkah
1.  **Clone & Install**:
    ```bash
    git clone https://github.com/username/kostapp.git
    cd kostapp
    composer install
    npm install
    ```
2.  **Environment**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
3.  **Database**:
    ```bash
    touch database/database.sqlite
    php artisan migrate:fresh --seed
    php artisan storage:link
    ```
4.  **Build Assets**:
    ```bash
    npm run build
    ```

---

## Menjalankan Aplikasi

Untuk fitur real-time, jalankan 3 perintah ini di terminal terpisah:
1.  **Web Server**: `php artisan serve`
2.  **WebSocket**: `php artisan reverb:start`
3.  **Queue**: `php artisan queue:listen`

Akses di: `http://127.0.0.1:8000`
**Login Default**: `admin@example.com` / `password`

---

## Panduan Deployment (Produksi)

1.  Gunakan **Nginx** sebagai reverse proxy untuk port 80/443 ke port Reverb (8080).
2.  Gunakan **Supervisor** untuk menjaga `reverb:start` dan `queue:work` tetap berjalan di latar belakang.
3.  Pastikan `APP_DEBUG=false` dan `APP_URL` sudah sesuai di file `.env`.

---
Dibuat untuk efisiensi bisnis Kost Anda.
