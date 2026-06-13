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

class PaymentNumberFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_number_format_with_bill()
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

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-M-12345',
            'description' => 'Test Bill',
            'amount' => 100000,
            'due_date' => now(),
            'status' => 'Belum Lunas',
        ]);

        // Test Admin side
        Livewire::test(PaymentManager::class)
            ->set('bill_id', $bill->id)
            ->call('generatePaymentNumber')
            ->assertSet('payment_number', 'PAY-M-12345-01');

        // Create a payment
        $pm = PaymentMethod::create(['name' => 'Cash', 'category' => 'Tunai', 'is_active' => true]);
        Payment::create([
            'registration_id' => $registration->id,
            'bill_id' => $bill->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-M-12345-01',
            'payment_date' => now(),
            'amount' => 50000,
            'status' => 'Lunas',
        ]);

        // Test second installment Admin side
        Livewire::test(PaymentManager::class)
            ->set('bill_id', $bill->id)
            ->call('generatePaymentNumber')
            ->assertSet('payment_number', 'PAY-M-12345-02');

        // Test Tenant side
        $this->actingAs($user);
        Livewire::test(TenantPaymentManager::class)
            ->set('bill_id', $bill->id)
            ->assertSet('payment_number', 'PAY-M-12345-02');
    }

    public function test_payment_number_format_without_bill()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $date = now()->format('dmY');
        $expectedPrefix = "PAY-{$date}-";

        Livewire::test(TenantPaymentManager::class)
            ->set('bill_id', 'umum')
            ->assertSet('payment_number', fn($val) => str_contains($val, $expectedPrefix));
    }
}
