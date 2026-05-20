<?php

namespace Tests\Feature;

use App\Livewire\RegistrationManager;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationPhotoPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
        Storage::fake('public');
    }

    public function test_existing_photos_are_loaded_into_component_state_when_editing()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price_monthly' => 1000000
        ]);

        $tenant = User::factory()->create(['name' => 'John Doe']);

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'photo_self' => 'photos/self.jpg',
            'photo_identity' => 'photos/identity.jpg',
            'photo_family_card' => 'photos/kk.jpg',
            'status' => 'active'
        ]);

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->call('openModal', $registration->id)
            ->assertSet('existing_photo_self', 'photos/self.jpg')
            ->assertSet('existing_photo_identity', 'photos/identity.jpg')
            ->assertSet('existing_photo_family_card', 'photos/kk.jpg')
            ->assertSee('Tersimpan');
    }
}
