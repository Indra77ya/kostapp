<?php

namespace Tests\Feature;

use App\Livewire\RegistrationManager;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationDeletionRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_cannot_delete_registration_with_payments()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price_monthly' => 1000000,
            'status' => 'occupied'
        ]);

        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
        ]);

        $pm = PaymentMethod::create(['name' => 'Cash', 'category' => 'Tunai', 'is_active' => true]);

        // Create a payment
        Payment::create([
            'registration_id' => $registration->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-001',
            'payment_date' => now(),
            'amount' => 1000000,
            'status' => 'Lunas'
        ]);

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->call('deleteRegistration', $registration->id)
            ->assertDispatched('notify', message: "Data check in {$tenant->name} tidak bisa dihapus karena sudah ada riwayat pembayaran.", type: 'warning');

        $this->assertDatabaseHas('registrations', ['id' => $registration->id]);
    }

    public function test_can_delete_registration_without_payments()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost B', 'address' => 'Jl. B']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '202',
            'type' => 'Deluxe',
            'price_monthly' => 2000000,
            'status' => 'occupied'
        ]);

        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-002',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 2000000,
            'total_price' => 2000000,
            'identity_type' => 'KTP',
            'identity_number' => '456',
            'gender' => 'Perempuan',
            'birth_place' => 'Bandung',
            'birth_date' => '1995-01-01',
        ]);

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->call('deleteRegistration', $registration->id)
            ->assertDispatched('notify', message: "Check in dan data penghuni {$tenant->name} berhasil dihapus.", type: 'success');

        $this->assertDatabaseMissing('registrations', ['id' => $registration->id]);
    }
}
