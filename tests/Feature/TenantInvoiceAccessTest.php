<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Room;
use App\Models\Registration;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantInvoiceAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createRegistration($user, $location, $room, $number)
    {
        return Registration::create([
            'registration_number' => $number,
            'registration_date' => now(),
            'stay_start_date' => now(),
            'user_id' => $user->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'room_price' => 1000000,
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345' . $user->id,
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);
    }

    public function test_tenant_can_access_own_invoice()
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'price_monthly' => 1000000,
            'status' => 'available'
        ]);

        $registration = $this->createRegistration($tenant, $location, $room, 'REG-001');

        $paymentMethod = PaymentMethod::create(['name' => 'Cash', 'category' => 'Tunai', 'is_active' => true]);

        $payment = Payment::create([
            'registration_id' => $registration->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_number' => 'PAY-001',
            'payment_date' => now(),
            'amount' => 1000000,
            'status' => 'Lunas'
        ]);

        $response = $this->actingAs($tenant)->get(route('payments.invoice', $payment->id));
        $response->assertStatus(200);
    }

    public function test_tenant_cannot_access_others_invoice()
    {
        $tenant1 = User::factory()->create();
        $tenant1->assignRole('tenant');

        $tenant2 = User::factory()->create();
        $tenant2->assignRole('tenant');

        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '102',
            'price_monthly' => 1000000,
            'status' => 'available'
        ]);

        $registration2 = $this->createRegistration($tenant2, $location, $room, 'REG-002');

        $paymentMethod = PaymentMethod::create(['name' => 'Cash', 'category' => 'Tunai', 'is_active' => true]);

        $payment2 = Payment::create([
            'registration_id' => $registration2->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_number' => 'PAY-002',
            'payment_date' => now(),
            'amount' => 1000000,
            'status' => 'Lunas'
        ]);

        $response = $this->actingAs($tenant1)->get(route('payments.invoice', $payment2->id));
        $response->assertStatus(403);
    }
}
