<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code'=>'1100','name'=>'Kas','type'=>'asset'],
            ['code'=>'1110','name'=>'Bank','type'=>'asset'],
            ['code'=>'1120','name'=>'Piutang Usaha','type'=>'asset'],
            ['code'=>'2100','name'=>'Uang Muka/Deposit Penyewa','type'=>'liability'],
            ['code'=>'2200','name'=>'Utang Usaha','type'=>'liability'],
            ['code'=>'3100','name'=>'Modal','type'=>'equity'],
            ['code'=>'3200','name'=>'Laba Ditahan','type'=>'equity'],
            ['code'=>'4100','name'=>'Pendapatan Sewa','type'=>'revenue'],
            ['code'=>'4200','name'=>'Pendapatan Lain-lain','type'=>'revenue'],
            ['code'=>'5100','name'=>'Biaya Operasional','type'=>'expense'],
            ['code'=>'5110','name'=>'Biaya Listrik/Air','type'=>'expense'],
            ['code'=>'5120','name'=>'Biaya Perawatan','type'=>'expense'],
        ];

        foreach ($accounts as $a) {
            Account::firstOrCreate(['code' => $a['code']], $a);
        }
    }
}
