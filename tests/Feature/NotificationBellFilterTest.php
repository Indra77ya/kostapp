<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\NotificationBell;
use App\Events\NotificationSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_bell_records_all_notification_types()
    {
        Livewire::test(NotificationBell::class)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Success Message',
                'type' => 'success'
            ])
            ->assertSet('unreadCount', 1)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Error Message',
                'type' => 'error'
            ])
            ->assertSet('unreadCount', 2)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Info Message',
                'type' => 'info'
            ])
            ->assertSet('unreadCount', 3)
            ->assertCount('notifications', 3);
    }

    public function test_notification_bell_listens_to_local_notify_event()
    {
        Livewire::test(NotificationBell::class)
            ->dispatch('notify', [
                'message' => 'Local Message',
                'type' => 'success'
            ])
            ->assertSet('unreadCount', 1)
            ->assertCount('notifications', 1)
            ->assertSee('Local Message');
    }
}
