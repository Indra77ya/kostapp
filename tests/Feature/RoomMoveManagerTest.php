<?php

namespace Tests\Feature;

use App\Livewire\RoomMoveManager;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use App\Models\RoomMove;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoomMoveManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (!Role::where('name', 'owner')->exists()) {
            Role::create(['name' => 'owner']);
        }
        if (!Role::where('name', 'tenant')->exists()) {
            Role::create(['name' => 'tenant']);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_can_process_room_move()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $location = Location::create(['name' => 'Test Kost', 'address' => 'Test Address']);
        $room1 = Room::create(['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $room2 = Room::create(['location_id' => $location->id, 'room_number' => '102', 'price_monthly' => 1000000, 'status' => 'available']);

        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room1->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_place' => 'Test',
            'birth_date' => '1990-01-01',
            'status' => 'active'
        ]);

        Livewire::actingAs($owner)
            ->test(RoomMoveManager::class)
            ->set('registration_id', $registration->id)
            ->set('new_room_id', $room2->id)
            ->set('move_date', now()->format('Y-m-d'))
            ->set('reason', 'Pindah ke lantai bawah')
            ->call('saveMove')
            ->assertHasNoErrors();

        // Verify registration updated
        $this->assertEquals($room2->id, $registration->refresh()->room_id);

        // Verify room statuses updated
        $this->assertEquals('available', $room1->refresh()->status);
        $this->assertEquals('occupied', $room2->refresh()->status);

        // Verify history recorded
        $this->assertDatabaseHas('room_moves', [
            'registration_id' => $registration->id,
            'old_room_id' => $room1->id,
            'new_room_id' => $room2->id,
            'reason' => 'Pindah ke lantai bawah'
        ]);
    }
}
