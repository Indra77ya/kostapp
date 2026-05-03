<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Location;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = Location::all();
        $location1Id = $locations->skip(0)->first()?->id;
        $location2Id = $locations->skip(1)->first()?->id;

        $rooms = [
            ['room_number' => '101', 'price' => 1500000, 'status' => 'occupied', 'description' => 'Kamar lantai 1 dengan AC', 'room_type' => 'Reguler', 'floor' => 1, 'facilities' => 'AC, WiFi, Kamar Mandi Dalam', 'location_id' => $location1Id],
            ['room_number' => '102', 'price' => 1500000, 'status' => 'available', 'description' => 'Kamar lantai 1 dengan AC', 'room_type' => 'Reguler', 'floor' => 1, 'facilities' => 'AC, WiFi, Kamar Mandi Dalam', 'location_id' => $location1Id],
            ['room_number' => '201', 'price' => 1200000, 'status' => 'available', 'description' => 'Kamar lantai 2 non-AC', 'room_type' => 'Ekonomi', 'floor' => 2, 'facilities' => 'WiFi, Kamar Mandi Luar', 'location_id' => $location2Id],
            ['room_number' => '202', 'price' => 1200000, 'status' => 'maintenance', 'description' => 'Sedang dalam perbaikan cat', 'room_type' => 'Ekonomi', 'floor' => 2, 'facilities' => 'WiFi, Kamar Mandi Luar', 'location_id' => $location2Id],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
