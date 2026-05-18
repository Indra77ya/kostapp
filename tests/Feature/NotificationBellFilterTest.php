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

    public function test_notification_bell_filters_out_success_and_error()
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
            ->assertCount('notifications', 1);
    }
}
