<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationRoomStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    /** @test */
    public function room_status_changes_to_occupied_on_registration()
    {
        Storage::fake('public');
        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'price' => 1000000,
            'status' => 'available'
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('identity_number', '123456789')
            ->set('birth_date', '1990-01-01')
            ->set('stay_start_date', now()->addDay()->format('Y-m-d'))
            ->set('photo_self', UploadedFile::fake()->image('self.jpg'))
            ->set('photo_identity', UploadedFile::fake()->image('identity.jpg'))
            ->call('saveRegistration');

        $this->assertEquals('occupied', $room->fresh()->status);
    }

    /** @test */
    public function room_status_reverts_to_available_on_deletion()
    {
        Storage::fake('public');
        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'price' => 1000000,
            'status' => 'occupied'
        ]);

        $regUser = User::factory()->create();
        $registration = Registration::create([
            'user_id' => $regUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now()->addDay(),
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\RegistrationManager::class)
            ->call('deleteRegistration', $registration->id);

        $this->assertEquals('available', $room->fresh()->status);
    }

    /** @test */
    public function occupied_rooms_are_hidden_in_selection()
    {
        $location = Location::create(['name' => 'Test Location']);
        $roomAvailable = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'price' => 1000000,
            'status' => 'available'
        ]);
        $roomOccupied = Room::create([
            'location_id' => $location->id,
            'room_number' => '102',
            'price' => 1000000,
            'status' => 'occupied'
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\RegistrationManager::class)
            ->set('location_id', $location->id)
            ->assertViewHas('rooms', function($rooms) use ($roomAvailable, $roomOccupied) {
                return $rooms->contains($roomAvailable) && !$rooms->contains($roomOccupied);
            });
    }
}
