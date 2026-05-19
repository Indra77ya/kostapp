<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\PaymentManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_can_create_payment()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create([
            'room_number' => '101',
            'location_id' => $location->id,
            'type' => 'Standard',
            'price_monthly' => 1000000,
            'status' => 'occupied'
        ]);

        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-123',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'room_price' => 1000000,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => 'Cash',
            'category' => 'Manual',
            'is_active' => true
        ]);

        Livewire::actingAs($owner)
            ->test(PaymentManager::class)
            ->set('registration_id', $registration->id)
            ->set('payment_method_id', $paymentMethod->id)
            ->set('amount', 1000000)
            ->set('payment_date', now()->format('Y-m-d'))
            ->call('savePayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'registration_id' => $registration->id,
            'amount' => 1000000,
            'status' => 'Lunas'
        ]);
    }

    public function test_automatic_payment_number_generation()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(PaymentManager::class)
            ->assertSet('payment_number', 'PAY-' . now()->format('dmY') . '-0001');
    }

    public function test_installment_logic()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create([
            'room_number' => '101',
            'location_id' => $location->id,
            'type' => 'Standard',
            'price_monthly' => 1000000,
            'status' => 'occupied'
        ]);

        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-123',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'room_price' => 1000000,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        $paymentMethod = PaymentMethod::create(['name' => 'Cash', 'category' => 'Manual', 'is_active' => true]);

        // First payment: Partial
        Livewire::actingAs($owner)
            ->test(PaymentManager::class)
            ->set('registration_id', $registration->id)
            ->set('payment_method_id', $paymentMethod->id)
            ->set('amount', 400000)
            ->assertSet('status', 'Belum Lunas (Sisa: Rp 600.000)')
            ->call('savePayment');

        $this->assertDatabaseHas('payments', ['amount' => 400000, 'status' => 'Belum Lunas (Sisa: Rp 600.000)']);

        // Second payment: Should auto-fill 600.000 and be Lunas
        Livewire::actingAs($owner)
            ->test(PaymentManager::class)
            ->set('registration_id', $registration->id)
            ->assertSet('amount', 600000)
            ->assertSet('status', 'Lunas')
            ->set('payment_method_id', $paymentMethod->id)
            ->call('savePayment');

        $this->assertDatabaseHas('payments', ['amount' => 600000, 'status' => 'Lunas']);
    }
}
