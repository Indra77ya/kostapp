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
    -   **Onboarding WhatsApp**: Mengirimkan rincian akun dan informasi check-in secara otomatis ke nomor WhatsApp penghuni.
-   **Check Out**:
    -   Proses keluar penghuni dengan pencatatan tanggal dan catatan tambahan.
    -   Otomatisasi pengembalian status kamar menjadi 'Tersedia'.
    -   Validasi keuangan untuk memastikan tidak ada tunggakan sebelum check-out final.
-   **Pindah Kamar**:
    -   Manajemen transisi penghuni antar kamar dalam satu lokasi atau antar lokasi.
    -   Riwayat perpindahan yang tercatat lengkap.
    -   Penyesuaian harga sewa dan diskon secara dinamis mengikuti konfigurasi kamar baru.
-   **Daftar Penghuni**:
    -   Direktori lengkap seluruh penghuni aktif maupun yang sudah check-out.
    -   Cetak Invoice, Data Diri, dan Kartu Penghuni.

### 2. Master Data (Aset & Konfigurasi)
-   **Manajemen Lokasi**: Pengelolaan gedung atau cabang kost dengan dukungan unggah foto lokasi.
-   **Manajemen Kamar**: Pengaturan nomor kamar, harga, tipe, dan fasilitas per kamar.
-   **Manajemen Fasilitas**: Daftar fasilitas (AC, WiFi, dll) dengan ikon yang dapat dikustomisasi.
-   **Manajemen Aturan (Rules)**: Pengaturan tata tertib kost menggunakan Rich Text Editor (CKEditor).
-   **Metode Pembayaran**: Pengaturan rekening bank, e-wallet, atau tunai. UI secara dinamis menyesuaikan label (misal: 'Bank' vs 'No. Rekening' atau 'E-Wallet' vs 'No. HP').
-   **Manajemen Pengguna**: Pengelolaan staf (Admin & Owner) dengan sistem hak akses yang ketat.

### 3. Pembayaran & Penagihan (Billing)
-   **Otomatisasi Tagihan**: Pembuatan tagihan otomatis saat check-in (bulanan, mingguan, harian, tahunan). Mendukung opsi masa inap tetap atau "Hingga Keluar".
-   **Dukungan Diskon**: Pengaturan diskon dengan durasi tertentu (misal: diskon 3 bulan pertama) atau diskon tetap.
-   **Sinkronisasi Penagihan Pintar**: Tagihan yang belum lunas akan otomatis diperbarui jika terjadi perubahan harga kamar atau konfigurasi diskon pada data registrasi.
-   **Lapor Pembayaran (Tenant)**: Fitur bagi penghuni untuk melihat daftar tagihan dan mengunggah bukti pembayaran secara mandiri.
-   **Konfirmasi Pembayaran (Admin)**: Dashboard khusus bagi admin untuk memvalidasi bukti pembayaran. Dukungan untuk rincian bank pengirim dan nomor rekening/HP.
-   **Riwayat & Cicilan**: Pencatatan riwayat pembayaran yang mendukung sistem cicilan dengan rincian saldo yang jelas pada kuitansi.
-   **Cetak Kuitansi Digital**: Kuitansi dengan tanda air dinamis ('LUNAS', 'CICILAN', 'BELUM LUNAS') dan riwayat pembayaran terlampir.

### 4. Dashboard & Notifikasi
-   **Statistik Interaktif**: Pantau jumlah kamar total, kamar tersedia, dan pesanan aktif secara visual.
-   **Notifikasi Real-Time**: Pemberitahuan instan menggunakan Laravel Reverb untuk aktivitas penting (pendaftaran baru, laporan pembayaran) tanpa refresh halaman.
-   **Smart Filtering**: Filter data yang responsif dan reaktif di seluruh modul manajemen (Status, Lokasi, Tipe Sewa, Urutan).

---

## Tampilan Aplikasi (Screenshots)

| Dashboard Utama | Daftar Kamar | Konfirmasi Bayar |
| :---: | :---: | :---: |
| ![Dashboard](https://via.placeholder.com/400x250?text=Dashboard+Stats) | ![Kamar](https://via.placeholder.com/400x250?text=Manajemen+Kamar) | ![Pembayaran](https://via.placeholder.com/400x250?text=Konfirmasi+Pembayaran) |

---

## Arsitektur & Teknologi

### Stack Teknologi
-   **Backend**: Laravel 11.x (PHP 8.2+)
-   **Frontend**: Livewire 3.5+ (Full TALL Stack experience)
-   **UI Framework**: Tabler (Bootstrap 5 based) dengan Tailwind CSS untuk utilitas tambahan.
-   **Database**: SQLite (Default) / MySQL / PostgreSQL.
-   **Real-Time**: Laravel Reverb (WebSocket) & Laravel Echo.
-   **Security**: Spatie Laravel Permission (Role: Developer, Owner, Admin, Tenant).
-   **Editor**: CKEditor 5 (Classic) untuk manajemen aturan dan deskripsi.
-   **Assets**: Vite (Laravel Vite Plugin).

### Logika Bisnis & Keamanan
1.  **Integritas Data**: Penghapusan data registrasi secara otomatis membersihkan file fisik terkait (Foto KTP, Diri, dll) untuk efisiensi penyimpanan.
2.  **Restriksi Hapus**: Data registrasi yang sudah memiliki riwayat pembayaran tidak dapat dihapus untuk menjaga validitas laporan keuangan.
3.  **Data Isolation**: Tenant hanya dapat mengakses invoice dan data miliknya sendiri melalui controller-level authorization.
4.  **System Reset**: Fitur reset sistem cerdas yang mendukung penghapusan data secara selektif (Operasional, Master Data, atau Akun Tenant) dengan logika cascading yang aman.

---

## Pengaturan & Pemeliharaan Sistem

Aplikasi ini dilengkapi dengan modul Pengaturan Sistem (khusus Owner/Developer):

-   **Backup Data**: Membuat file `.zip` yang berisi database dan seluruh media unggahan.
-   **Restore Data**: Mengembalikan kondisi aplikasi dari file backup.
-   **Reset Sistem**: Menghapus data operasional/master untuk memulai siklus bisnis baru.
-   **Manajemen Credential**: Menyimpan `password_plain` (terenkripsi secara sistem namun dapat dilihat admin) untuk memudahkan edukasi akses bagi penghuni baru.

---

## Panduan Instalasi (Lokal)

### Persyaratan
- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (Default)

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
3.  **Database & Storage**:
    ```bash
    touch database/database.sqlite
    php artisan migrate --seed
    php artisan storage:link
    ```
4.  **Build Assets**:
    ```bash
    npm run build
    ```

---

## Menjalankan Aplikasi

Untuk pengalaman penuh (termasuk fitur real-time), jalankan perintah berikut:

1.  **Mode Pengembangan (All-in-one)**:
    ```bash
    npm run dev
    ```
    *(Menjalankan server, queue, logs, dan vite secara bersamaan)*

2.  **Atau Terminal Terpisah**:
    -   Web Server: `php artisan serve`
    -   WebSocket: `php artisan reverb:start`
    -   Queue: `php artisan queue:listen`

Akses di: `http://127.0.0.1:8000`
**Login Default**: `admin@kost.com` / `password`

---

## Panduan Deployment (Produksi)

1.  Gunakan **Nginx** sebagai reverse proxy untuk port 80/443.
2.  Konfigurasi **Supervisor** untuk menjaga proses `reverb:start` dan `queue:work` tetap berjalan.
3.  Gunakan `php artisan optimize` dan `npm run build` untuk performa maksimal.

---
Dibuat dengan ❤️ untuk efisiensi bisnis Kost Anda.
