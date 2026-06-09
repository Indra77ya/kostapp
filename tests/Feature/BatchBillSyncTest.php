<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchBillSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_ended_registration_appends_batch_when_all_paid()
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
            'stay_start_date' => now()->subMonths(1),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'is_open_ended' => true,
            'room_price' => 1000000,
            'total_price' => 12000000,
            'status' => 'active',
            'identity_type' => 'KTP',
            'identity_number' => '123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01'
        ]);

        $registration->syncBills();
        $this->assertEquals(12, $registration->bills()->count());

        // Mark all as paid
        $registration->bills()->update(['status' => 'Lunas', 'paid_amount' => 1000000]);

        // Sync again
        $registration->syncBills();
        $this->assertEquals(24, $registration->bills()->count());

        // Ensure the new ones are "Belum Lunas"
        $this->assertEquals(12, $registration->bills()->where('status', 'Belum Lunas')->count());
    }
}
