<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AppNotification;
use App\Livewire\NotificationBell;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class NotificationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'tenant']);
    }

    public function test_notifications_persist_and_are_displayed_in_bell()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $this->actingAs($user);

        AppNotification::createForUser(
            userId: $user->id,
            message: 'Tagihan baru bulan September.',
            type: 'info',
            url: route('payments.index')
        );

        // Render Livewire component
        Livewire::test(NotificationBell::class)
            ->assertSee('Tagihan baru bulan September.')
            ->assertSee('Lihat detail');

        // Simulate refresh/re-mount
        $this->assertEquals(1, AppNotification::where('user_id', $user->id)->count());

        Livewire::test(NotificationBell::class)
            ->assertSee('Tagihan baru bulan September.');
    }

    public function test_marking_notification_as_read_updates_database_and_redirects()
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');
        $this->actingAs($user);

        $notification = AppNotification::create([
            'user_id' => $user->id,
            'message' => 'Pembayaran anda disetujui.',
            'type' => 'success',
            'url' => route('tenant.payments'),
            'is_read' => false,
        ]);

        Livewire::test(NotificationBell::class)
            ->call('markAsRead', $notification->id)
            ->assertRedirect(route('tenant.payments'));

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_notifications_are_capped_at_100_per_user()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        for ($i = 1; $i <= 105; $i++) {
            AppNotification::createForUser(
                userId: $user->id,
                message: "Notifikasi ke-{$i}",
                type: 'info'
            );
        }

        $this->assertEquals(100, AppNotification::where('user_id', $user->id)->count());
        $this->assertFalse(AppNotification::where('user_id', $user->id)->where('message', 'Notifikasi ke-1')->exists());
        $this->assertTrue(AppNotification::where('user_id', $user->id)->where('message', 'Notifikasi ke-105')->exists());
    }

    public function test_clear_notifications_deletes_database_records()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $this->actingAs($user);

        AppNotification::createForUser(userId: $user->id, message: 'Notification 1');
        AppNotification::createForUser(userId: $user->id, message: 'Notification 2');

        $this->assertEquals(2, AppNotification::where('user_id', $user->id)->count());

        Livewire::test(NotificationBell::class)
            ->call('clearNotifications')
            ->assertSee('Tidak ada notifikasi');

        $this->assertEquals(0, AppNotification::where('user_id', $user->id)->count());
    }
}
