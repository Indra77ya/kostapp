<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Kost',
                'email' => 'admin@kost.com',
                'password' => Hash::make('password'),
                'password_plain' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Budi Pengelola',
                'email' => 'budi@kost.com',
                'password' => Hash::make('password'),
                'password_plain' => 'password',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);
            $user->assignRole($role);
        }
    }
}
