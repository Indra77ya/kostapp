<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchBillSyncTest extends TestCase
{
    use RefreshDatabase;

    private function createLocation()
    {
        return Location::create([
            'name' => 'Test Location ' . uniqid(),
            'address' => 'Test Address',
        ]);
    }

    private function createRoom($locationId)
    {
        return Room::create([
            'location_id' => $locationId,
            'room_number' => 'RM-' . uniqid(),
            'type' => 'Reguler',
            'price_monthly' => 1000000,
            'status' => 'available',
        ]);
    }

    private function getBaseData($user, $location, $room)
    {
        return [
            'user_id' => $user->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-' . uniqid(),
            'registration_date' => Carbon::now(),
            'stay_start_date' => Carbon::now(),
            'duration_type' => 'monthly',
            'is_open_ended' => true,
            'room_price' => 1000000,
            'total_price' => 12000000,
            'status' => 'active',
            'identity_type' => 'KTP',
            'identity_number' => '123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01',
        ];
    }

    public function test_open_ended_registration_generates_initial_batch()
    {
        $user = User::factory()->create();
        $location = $this->createLocation();
        $room = $this->createRoom($location->id);

        $data = $this->getBaseData($user, $location, $room);
        $registration = Registration::create($data);

        $registration->syncBills();

        // Monthly batch size is 12
        $this->assertEquals(12, $registration->bills()->count());
    }

    public function test_open_ended_registration_adds_new_batch_when_last_bill_past_due()
    {
        $user = User::factory()->create();
        $location = $this->createLocation();
        $room = $this->createRoom($location->id);

        // Start stay 13 months ago so the first batch of 12 is already past due
        $stayStart = Carbon::now()->subMonths(13);

        $data = $this->getBaseData($user, $location, $room);
        $data['registration_date'] = $stayStart;
        $data['stay_start_date'] = $stayStart;

        $registration = Registration::create($data);

        // First sync to generate initial 12
        $registration->syncBills();
        $this->assertEquals(12, $registration->bills()->count());

        // Second sync should detect that the 12th bill (due 1 month ago) is past due and add another 12
        $registration->syncBills();
        $this->assertEquals(24, $registration->bills()->count());
    }

    public function test_batch_sizes_for_different_duration_types()
    {
        $types = [
            'daily' => 7,
            'weekly' => 4,
            'monthly' => 12,
            'yearly' => 5
        ];

        foreach ($types as $type => $expectedBatch) {
            $user = User::factory()->create();
            $location = $this->createLocation();
            $room = $this->createRoom($location->id);

            $data = $this->getBaseData($user, $location, $room);
            $data['duration_type'] = $type;

            $registration = Registration::create($data);

            $registration->syncBills();
            $this->assertEquals($expectedBatch, $registration->bills()->count(), "Failed for type: $type");
        }
    }
}
