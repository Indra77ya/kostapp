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

class HistorySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_search_filters_bills_and_payments()
    {
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

        $bill1 = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-SEARCH-1',
            'description' => 'Target Bill',
            'amount' => 100000,
            'due_date' => now(),
            'status' => 'Belum Lunas',
        ]);

        $bill2 = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-OTHER-2',
            'description' => 'Other Bill',
            'amount' => 100000,
            'due_date' => now(),
            'status' => 'Belum Lunas',
        ]);

        $pm = PaymentMethod::create(['name' => 'Cash', 'category' => 'Tunai', 'is_active' => true]);

        $payment1 = Payment::create([
            'registration_id' => $registration->id,
            'bill_id' => $bill1->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-SEARCH-1',
            'payment_date' => now(),
            'amount' => 50000,
            'status' => 'Lunas',
        ]);

        $payment2 = Payment::create([
            'registration_id' => $registration->id,
            'bill_id' => $bill2->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-OTHER-2',
            'payment_date' => now(),
            'amount' => 50000,
            'status' => 'Lunas',
        ]);

        // Test Admin side
        Livewire::test(PaymentManager::class)
            ->set('selectedRegistrationId', $registration->id)
            ->set('viewMode', 'history')
            ->set('historySearch', 'Target')
            ->assertSee('BILL-SEARCH-1')
            ->assertDontSee('BILL-OTHER-2')
            ->assertSee('PAY-SEARCH-1')
            ->assertDontSee('PAY-OTHER-2');

        // Test Tenant side
        $this->actingAs($user);
        Livewire::test(TenantPaymentManager::class)
            ->set('historySearch', 'OTHER')
            ->assertDontSee('BILL-SEARCH-1')
            ->assertSee('BILL-OTHER-2')
            ->assertDontSee('PAY-SEARCH-1')
            ->assertSee('PAY-OTHER-2');
    }
}
