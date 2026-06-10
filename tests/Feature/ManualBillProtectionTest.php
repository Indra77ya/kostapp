<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualBillProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_bill_is_not_overwritten_by_sync_bills()
    {
        // Setup
        $user = User::factory()->create();
        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'price_monthly' => 1000000,
            'status' => 'available'
        ]);

        $registration = Registration::create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'is_open_ended' => false,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active',
        ]);

        // Create a manual bill
        $manualBill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-M-12345',
            'description' => 'Tagihan Air',
            'amount' => 50000,
            'due_date' => now(),
            'status' => 'Belum Lunas',
        ]);

        // Run syncBills
        $registration->syncBills();

        // Verify manual bill is unchanged
        $manualBill->refresh();
        $this->assertEquals('Tagihan Air', $manualBill->description);
        $this->assertEquals(50000, $manualBill->amount);
        $this->assertEquals('BILL-M-12345', $manualBill->bill_number);

        // Verify that syncBills still created the room rent bill
        $roomRentBill = Bill::where('registration_id', $registration->id)
            ->where('bill_number', 'not like', 'BILL-M-%')
            ->where('bill_number', 'not like', 'BILL-DEP-%')
            ->first();

        $this->assertNotNull($roomRentBill);
        $this->assertStringContainsString('Tagihan Sewa Kamar', $roomRentBill->description);
    }
}
