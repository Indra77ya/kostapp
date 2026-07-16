<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Livewire\PaymentManager;
use App\Livewire\TenantPaymentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class SelectableBillsPaginationTest extends TestCase
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

    public function test_selectable_bills_shows_all_bills_across_pagination()
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');
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
            'duration_value' => 15,
            'is_open_ended' => false,
            'room_price' => 1000000,
            'total_price' => 15000000,
            'identity_type' => 'KTP',
            'identity_number' => '12345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'status' => 'active',
        ]);

        // Generate 15 bills
        for ($i = 1; $i <= 15; $i++) {
            Bill::create([
                'registration_id' => $registration->id,
                'bill_number' => 'BILL-PAG-' . $i,
                'description' => 'Sewa Kamar ' . $i,
                'amount' => 1000000,
                'due_date' => now()->addMonths($i),
                'status' => 'Belum Lunas',
            ]);
        }

        // Test Admin side
        Livewire::test(PaymentManager::class)
            ->call('selectRegistration', $registration->id)
            ->set('billsPerPage', 10)
            ->assertViewHas('bills', function ($bills) {
                // Should show the last page of bills (page 2 of 2, 5 bills)
                return $bills->count() === 5;
            })
            ->assertViewHas('selectableBills', function ($selectableBills) {
                // Should show all 15 bills regardless of pagination
                return $selectableBills->count() === 15;
            });

        // Test Tenant side
        $this->actingAs($user);
        Livewire::test(TenantPaymentManager::class)
            ->assertViewHas('bills', function ($bills) {
                // Page size is 12 (batch size for monthly). Page 2 of 2 will have 3 bills.
                return $bills->count() === 3;
            })
            ->assertViewHas('selectableBills', function ($selectableBills) {
                // Should show all 15 bills regardless of pagination
                return $selectableBills->count() === 15;
            });
    }
}
