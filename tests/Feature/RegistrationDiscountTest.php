<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_calculates_discounted_total_correctly()
    {
        $user = User::factory()->create();
        $location = Location::create(['name' => 'Test Loc', 'address' => 'Test Addr']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'status' => 'available',
            'price_monthly' => 1000000
        ]);

        $registration = Registration::create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 12,
            'is_open_ended' => false,
            'room_price' => 1000000,
            'discount_type' => 'fixed',
            'discount_value' => 100000,
            'discount_duration' => 6,
            'is_discount_open_ended' => false,
            'total_price' => 0, // Placeholder
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01'
        ]);

        $registration->syncBills();

        // 6 months at 900k, 6 months at 1000k = 5.4M + 6M = 11.4M
        $this->assertEquals(11400000, $registration->bills()->sum('amount'));
    }
}
