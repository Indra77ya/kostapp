<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Room;
use App\Livewire\LocationManager;
use App\Events\NotificationSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class NotificationVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner']);
    }

    public function test_deleting_location_with_rooms_triggers_error_notification()
    {
        Event::fake();

        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Location with Rooms']);
        Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'price_monthly' => 1000000,
            'status' => 'available'
        ]);

        Livewire::actingAs($owner)
            ->test(LocationManager::class)
            ->call('deleteLocation', $location->id);

        Event::assertDispatched(NotificationSent::class, function ($event) {
            return $event->type === 'error' && str_contains($event->message, 'Gagal menghapus');
        });

        // Ensure location still exists
        $this->assertTrue(Location::where('id', $location->id)->exists());
    }

    public function test_updating_location_triggers_info_notification()
    {
        Event::fake();

        $owner = User::factory()->create();
        $owner->assignRole('owner');
        $location = Location::create(['name' => 'Old Name']);

        Livewire::actingAs($owner)
            ->test(LocationManager::class)
            ->call('openModal', $location->id)
            ->set('name', 'New Name')
            ->call('saveLocation');

        Event::assertDispatched(NotificationSent::class, function ($event) {
            return $event->type === 'info' && str_contains($event->message, 'telah diperbarui');
        });
    }
}
