<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\TenantPaymentManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantBillPendingStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_bill_shows_pending_confirmation_status()
    {
        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create(['room_number' => '101', 'location_id' => $location->id, 'type' => 'Standard', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');
        $reg = Registration::create(['user_id' => $tenant->id, 'room_id' => $room->id, 'location_id' => $location->id, 'registration_number' => 'REG-123', 'registration_date' => now(), 'stay_start_date' => now(), 'room_price' => 1000000, 'total_price' => 1000000, 'identity_type' => 'KTP', 'identity_number' => '12345', 'gender' => 'Laki-laki', 'birth_date' => '1990-01-01', 'status' => 'active']);

        $bill = Bill::create([
            'registration_id' => $reg->id,
            'bill_number' => 'BILL-001',
            'description' => 'Target Bill',
            'amount' => 1000000,
            'due_date' => now(),
            'status' => 'Belum Lunas'
        ]);

        $pm = PaymentMethod::create(['name' => 'Cash', 'category' => 'Manual', 'is_active' => true]);

        // Initially shows Belum Lunas
        Livewire::actingAs($tenant)
            ->test(TenantPaymentManager::class)
            ->assertSee('Target Bill')
            ->assertSee('Belum Lunas');

        // Add pending payment
        Payment::create([
            'registration_id' => $reg->id,
            'bill_id' => $bill->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-001',
            'payment_date' => now(),
            'amount' => 1000000,
            'status' => 'Menunggu Konfirmasi'
        ]);

        // Now should show Menunggu Konfirmasi in the bill row
        Livewire::actingAs($tenant)
            ->test(TenantPaymentManager::class)
            ->assertSee('Target Bill')
            ->assertSee('Menunggu Konfirmasi');
    }
}
