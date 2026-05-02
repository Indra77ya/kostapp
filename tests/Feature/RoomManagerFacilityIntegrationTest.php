<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoomManagerFacilityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_can_save_room_with_facilities_as_array()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Facility::create(['name' => 'AC', 'category' => 'Kamar']);
        Facility::create(['name' => 'WiFi', 'category' => 'Kamar']);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\RoomManager::class)
            ->set('room_number', '101')
            ->set('price', 1000000)
            ->set('status', 'available')
            ->set('facilities', ['AC', 'WiFi'])
            ->call('saveRoom');

        $this->assertDatabaseHas('rooms', [
            'room_number' => '101',
            'facilities' => 'AC, WiFi'
        ]);
    }

    public function test_can_load_room_facilities_into_array()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $room = Room::create([
            'room_number' => '102',
            'price' => 1000000,
            'status' => 'available',
            'facilities' => 'AC, WiFi'
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\RoomManager::class)
            ->call('openModal', $room->id)
            ->assertSet('facilities', ['AC', 'WiFi']);
    }
}
