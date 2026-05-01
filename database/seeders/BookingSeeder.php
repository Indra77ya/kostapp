<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = User::whereEmail('tenant@example.com')->first();
        $room = Room::where('room_number', '101')->first();

        if ($tenant && $room) {
            Booking::create([
                'user_id' => $tenant->id,
                'room_id' => $room->id,
                'check_in' => now()->startOfMonth(),
                'check_out' => now()->addMonth()->endOfMonth(),
                'total_price' => $room->price,
                'status' => 'confirmed',
            ]);
        }
    }
}
