<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\NotificationBell;
use App\Livewire\FacilityManager;
use App\Models\Facility;
use App\Events\NotificationSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;

class FacilityNotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_facility_manager_dispatches_notify_with_hide_in_bell()
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        Livewire::test(FacilityManager::class)
            ->set('name', 'WiFi')
            ->set('category', 'Kamar')
            ->call('saveFacility')
            ->assertDispatched('notify', message: 'Fasilitas baru WiFi telah ditambahkan.', type: 'success', hideInBell: true);

        $facility = Facility::first();

        Livewire::test(FacilityManager::class)
            ->call('openModal', $facility->id)
            ->set('name', 'WiFi High Speed')
            ->call('saveFacility')
            ->assertDispatched('notify', message: 'Fasilitas WiFi High Speed telah diperbarui.', type: 'info', hideInBell: true);

        Livewire::test(FacilityManager::class)
            ->call('deleteFacility', $facility->id)
            ->assertDispatched('notify', message: 'Fasilitas WiFi High Speed telah dihapus.', type: 'warning', hideInBell: true);
    }

    public function test_notification_bell_ignores_notifications_with_hide_in_bell_flag()
    {
        Livewire::test(NotificationBell::class)
            // Test with direct dispatch data structure (Livewire style)
            ->dispatch('notify', message: 'Test Message', type: 'info', hideInBell: true)
            ->assertSet('unreadCount', 0)

            // Test with Echo style payload (Event style)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Echo Message',
                'type' => 'info',
                'hideInBell' => true
            ])
            ->assertSet('unreadCount', 0)

            // Verify normal notification still works
            ->dispatch('notify', message: 'Normal Message', type: 'info', hideInBell: false)
            ->assertSet('unreadCount', 1);
    }
}
