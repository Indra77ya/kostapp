<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Bill;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\PaymentManager;
use App\Livewire\TenantPaymentManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OverpaymentWarningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_admin_overpayment_calculates_excess_amount()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create(['room_number' => '101', 'location_id' => $location->id, 'type' => 'Standard', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');
        $reg = Registration::create(['user_id' => $tenant->id, 'room_id' => $room->id, 'location_id' => $location->id, 'registration_number' => 'REG-123', 'registration_date' => now(), 'stay_start_date' => now(), 'room_price' => 1000000, 'total_price' => 1000000, 'identity_type' => 'KTP', 'identity_number' => '12345', 'gender' => 'Laki-laki', 'birth_date' => '1990-01-01', 'status' => 'active']);

        $bill = Bill::create([
            'registration_id' => $reg->id,
            'bill_number' => 'BILL-001',
            'description' => 'Test Bill',
            'amount' => 1000000,
            'due_date' => now(),
            'status' => 'Belum Lunas'
        ]);

        Livewire::actingAs($owner)
            ->test(PaymentManager::class)
            ->call('selectRegistration', $reg->id)
            ->call('openModal')
            ->set('bill_id', $bill->id)
            ->set('amount', 1500000)
            ->assertSet('excess_amount', 500000)
            ->assertSeeHtml('Kelebihan pembayaran sebesar <strong>Rp 500.000</strong>');
    }

    public function test_tenant_overpayment_calculates_excess_amount()
    {
        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create(['room_number' => '101', 'location_id' => $location->id, 'type' => 'Standard', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');
        $reg = Registration::create(['user_id' => $tenant->id, 'room_id' => $room->id, 'location_id' => $location->id, 'registration_number' => 'REG-123', 'registration_date' => now(), 'stay_start_date' => now(), 'room_price' => 1000000, 'total_price' => 1000000, 'identity_type' => 'KTP', 'identity_number' => '12345', 'gender' => 'Laki-laki', 'birth_date' => '1990-01-01', 'status' => 'active']);

        $bill = Bill::create([
            'registration_id' => $reg->id,
            'bill_number' => 'BILL-001',
            'description' => 'Test Bill',
            'amount' => 1000000,
            'due_date' => now(),
            'status' => 'Belum Lunas'
        ]);

        Livewire::actingAs($tenant)
            ->test(TenantPaymentManager::class)
            ->call('openModal', $bill->id)
            ->set('amount', 1200000)
            ->assertSet('excess_amount', 200000)
            ->assertSeeHtml('Kelebihan pembayaran sebesar <strong>Rp 200.000</strong>');
    }

    public function test_confirmation_modal_shows_overpayment_warning()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Test Location', 'address' => 'Test Address']);
        $room = Room::create(['room_number' => '101', 'location_id' => $location->id, 'type' => 'Standard', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');
        $reg = Registration::create(['user_id' => $tenant->id, 'room_id' => $room->id, 'location_id' => $location->id, 'registration_number' => 'REG-123', 'registration_date' => now(), 'stay_start_date' => now(), 'room_price' => 1000000, 'total_price' => 1000000, 'identity_type' => 'KTP', 'identity_number' => '12345', 'gender' => 'Laki-laki', 'birth_date' => '1990-01-01', 'status' => 'active']);

        $bill = Bill::create([
            'registration_id' => $reg->id,
            'bill_number' => 'BILL-001',
            'description' => 'Test Bill',
            'amount' => 1000000,
            'due_date' => now(),
            'status' => 'Belum Lunas'
        ]);

        $pm = PaymentMethod::create(['name' => 'Bank Transfer', 'category' => 'Bank', 'is_active' => true]);

        $payment = Payment::create([
            'registration_id' => $reg->id,
            'bill_id' => $bill->id,
            'payment_method_id' => $pm->id,
            'payment_number' => 'PAY-001',
            'payment_date' => now(),
            'amount' => 1300000,
            'status' => 'Menunggu Konfirmasi'
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\PaymentConfirmationManager::class)
            ->call('showDetail', $payment->id)
            ->assertSeeHtml('Menyetujui pembayaran ini akan menghasilkan kelebihan bayar sebesar <strong>Rp 300.000</strong>');
    }
}
