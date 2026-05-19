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
            [
                'room_number' => '101',
                'price_daily' => 100000,
                'price_weekly' => 600000,
                'price_monthly' => 1500000,
                'price_yearly' => 15000000,
                'status' => 'available',
                'description' => 'Kamar lantai 1 dengan AC',
                'room_type' => 'Reguler',
                'floor' => 1,
                'facilities' => 'AC, WiFi, Kamar Mandi Dalam',
                'location_id' => $location1Id
            ],
            [
                'room_number' => '102',
                'price_daily' => 150000,
                'price_weekly' => 800000,
                'price_monthly' => 1800000,
                'price_yearly' => 18000000,
                'status' => 'available',
                'description' => 'Kamar lantai 1 Premium',
                'room_type' => 'VIP',
                'floor' => 1,
                'facilities' => 'AC, WiFi, TV, Kamar Mandi Dalam',
                'location_id' => $location1Id
            ],
            [
                'room_number' => '201',
                'price_daily' => 75000,
                'price_weekly' => 450000,
                'price_monthly' => 1200000,
                'price_yearly' => 12000000,
                'status' => 'available',
                'description' => 'Kamar lantai 2 non-AC',
                'room_type' => 'Ekonomi',
                'floor' => 2,
                'facilities' => 'WiFi, Kamar Mandi Luar',
                'location_id' => $location2Id
            ],
            [
                'room_number' => '202',
                'price_daily' => 80000,
                'price_weekly' => 500000,
                'price_monthly' => 1300000,
                'price_yearly' => 13000000,
                'status' => 'available',
                'description' => 'Kamar lantai 2 dengan AC',
                'room_type' => 'Reguler',
                'floor' => 2,
                'facilities' => 'AC, WiFi, Kamar Mandi Dalam',
                'location_id' => $location2Id
            ],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
