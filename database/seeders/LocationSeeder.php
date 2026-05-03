<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Kost Ceria I',
                'address' => 'Jl. Mawar No. 123, Jakarta Selatan',
                'google_maps_link' => 'https://maps.google.com',
                'phone' => '08123456789',
                'description' => 'Kost nyaman dekat pusat kota.',
            ],
            [
                'name' => 'Kost Ceria II',
                'address' => 'Jl. Melati No. 45, Jakarta Barat',
                'google_maps_link' => 'https://maps.google.com',
                'phone' => '08987654321',
                'description' => 'Kost tenang dengan fasilitas lengkap.',
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
