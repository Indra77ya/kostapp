<?php

namespace Tests\Feature;

use App\Livewire\RegistrationManager;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
        Storage::fake('public');
    }

    public function test_can_create_new_registration()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price' => 1000000,
            'facilities' => 'Bed, AC'
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('stay_start_date', '2026-06-01')
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('identity_number', '123456789')
            ->set('birth_place', 'Jakarta')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->set('emergency_contacts.0.name', 'Jane Doe')
            ->set('emergency_contacts.0.relationship', 'Ibu')
            ->set('emergency_contacts.0.phone_number', '0812345')
            ->call('saveRegistration')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('registrations', [
            'registration_number' => 'REG-' . now()->format('dmY') . '-0001',
            'total_price' => 1000000,
            'birth_place' => 'Jakarta',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('emergency_contacts', [
            'name' => 'Jane Doe',
            'relationship' => 'Ibu',
        ]);
    }
}
