<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Room;
use App\Models\Rule;
use App\Models\PaymentMethod;
use App\Livewire\LocationManager;
use App\Livewire\RoomManager;
use App\Livewire\RuleManager;
use App\Livewire\PaymentMethodManager;
use App\Livewire\NotificationBell;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataNotificationBellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('owner');
        $this->actingAs($user);
    }

    public function test_location_manager_hides_notifications_from_bell()
    {
        Livewire::test(LocationManager::class)
            ->set('name', 'Kost Baru')
            ->call('saveLocation')
            ->assertDispatched('notify', hideInBell: true);

        $location = Location::first();

        Livewire::test(LocationManager::class)
            ->call('openModal', $location->id)
            ->set('name', 'Kost Updated')
            ->call('saveLocation')
            ->assertDispatched('notify', hideInBell: true);

        Livewire::test(LocationManager::class)
            ->call('deleteLocation', $location->id)
            ->assertDispatched('notify', hideInBell: true);
    }

    public function test_room_manager_hides_notifications_from_bell()
    {
        Livewire::test(RoomManager::class)
            ->set('room_number', '101')
            ->set('price_monthly', 1000000)
            ->set('status', 'available')
            ->call('saveRoom')
            ->assertDispatched('notify', hideInBell: true);

        $room = Room::first();

        Livewire::test(RoomManager::class)
            ->call('openModal', $room->id)
            ->set('price_monthly', 1100000)
            ->call('saveRoom')
            ->assertDispatched('notify', hideInBell: true);

        Livewire::test(RoomManager::class)
            ->call('deleteRoom', $room->id)
            ->assertDispatched('notify', hideInBell: true);
    }

    public function test_rule_manager_hides_notifications_from_bell()
    {
        Livewire::test(RuleManager::class)
            ->set('title', 'No Pets')
            ->set('category', 'Umum')
            ->call('saveRule')
            ->assertDispatched('notify', hideInBell: true);

        $rule = Rule::first();

        Livewire::test(RuleManager::class)
            ->call('openModal', $rule->id)
            ->set('title', 'No Pets Allowed')
            ->call('saveRule')
            ->assertDispatched('notify', hideInBell: true);

        Livewire::test(RuleManager::class)
            ->call('deleteRule', $rule->id)
            ->assertDispatched('notify', hideInBell: true);
    }

    public function test_payment_method_manager_hides_notifications_from_bell()
    {
        Livewire::test(PaymentMethodManager::class)
            ->set('name', 'Bank BCA')
            ->set('category', 'Bank')
            ->call('savePaymentMethod')
            ->assertDispatched('notify', hideInBell: true);

        $pm = \App\Models\PaymentMethod::where('name', 'Bank BCA')->first();

        Livewire::test(PaymentMethodManager::class)
            ->call('openModal', $pm->id)
            ->set('name', 'Bank BCA Updated')
            ->call('savePaymentMethod')
            ->assertDispatched('notify', hideInBell: true);
    }

    public function test_payment_method_manager_hides_delete_notification_from_bell()
    {
        $pm = \App\Models\PaymentMethod::create([
            'name' => 'Bank Mandiri',
            'category' => 'Bank'
        ]);

        Livewire::test(PaymentMethodManager::class)
            ->call('deletePaymentMethod', $pm->id)
            ->assertDispatched('notify', hideInBell: true);
    }
}
