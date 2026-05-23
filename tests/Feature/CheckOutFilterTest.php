<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\CheckOutManager;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckOutFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->location = Location::create(['name' => 'Kost Ceria', 'address' => 'Jl. Ceria']);
        $this->paymentMethod = PaymentMethod::create([
            'name' => 'Transfer Bank',
            'category' => 'Bank',
            'is_active' => true
        ]);
    }

    private function createRegistration($name, $durationType, $hasDebt = false)
    {
        $room = Room::create([
            'room_number' => 'Room-' . uniqid(),
            'location_id' => $this->location->id,
            'type' => 'Standard',
            'price_monthly' => 1000000,
            'status' => 'occupied'
        ]);

        $tenant = User::factory()->create(['name' => $name]);
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'location_id' => $this->location->id,
            'registration_number' => 'REG-' . uniqid(),
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => $durationType,
            'duration_value' => 1,
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

        $bill = Bill::create([
            'registration_id' => $registration->id,
            'bill_number' => 'BILL-' . uniqid(),
            'description' => 'Sewa',
            'amount' => 1000000,
            'paid_amount' => $hasDebt ? 0 : 1000000,
            'due_date' => now(),
            'status' => $hasDebt ? 'Belum Lunas' : 'Lunas',
        ]);

        if (!$hasDebt) {
            Payment::create([
                'registration_id' => $registration->id,
                'bill_id' => $bill->id,
                'payment_method_id' => $this->paymentMethod->id,
                'payment_number' => 'PAY-' . uniqid(),
                'payment_date' => now(),
                'amount' => 1000000,
                'status' => 'Lunas'
            ]);
        }

        return $registration;
    }

    public function test_can_filter_by_duration_type()
    {
        $this->createRegistration('Daily User', 'daily');
        $this->createRegistration('Monthly User', 'monthly');

        Livewire::actingAs($this->admin)
            ->test(CheckOutManager::class)
            ->set('filterDurationType', 'daily')
            ->assertSee('Daily User')
            ->assertDontSee('Monthly User')
            ->set('filterDurationType', 'monthly')
            ->assertSee('Monthly User')
            ->assertDontSee('Daily User');
    }

    public function test_can_filter_by_payment_status()
    {
        $this->createRegistration('Paid User', 'monthly', false);
        $this->createRegistration('Debt User', 'monthly', true);

        Livewire::actingAs($this->admin)
            ->test(CheckOutManager::class)
            ->set('filterPaymentStatus', 'lunas')
            ->assertSee('Paid User')
            ->assertDontSee('Debt User')
            ->set('filterPaymentStatus', 'tunggakan')
            ->assertSee('Debt User')
            ->assertDontSee('Paid User');
    }

    public function test_can_sort_by_name()
    {
        $this->createRegistration('Alpha', 'monthly');
        $this->createRegistration('Zeta', 'monthly');

        Livewire::actingAs($this->admin)
            ->test(CheckOutManager::class)
            ->set('sort', 'name_asc')
            ->assertSeeInOrder(['Alpha', 'Zeta'])
            ->set('sort', 'name_desc')
            ->assertSeeInOrder(['Zeta', 'Alpha']);
    }

    public function test_reset_filters_works()
    {
        Livewire::actingAs($this->admin)
            ->test(CheckOutManager::class)
            ->set('search', 'Someone')
            ->set('filterDurationType', 'daily')
            ->set('filterPaymentStatus', 'lunas')
            ->set('sort', 'name_asc')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterDurationType', '')
            ->assertSet('filterPaymentStatus', '')
            ->assertSet('sort', 'latest');
    }
}
