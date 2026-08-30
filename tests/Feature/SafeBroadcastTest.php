<?php

namespace Tests\Feature;

use App\Helpers\BroadcastHelper;
use App\Events\NotificationSent;
use App\Models\User;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pusher\PusherException;
use Tests\TestCase;

class SafeBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_broadcast_catches_exception_without_failing()
    {
        // Execute safeBroadcast when socket ID is not set / invalid
        // It should catch PusherException safely and log a warning without throwing an exception.
        $event = new NotificationSent('Test message', 'info');

        // This should run without throwing any exception
        BroadcastHelper::safeBroadcast($event, true);

        $this->assertTrue(true);
    }

    public function test_registration_manager_saves_successfully_even_if_broadcast_fails()
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create([
            'name' => 'Lokasi Test',
            'address' => 'Jl. Test No. 123',
        ]);

        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'status' => 'available',
            'price_monthly' => 1000000,
        ]);

        // Simulating Livewire registration save call
        \Livewire::actingAs($owner)
            ->test(\App\Livewire\RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('registration_date', now()->format('Y-m-d'))
            ->set('stay_start_date', now()->format('Y-m-d'))
            ->set('name', 'Budi Penolak Error')
            ->set('email', 'budi.safe@test.com')
            ->set('identity_number', '1234567890123456')
            ->set('gender', 'Laki-laki')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', \Illuminate\Http\UploadedFile::fake()->image('self.jpg'))
            ->set('photo_identity', \Illuminate\Http\UploadedFile::fake()->image('ktp.jpg'))
            ->call('saveRegistration')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'budi.safe@test.com',
        ]);
    }
}
