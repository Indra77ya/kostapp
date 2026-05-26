<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'owner@kost.com',
            'password' => Hash::make('password'),
            'password_plain' => 'password',
        ]);
        $user->assignRole('owner');
    }
}
