<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Room;
use App\Models\Registration;
use App\Models\Bill;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TenantDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('password'),
            'password_plain' => 'password',
        ]);
        $tenant->assignRole('tenant');

        $location = Location::first();
        $room = Room::where('location_id', $location->id)->first();

        $reg = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'status' => 'active',
            'total_price' => 1000000,
            'room_price' => 1000000,
            'duration_type' => 'monthly',
            'registration_number' => 'REG-001',
            'registration_date' => Carbon::now(),
            'stay_start_date' => Carbon::now(),
        ]);

        Bill::create([
            'registration_id' => $reg->id,
            'bill_number' => 'BILL-001',
            'description' => 'Sewa Kamar Juni',
            'amount' => 300000,
            'paid_amount' => 0,
            'status' => 'Belum Lunas',
            'due_date' => Carbon::now()->addDays(5),
        ]);
    }
}
