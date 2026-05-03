<?php

namespace Database\Seeders;

use App\Models\Rule;
use App\Models\Location;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = Location::all();

        $rules = [
            [
                'title' => 'Jam Malam',
                'description' => 'Pintu gerbang ditutup pukul 23:00 WIB.',
                'category' => 'Keamanan',
                'location_id' => null, // Global rule
                'is_active' => true,
            ],
            [
                'title' => 'Dilarang Merokok',
                'description' => 'Dilarang merokok di dalam kamar.',
                'category' => 'Kebersihan',
                'location_id' => null, // Global rule
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            Rule::create($rule);
        }

        if ($locations->count() > 0) {
            Rule::create([
                'title' => 'Parkir Motor',
                'description' => 'Parkir motor harus rapi di tempat yang disediakan.',
                'category' => 'Umum',
                'location_id' => $locations->first()->id,
                'is_active' => true,
            ]);
        }
    }
}
