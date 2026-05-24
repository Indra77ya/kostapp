<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Livewire\PaymentConfirmationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentConfirmationFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createAdmin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        return $admin;
    }

    private function createLocation($name)
    {
        return Location::create(['name' => $name, 'address' => 'Test Address']);
    }

    private function createPaymentMethod($name)
    {
        return PaymentMethod::create(['name' => $name, 'category' => 'Bank', 'is_active' => true]);
    }

    private function createRegistration($user, $room, $location)
    {
        return Registration::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'location_id' => $location->id,
            'registration_number' => 'REG-' . uniqid(),
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
    }

    private function createPayment($registration, $method, $status = 'Menunggu Konfirmasi')
    {
        return Payment::create([
            'registration_id' => $registration->id,
            'payment_method_id' => $method->id,
            'payment_number' => 'PAY-' . uniqid(),
            'payment_date' => now(),
            'amount' => 500000,
            'status' => $status
        ]);
    }

    /** @test */
    public function can_filter_by_location()
    {
        $admin = $this->createAdmin();

        $loc1 = $this->createLocation('Lokasi A');
        $loc2 = $this->createLocation('Lokasi B');

        $room1 = Room::create(['room_number' => '101', 'location_id' => $loc1->id, 'type' => 'Standard', 'price_monthly' => 1000000]);
        $room2 = Room::create(['room_number' => '102', 'location_id' => $loc2->id, 'type' => 'Standard', 'price_monthly' => 1000000]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $reg1 = $this->createRegistration($user1, $room1, $loc1);
        $reg2 = $this->createRegistration($user2, $room2, $loc2);

        $pm = $this->createPaymentMethod('BCA');

        $this->createPayment($reg1, $pm);
        $this->createPayment($reg2, $pm);

        Livewire::actingAs($admin)
            ->test(PaymentConfirmationManager::class)
            ->set('filterLocation', $loc1->id)
            ->assertViewHas('payments', function ($payments) use ($reg1) {
                return $payments->count() === 1 && $payments->first()->registration_id === $reg1->id;
            });
    }

    /** @test */
    public function can_filter_by_payment_method()
    {
        $admin = $this->createAdmin();
        $loc = $this->createLocation('Lokasi');
        $room = Room::create(['room_number' => '101', 'location_id' => $loc->id, 'type' => 'Standard', 'price_monthly' => 1000000]);
        $user = User::factory()->create();
        $reg = $this->createRegistration($user, $room, $loc);

        $pm1 = $this->createPaymentMethod('BCA');
        $pm2 = $this->createPaymentMethod('OVO');

        $this->createPayment($reg, $pm1);
        $this->createPayment($reg, $pm2);

        Livewire::actingAs($admin)
            ->test(PaymentConfirmationManager::class)
            ->set('filterPaymentMethod', $pm1->id)
            ->assertViewHas('payments', function ($payments) use ($pm1) {
                return $payments->count() === 1 && $payments->first()->payment_method_id === $pm1->id;
            });
    }

    /** @test */
    public function can_sort_by_name()
    {
        $admin = $this->createAdmin();
        $loc = $this->createLocation('Lokasi');
        $room1 = Room::create(['room_number' => '101', 'location_id' => $loc->id, 'type' => 'Standard', 'price_monthly' => 1000000]);
        $room2 = Room::create(['room_number' => '102', 'location_id' => $loc->id, 'type' => 'Standard', 'price_monthly' => 1000000]);

        $userA = User::factory()->create(['name' => 'Abby']);
        $userZ = User::factory()->create(['name' => 'Zebra']);

        $regA = $this->createRegistration($userA, $room1, $loc);
        $regZ = $this->createRegistration($userZ, $room2, $loc);

        $pm = $this->createPaymentMethod('BCA');

        $this->createPayment($regA, $pm);
        $this->createPayment($regZ, $pm);

        Livewire::actingAs($admin)
            ->test(PaymentConfirmationManager::class)
            ->set('sort', 'name_asc')
            ->assertViewHas('payments', function ($payments) {
                return $payments->first()->registration->user->name === 'Abby';
            })
            ->set('sort', 'name_desc')
            ->assertViewHas('payments', function ($payments) {
                return $payments->first()->registration->user->name === 'Zebra';
            });
    }
}
