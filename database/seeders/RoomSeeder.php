<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['room_number' => '101', 'price' => 1500000, 'status' => 'occupied', 'description' => 'Kamar lantai 1 dengan AC', 'room_type' => 'Reguler', 'floor' => 1, 'facilities' => 'AC, Wifi, Kamar Mandi Dalam'],
            ['room_number' => '102', 'price' => 1500000, 'status' => 'available', 'description' => 'Kamar lantai 1 dengan AC', 'room_type' => 'Reguler', 'floor' => 1, 'facilities' => 'AC, Wifi, Kamar Mandi Dalam'],
            ['room_number' => '201', 'price' => 1200000, 'status' => 'available', 'description' => 'Kamar lantai 2 non-AC', 'room_type' => 'Ekonomi', 'floor' => 2, 'facilities' => 'Wifi, Kamar Mandi Luar'],
            ['room_number' => '202', 'price' => 1200000, 'status' => 'maintenance', 'description' => 'Sedang dalam perbaikan cat', 'room_type' => 'Ekonomi', 'floor' => 2, 'facilities' => 'Wifi, Kamar Mandi Luar'],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
