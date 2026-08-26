<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ASET (ASSETS)
            [
                'code' => '1-1000',
                'name' => 'Kas Utama & Bank',
                'type' => 'asset',
                'sub_type' => 'Aset Lancar',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Rekening Kas Utama / Bank Penerima Pembayaran',
            ],
            [
                'code' => '1-1100',
                'name' => 'Kas Kecil / Operasional',
                'type' => 'asset',
                'sub_type' => 'Aset Lancar',
                'normal_balance' => 'debit',
                'category' => 'Kas & Setara Kas',
                'description' => 'Kas Tunai Pengeluaran Operasional Harian',
            ],
            [
                'code' => '1-2000',
                'name' => 'Piutang Sewa Kamar',
                'type' => 'asset',
                'sub_type' => 'Aset Lancar',
                'normal_balance' => 'debit',
                'category' => 'Piutang',
                'description' => 'Tagihan Sewa Kamar Yang Belum Dibayar Tenant',
            ],

            // LIABILITAS (LIABILITIES)
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
                'sub_type' => 'Liabilitas Jangka Pendek',
                'normal_balance' => 'credit',
                'category' => 'Kewajiban Jangka Pendek',
                'description' => 'Kewajiban Pembayaran Kepada Vendor / Pihak Ketiga',
            ],

            // EKUITAS (EQUITY)
            [
                'code' => '3-1000',
                'name' => 'Modal Pemilik',
                'type' => 'equity',
                'sub_type' => 'Ekuitas Pemilik',
                'normal_balance' => 'credit',
                'category' => 'Ekuitas Pemilik',
                'description' => 'Modal Disetor Oleh Pemilik Kos',
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

            // PENDAPATAN (REVENUE)
            [
                'code' => '4-1000',
                'name' => 'Pendapatan Sewa Kamar',
                'type' => 'revenue',
                'sub_type' => 'Pendapatan Usaha / Utama',
                'normal_balance' => 'credit',
                'category' => 'Pendapatan Usaha',
                'description' => 'Pendapatan Utama Dari Sewa Kamar Kos',
            ],
            [
                'code' => '4-2000',
                'name' => 'Pendapatan Denda & Tambahan',
                'type' => 'revenue',
                'sub_type' => 'Pendapatan Lain-lain',
                'normal_balance' => 'credit',
                'category' => 'Pendapatan Lain-lain',
                'description' => 'Pendapatan Dari Keterlambatan atau Layanan Ekstra',
            ],
            [
                'code' => '4-3000',
                'name' => 'Pendapatan Potongan Deposit / Klaim Kerusakan',
                'type' => 'revenue',
                'sub_type' => 'Pendapatan Lain-lain',
                'normal_balance' => 'credit',
                'category' => 'Pendapatan Lain-lain',
                'description' => 'Deposit Tenant Yang Dipotong Untuk Kerusakan Saat Check Out',
            ],

            // BEBAN (EXPENSES)
            [
                'code' => '5-1000',
                'name' => 'Beban Listrik, Air & Utility',
                'type' => 'expense',
                'sub_type' => 'Beban Operasional',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Tagihan PLN, PDAM, dan Utility Lainnya',
            ],
            [
                'code' => '5-1100',
                'name' => 'Beban Internet & Wi-Fi',
                'type' => 'expense',
                'sub_type' => 'Beban Operasional',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Biaya Langganan Internet & Networking Kos',
            ],
            [
                'code' => '5-2000',
                'name' => 'Beban Pemeliharaan & Perbaikan Gedung',
                'type' => 'expense',
                'sub_type' => 'Beban Pemeliharaan & Perbaikan',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Biaya Perbaikan AC, Kran, Cat, dan Bangunan',
            ],
            [
                'code' => '5-2100',
                'name' => 'Beban Kebersihan & Perlengkapan Kos',
                'type' => 'expense',
                'sub_type' => 'Beban Pemeliharaan & Perbaikan',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Biaya Alat Kebersihan, Sabun, Plastik Sampah, dll.',
            ],
            [
                'code' => '5-3000',
                'name' => 'Beban Gaji & Honor Staf',
                'type' => 'expense',
                'sub_type' => 'Beban Operasional',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Gaji Penjaga Kos, Petugas Kebersihan, Keamanan',
            ],
            [
                'code' => '5-4000',
                'name' => 'Beban Perizinan & Pajak Property',
                'type' => 'expense',
                'sub_type' => 'Beban Administrasi & Umum',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'PBB, Retribusi Daerah, dan Izin Usaha',
            ],
            [
                'code' => '5-5000',
                'name' => 'Beban Pemasaran & Promosi',
                'type' => 'expense',
                'sub_type' => 'Beban Operasional',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Iklan Sosial Media, Brosur, Spanduk',
            ],
            [
                'code' => '5-9000',
                'name' => 'Beban Operasional Lain-Lain',
                'type' => 'expense',
                'sub_type' => 'Beban Operasional',
                'normal_balance' => 'debit',
                'category' => 'Beban Operasional',
                'description' => 'Biaya Tak Terduga Lainnya',
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(['code' => $account['code']], $account);
        }
    }
}
