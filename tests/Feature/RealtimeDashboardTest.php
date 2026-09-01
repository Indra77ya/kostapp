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

        Room::create(['room_number' => '101', 'price_monthly' => 1000, 'status' => 'available']);
        Room::create(['room_number' => '102', 'price_monthly' => 1000, 'status' => 'occupied']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\DashboardStats::class)
            ->assertSet('totalRooms', 2)
            ->assertSet('availableRooms', 1)
            ->assertSee('Okupansi 50%')
            ->assertSee('1 / 2 Kamar Terisi (1 Kosong)');
    }

    public function test_notification_bell_component_can_be_cleared()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        \App\Models\AppNotification::createForUser($user->id, 'Test notification', 'info');

        Livewire::actingAs($user)
            ->test(\App\Livewire\NotificationBell::class)
            ->call('clearNotifications')
            ->assertSee('Tidak ada notifikasi');

        $this->assertEquals(0, \App\Models\AppNotification::where('user_id', $user->id)->count());
    }
}
