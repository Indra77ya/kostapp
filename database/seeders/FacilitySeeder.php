<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            ['name' => 'AC', 'category' => 'Kamar'],
            ['name' => 'WiFi', 'category' => 'Kamar'],
            ['name' => 'Kamar Mandi Dalam', 'category' => 'Kamar'],
            ['name' => 'Kamar Mandi Luar', 'category' => 'Kamar'],
            ['name' => 'Tempat Tidur', 'category' => 'Kamar'],
            ['name' => 'Lemari Pakaian', 'category' => 'Kamar'],
            ['name' => 'Parkir Motor', 'category' => 'Umum'],
            ['name' => 'Parkir Mobil', 'category' => 'Umum'],
            ['name' => 'Dapur Bersama', 'category' => 'Umum'],
            ['name' => 'CCTV', 'category' => 'Umum'],
            ['name' => 'Laundry', 'category' => 'Umum'],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }
    }
}
