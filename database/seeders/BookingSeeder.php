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
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first();
        $room = Room::where('room_number', '101')->first();

        if ($admin && $room) {
            Booking::create([
                'user_id' => $admin->id,
                'room_id' => $room->id,
                'check_in' => now()->startOfMonth(),
                'check_out' => now()->addMonth()->endOfMonth(),
                'total_price' => $room->price_monthly,
                'status' => 'confirmed',
            ]);
        }
    }
}
