<?php

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class VerificationSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = User::create([
            'name' => 'Indra Nur Utomo',
            'email' => 'indra@example.com',
            'password' => Hash::make('password'),
            'password_plain' => 'password',
            'phone_number' => '08123456789',
        ]);
        $tenant->assignRole('tenant');

        $location = Location::first();
        $room = Room::where('location_id', $location->id)->where('status', 'available')->first();

        if ($room) {
            $room->update(['status' => 'occupied']);
            Registration::create([
                'user_id' => $tenant->id,
                'location_id' => $location->id,
                'room_id' => $room->id,
                'registration_number' => 'REG-001',
                'registration_date' => Carbon::now(),
                'stay_start_date' => Carbon::now()->addDays(1),
                'duration_type' => 'monthly',
                'duration_value' => 3,
                'is_open_ended' => false,
                'room_price' => 1500000,
                'discount_type' => 'fixed',
                'discount_value' => 100000,
                'discount_duration' => 1,
                'is_discount_open_ended' => false,
                'total_price' => 4400000,
                'status' => 'active',
                'identity_type' => 'KTP',
                'identity_number' => '1234567890',
                'gender' => 'Laki-laki',
                'birth_date' => '2001-07-20'
            ]);
        }
    }
}
