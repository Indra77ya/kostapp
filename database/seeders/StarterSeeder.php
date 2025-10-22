<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Location;
use App\Models\RoomType;
use App\Models\Room;

class StarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // 1) Room Types
            $kostType = RoomType::firstOrCreate(
                ['name' => 'Kost Standar'],
                ['billing_cycle' => 'monthly', 'base_price' => 1000000] // Rp 1.000.000/bln contoh
            );

            $homestayType = RoomType::firstOrCreate(
                ['name' => 'Homestay'],
                ['billing_cycle' => 'daily', 'base_price' => 250000] // Rp 250.000/hari contoh
            );

            // 2) Locations
            $kost = Location::firstOrCreate(
                ['code' => 'KOST-A'],
                [
                    'name' => 'Kost Utama A',
                    'type' => 'kost',
                    'default_room_quota' => 40,
                    'address' => 'Jl. Merpati No. 1',
                    'wa_group_link' => null,
                ]
            );

            $hstay = Location::firstOrCreate(
                ['code' => 'HMS-1'],
                [
                    'name' => 'Homestay Cemara 1',
                    'type' => 'homestay',
                    'default_room_quota' => 28,
                    'address' => 'Jl. Cemara No. 2',
                    'wa_group_link' => null,
                ]
            );

            // 3) Generate Rooms sesuai kuota (aman dari duplikasi)
            if ($kost->rooms()->count() === 0) {
                for ($i = 1; $i <= $kost->default_room_quota; $i++) {
                    Room::create([
                        'location_id'  => $kost->id,
                        'room_type_id' => $kostType->id,
                        'number'       => 'A-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'status'       => 'available',
                        'floor'        => ceil($i / 10), // contoh: tiap 10 kamar ganti lantai
                    ]);
                }
            }

            if ($hstay->rooms()->count() === 0) {
                for ($i = 1; $i <= $hstay->default_room_quota; $i++) {
                    Room::create([
                        'location_id'  => $hstay->id,
                        'room_type_id' => $homestayType->id,
                        'number'       => 'H-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'status'       => 'available',
                        'floor'        => ceil($i / 7), // contoh: 7 kamar per lantai
                    ]);
                }
            }
        });
    }
}
