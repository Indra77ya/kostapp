<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RealtimeDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_dashboard_stats_component_loads_correctly()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Room::create(['room_number' => '101', 'price' => 1000, 'status' => 'available']);
        Room::create(['room_number' => '102', 'price' => 1000, 'status' => 'occupied']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSet('totalRooms', 2)
            ->assertSet('availableRooms', 1)
            ->assertSee('2 Total Kamar')
            ->assertSee('1 Tersedia');
    }

    public function test_notification_bell_component_can_be_cleared()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\NotificationBell::class)
            ->set('notifications', [['message' => 'Test', 'time' => 'now', 'type' => 'info']])
            ->set('unreadCount', 1)
            ->call('clearNotifications')
            ->assertSet('notifications', [])
            ->assertSet('unreadCount', 0);
    }
}
