<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ==========================================
            // 1. ASET (ASSETS)
            // ==========================================
            // Kas & Bank
            [
                'code' => '1-1000',
                'name' => 'Kas Utama & Bank',
                'type' => 'asset',
                'sub_type' => 'Kas & Bank',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Rekening Kas Utama / Bank Penerima Pembayaran',
            ],
            [
                'code' => '1-1100',
                'name' => 'Kas Kecil / Operasional',
                'type' => 'asset',
                'sub_type' => 'Kas & Bank',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Kas Tunai Pengeluaran Operasional Harian',
            ],
            [
                'code' => '1-1200',
                'name' => 'Bank BCA',
                'type' => 'asset',
                'sub_type' => 'Kas & Bank',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Rekening Bank BCA Operasional Kos',
            ],
            [
                'code' => '1-1300',
                'name' => 'Bank Mandiri',
                'type' => 'asset',
                'sub_type' => 'Kas & Bank',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Rekening Bank Mandiri Operasional Kos',
            ],
            [
                'code' => '1-1400',
                'name' => 'Bank BRI',
                'type' => 'asset',
                'sub_type' => 'Kas & Bank',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Rekening Bank BRI Operasional Kos',
            ],
            [
                'code' => '1-1500',
                'name' => 'E-Wallet / QRIS',
                'type' => 'asset',
                'sub_type' => 'Kas & Bank',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Penampungan Pembayaran E-Wallet / QRIS',
            ],

            // Piutang
            [
                'code' => '1-2000',
                'name' => 'Piutang Sewa Kamar',
                'type' => 'asset',
                'sub_type' => 'Piutang Usaha',
                'normal_balance' => 'debit',
                'category' => 'Piutang Usaha',
                'description' => 'Tagihan Sewa Kamar Yang Belum Dibayar Tenant',
            ],
            [
                'code' => '1-2100',
                'name' => 'Piutang Lain-Lain Tenant',
                'type' => 'asset',
                'sub_type' => 'Piutang Usaha',
                'normal_balance' => 'debit',
                'category' => 'Piutang Usaha',
                'description' => 'Tagihan Denda, Laundry, atau Layanan Tambahan Tenant',
            ],

            // Perlengkapan & Biaya Dibayar Di Muka
            [
                'code' => '1-3000',
                'name' => 'Perlengkapan & Alat Kebersihan Kos',
                'type' => 'asset',
                'sub_type' => 'Perlengkapan & Persediaan',
                'normal_balance' => 'debit',
                'category' => 'Persediaan',
                'description' => 'Stok Alat Kebersihan, Sabun, Tissue, dan Perlengkapan Kos',
            ],
            [
                'code' => '1-4000',
                'name' => 'Sewa Gedung / Lahan Dibayar Di Muka',
                'type' => 'asset',
                'sub_type' => 'Biaya Dibayar Di Muka',
                'normal_balance' => 'debit',
                'category' => 'Aset Lancar Lainnya',
                'description' => 'Uang Sewa Lahan/Gedung Kos Yang Dibayar Di Muka',
            ],

            // Aset Tetap & Akumulasi Penyusutan
            [
                'code' => '1-7000',
                'name' => 'Bangunan Gedung Kos',
                'type' => 'asset',
                'sub_type' => 'Aset Tetap',
                'normal_balance' => 'debit',
                'category' => 'Aset Tetap',
                'description' => 'Nilai Bangunan Gedung Kos',
            ],
            [
                'code' => '1-7100',
                'name' => 'Peralatan & Meubel Kos (AC, Kasur, Lemari)',
                'type' => 'asset',
                'sub_type' => 'Aset Tetap',
                'normal_balance' => 'debit',
                'category' => 'Aset Tetap',
                'description' => 'Peralatan Elektonik & Meubelior Kamar Kos',
            ],
            [
                'code' => '1-7800',
                'name' => 'Akumulasi Penyusutan Bangunan',
                'type' => 'asset',
                'sub_type' => 'Akumulasi Penyusutan',
                'normal_balance' => 'credit',
                'category' => 'Aset Tetap',
                'description' => 'Akumulasi Penyusutan Gedung Kos',
            ],
            [
                'code' => '1-7900',
                'name' => 'Akumulasi Penyusutan Peralatan & Meubel',
                'type' => 'asset',
                'sub_type' => 'Akumulasi Penyusutan',
                'normal_balance' => 'credit',
                'category' => 'Aset Tetap',
                'description' => 'Akumulasi Penyusutan AC, TV, Kasur & Peralatan Kamar',
            ],

            // ==========================================
            // 2. LIABILITAS (LIABILITIES)
            // ==========================================
            [
                'code' => '2-1000',
                'name' => 'Utang Deposit Tenant (Uang Jaminan)',
                'type' => 'liability',
                'sub_type' => 'Liabilitas Jangka Pendek',
                'normal_balance' => 'credit',
                'category' => 'Kewajiban Jangka Pendek',
                'description' => 'Titipan Uang Jaminan / Deposit Milik Tenant',
            ],
            [
                'code' => '2-2000',
                'name' => 'Utang Usaha / Operasional',
                'type' => 'liability',
                'sub_type' => 'Utang Usaha',
                'normal_balance' => 'credit',
                'category' => 'Kewajiban Jangka Pendek',
                'description' => 'Kewajiban Pembayaran Kepada Vendor / Pihak Ketiga',
            ],
            [
                'code' => '2-3000',
                'name' => 'Utang Gaji & Honor Staf',
                'type' => 'liability',
                'sub_type' => 'Beban Yang Masih Harus Dibayar',
                'normal_balance' => 'credit',
                'category' => 'Kewajiban Jangka Pendek',
                'description' => 'Gaji / Honor Karyawan Yang Belum Dibayarkan',
            ],
            [
                'code' => '2-4000',
                'name' => 'Utang Pajak (PBB / Pajak Daerah)',
                'type' => 'liability',
                'sub_type' => 'Utang Pajak & Retribusi',
                'normal_balance' => 'credit',
                'category' => 'Kewajiban Jangka Pendek',
                'description' => 'Kewajiban Pajak Yang Belum Disetorkan',
            ],
            [
                'code' => '2-8000',
                'name' => 'Utang Bank Jangka Panjang',
                'type' => 'liability',
                'sub_type' => 'Liabilitas Jangka Panjang',
                'normal_balance' => 'credit',
                'category' => 'Kewajiban Jangka Panjang',
                'description' => 'Pinjaman Bank / Lembaga Keuangan Jangka Panjang',
            ],

            // ==========================================
            // 3. EKUITAS (EQUITY)
            // ==========================================
            [
                'code' => '3-1000',
                'name' => 'Modal Pemilik',
                'type' => 'equity',
                'sub_type' => 'Modal Disetor',
                'normal_balance' => 'credit',
                'category' => 'Ekuitas Pemilik',
                'description' => 'Modal Disetor Oleh Pemilik Kos',
            ],
            [
                'code' => '3-1100',
                'name' => 'Prive / Pengambilan Pemilik',
                'type' => 'equity',
                'sub_type' => 'Prive / Pengambilan Pemilik',
                'normal_balance' => 'debit',
                'category' => 'Ekuitas Pemilik',
                'description' => 'Pengambilan Uang Kos Untuk Keperluan Pribadi Pemilik',
            ],
            [
                'code' => '3-2000',
                'name' => 'Laba Ditahan',
                'type' => 'equity',
                'sub_type' => 'Laba Ditahan',
                'normal_balance' => 'credit',
                'category' => 'Ekuitas Pemilik',
                'description' => 'Akumulasi Laba/Rugi Periode Sebelumnya',
            ],
            [
                'code' => '3-3000',
                'name' => 'Laba Tahun Berjalan',
                'type' => 'equity',
                'sub_type' => 'Laba Tahun Berjalan',
                'normal_balance' => 'credit',
                'category' => 'Ekuitas Pemilik',
                'description' => 'Laba/Rugi Bersih Periode Tahun Berjalan',
            ],

            // ==========================================
            // 4. PENDAPATAN (REVENUE)
            // ==========================================
            [
                'code' => '4-1000',
                'name' => 'Pendapatan Sewa Kamar',
                'type' => 'revenue',
                'sub_type' => 'Pendapatan Utama / Sewa',
                'normal_balance' => 'credit',
                'category' => 'Pendapatan Utama',
                'description' => 'Pendapatan Utama Dari Sewa Kamar Kos',
            ],
            [
                'code' => '4-2000',
                'name' => 'Pendapatan Denda & Tambahan',
                'type' => 'revenue',
                'sub_type' => 'Pendapatan Denda & Administrasi',
                'normal_balance' => 'credit',
                'category' => 'Pendapatan Lain-lain',
                'description' => 'Pendapatan Dari Keterlambatan Pembayaran atau Denda',
            ],
            [
                'code' => '4-2100',
                'name' => 'Pendapatan Layanan Tambahan (Laundry/Parkir/Catering)',
                'type' => 'revenue',
                'sub_type' => 'Pendapatan Layanan / Service',
                'normal_balance' => 'credit',
                'category' => 'Pendapatan Layanan',
                'description' => 'Pendapatan Ekstra Dari Layanan Laundry, Parkir, Catering, dll.',
            ],
            [
                'code' => '4-3000',
                'name' => 'Pendapatan Potongan Deposit / Klaim Kerusakan',
                'type' => 'revenue',
                'sub_type' => 'Pendapatan Non-Operasional / Lain-lain',
                'normal_balance' => 'credit',
                'category' => 'Pendapatan Lain-lain',
                'description' => 'Deposit Tenant Yang Dipotong Untuk Kerusakan Saat Check Out',
            ],

            // ==========================================
            // 5. BEBAN (EXPENSES)
            // ==========================================
            // Utilitas
            [
                'code' => '5-1000',
                'name' => 'Beban Listrik, Air & Utility',
                'type' => 'expense',
                'sub_type' => 'Beban Utilitas',
                'normal_balance' => 'debit',
                'category' => 'Beban Utilitas',
                'description' => 'Tagihan PLN, PDAM, dan Utility Utama Kos',
            ],
            [
                'code' => '5-1100',
                'name' => 'Beban Internet & Wi-Fi',
                'type' => 'expense',
                'sub_type' => 'Beban Utilitas',
                'normal_balance' => 'debit',
                'category' => 'Beban Utilitas',
                'description' => 'Biaya Langganan Internet & Networking Kos',
            ],

            // Pemeliharaan & Kebersihan
            [
                'code' => '5-2000',
                'name' => 'Beban Pemeliharaan & Perbaikan Gedung',
                'type' => 'expense',
                'sub_type' => 'Beban Pemeliharaan & Perbaikan',
                'normal_balance' => 'debit',
                'category' => 'Beban Perbaikan',
                'description' => 'Biaya Perbaikan Bangunan, Cat, Pompa Air, Genteng, dll.',
            ],
            [
                'code' => '5-2100',
                'name' => 'Beban Perbaikan AC & Elektonik Kamar',
                'type' => 'expense',
                'sub_type' => 'Beban Pemeliharaan & Perbaikan',
                'normal_balance' => 'debit',
                'category' => 'Beban Perbaikan',
                'description' => 'Service AC, Perbaikan TV, Kulkas, Water Heater',
            ],
            [
                'code' => '5-2200',
                'name' => 'Beban Kebersihan, Sampah & Environment',
                'type' => 'expense',
                'sub_type' => 'Beban Kebersihan & Keamanan',
                'normal_balance' => 'debit',
                'category' => 'Beban Kebersihan',
                'description' => 'Iuran Sampah RT/RW, Pembelian Perlengkapan Kebersihan',
            ],
            [
                'code' => '5-2300',
                'name' => 'Beban Keamanan & Iuran Lingkungan',
                'type' => 'expense',
                'sub_type' => 'Beban Kebersihan & Keamanan',
                'normal_balance' => 'debit',
                'category' => 'Beban Keamanan',
                'description' => 'Iuran Keamanan Warga / Linmas, CCTV Maintenance',
            ],

            // SDM & Gaji
            [
                'code' => '5-3000',
                'name' => 'Beban Gaji Penjaga Kos & Kebersihan',
                'type' => 'expense',
                'sub_type' => 'Beban Gaji & Honor',
                'normal_balance' => 'debit',
                'category' => 'Beban SDM',
                'description' => 'Gaji Rutin Penjaga Kos, Petugas Kebersihan, Keamanan',
            ],
            [
                'code' => '5-3100',
                'name' => 'Beban Bonus & THR Staf',
                'type' => 'expense',
                'sub_type' => 'Beban Gaji & Honor',
                'normal_balance' => 'debit',
                'category' => 'Beban SDM',
                'description' => 'Bonus Kinerja & Tunjangan Hari Raya Karyawan',
            ],

            // Pemasaran, Perizinan & Adm
            [
                'code' => '5-4000',
                'name' => 'Beban Perizinan & Pajak Property (PBB)',
                'type' => 'expense',
                'sub_type' => 'Beban Administrasi & Umum',
                'normal_balance' => 'debit',
                'category' => 'Beban Pajak & Izin',
                'description' => 'PBB, Retribusi Daerah, dan Perizinan Usaha Kos',
            ],
            [
                'code' => '5-5000',
                'name' => 'Beban Pemasaran & Promosi (Iklan Kos)',
                'type' => 'expense',
                'sub_type' => 'Beban Pemasaran & Promosi',
                'normal_balance' => 'debit',
                'category' => 'Beban Pemasaran',
                'description' => 'Iklan Sosial Media, Portal Kos, Spanduk, Brosur',
            ],
            [
                'code' => '5-6000',
                'name' => 'Beban Administrasi & ATK',
                'type' => 'expense',
                'sub_type' => 'Beban Administrasi & Umum',
                'normal_balance' => 'debit',
                'category' => 'Beban Administrasi',
                'description' => 'Kertas, Kuitansi, Foto KTP Tenant, Materai',
            ],
            [
                'code' => '5-6100',
                'name' => 'Beban Administrasi Bank',
                'type' => 'expense',
                'sub_type' => 'Beban Administrasi & Umum',
                'normal_balance' => 'debit',
                'category' => 'Beban Administrasi',
                'description' => 'Biaya Admin Bank / Transfer Antar Bank / Layanan Perbankan',
            ],

            // Penyusutan & Lain-Lain
            [
                'code' => '5-7000',
                'name' => 'Beban Penyusutan Peralatan & Meubel',
                'type' => 'expense',
                'sub_type' => 'Beban Penyusutan',
                'normal_balance' => 'debit',
                'category' => 'Beban Penyusutan',
                'description' => 'Penyusutan Bulanan Peralatan & Furniture Kos',
            ],
            [
                'code' => '5-7100',
                'name' => 'Beban Penyusutan Bangunan Gedung',
                'type' => 'expense',
                'sub_type' => 'Beban Penyusutan',
                'normal_balance' => 'debit',
                'category' => 'Beban Penyusutan',
                'description' => 'Penyusutan Bulanan Bangunan Kos',
            ],
            [
                'code' => '5-9000',
                'name' => 'Beban Operasional Lain-Lain',
                'type' => 'expense',
                'sub_type' => 'Beban Operasional',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Biaya Operasional Tak Terduga Lainnya',
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(['code' => $account['code']], $account);
        }
    }
}
