<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Bank Central Asia (BCA)',
                'category' => 'Bank',
                'account_number' => '1234567890',
                'account_name' => 'PT Kost Ceria Indonesia',
                'instructions' => '<p>1. Masukkan kartu ATM BCA</p><p>2. Pilih Transfer</p><p>3. Masukkan nomor rekening</p>',
                'is_active' => true,
            ],
            [
                'name' => 'GoPay',
                'category' => 'E-Wallet',
                'account_number' => '08123456789',
                'account_name' => 'Kost Ceria',
                'instructions' => '<p>1. Buka aplikasi Gojek</p><p>2. Pilih Bayar</p><p>3. Masukkan nomor HP</p>',
                'is_active' => true,
            ],
            [
                'name' => 'Tunai',
                'category' => 'Tunai',
                'account_number' => '-',
                'account_name' => 'Pengelola Kost',
                'instructions' => '<p>Bayar langsung ke pengelola di lokasi.</p>',
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}
