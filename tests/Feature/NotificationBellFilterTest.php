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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('owner');
        $this->actingAs($user);
    }

    public function test_notification_bell_filters_out_success_and_error_types()
    {
        NotificationSent::dispatch('Success Message', 'success');
        NotificationSent::dispatch('Error Message', 'error');
        NotificationSent::dispatch('Info Message', 'info');
        NotificationSent::dispatch('Warning Message', 'warning');

        $this->assertEquals(2, \App\Models\AppNotification::where('user_id', auth()->id())->where('is_read', false)->count());
    }

    public function test_notification_bell_listens_to_local_notify_event_and_filters()
    {
        Livewire::test(NotificationBell::class)
            // Success should be filtered out
            ->dispatch('notify', message: 'Success Message', type: 'success')
            ->assertSet('unreadCount', 0)
            // Info should be recorded
            ->dispatch('notify', message: 'Info Message', type: 'info')
            ->assertSee('Info Message');

        $this->assertEquals(1, \App\Models\AppNotification::where('user_id', auth()->id())->where('is_read', false)->count());
    }
}
