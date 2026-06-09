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

class TenantSelectableBillsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        if (!Role::where('name', 'tenant')->exists()) {
            Role::create(['name' => 'tenant']);
        }
    }

    public function test_selectable_bills_excludes_bills_with_pending_payments()
    {
        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create(['room_number' => '101', 'location_id' => $location->id, 'type' => 'Standard', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        // Use is_open_ended = false and duration_value = 2 to have fixed bills
        $reg = Registration::create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-123',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 2,
            'is_open_ended' => false,
            'room_price' => 1000000,
            'total_price' => 2000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        $reg->syncBills();
        $bills = $reg->bills()->orderBy('due_date', 'asc')->get();
        $this->assertCount(2, $bills);

        $bill1 = $bills[0];
        $bill2 = $bills[1];

        $pm = PaymentMethod::create(['name' => 'Cash', 'category' => 'Manual', 'is_active' => true]);

        // Add pending payment for bill 1
        Payment::create([
            'registration_id' => $reg->id,
            'bill_id' => $bill1->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-001',
            'payment_date' => now(),
            'amount' => 1000000,
            'status' => 'Menunggu Konfirmasi'
        ]);

        // Test selectableBills
        Livewire::actingAs($tenant)
            ->test(TenantPaymentManager::class)
            ->assertViewHas('selectableBills', function($selectable) use ($bill2) {
                // Should only contain bill 2, not bill 1
                return $selectable->count() === 1 && $selectable->first()->id === $bill2->id;
            });
    }
}
