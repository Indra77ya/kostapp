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

    public function test_notification_bell_filters_out_success_and_error_types()
    {
        Livewire::test(NotificationBell::class)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Success Message',
                'type' => 'success'
            ])
            ->assertSet('unreadCount', 0)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Error Message',
                'type' => 'error'
            ])
            ->assertSet('unreadCount', 0)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Info Message',
                'type' => 'info'
            ])
            ->assertSet('unreadCount', 1)
            ->dispatch('echo:notifications,NotificationSent', [
                'message' => 'Warning Message',
                'type' => 'warning'
            ])
            ->assertSet('unreadCount', 2)
            ->assertCount('notifications', 2);
    }

    public function test_notification_bell_listens_to_local_notify_event_and_filters()
    {
        Livewire::test(NotificationBell::class)
            // Success should be filtered out
            ->dispatch('notify', message: 'Success Message', type: 'success')
            ->assertSet('unreadCount', 0)
            // Info should be recorded
            ->dispatch('notify', message: 'Info Message', type: 'info')
            ->assertSet('unreadCount', 1)
            ->assertCount('notifications', 1)
            ->assertSee('Info Message');
    }
}
