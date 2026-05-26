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

class RegistrationFileDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
        Storage::fake('public');
    }

    public function test_files_are_deleted_when_registration_is_deleted()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price_monthly' => 1000000,
            'facilities' => 'Bed, AC'
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('identity.jpg');
        $photoFamilyCard = UploadedFile::fake()->image('family.jpg');

        // Create a registration via Livewire
        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room->id)
            ->set('stay_start_date', '2026-06-01')
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('identity_number', '123456789')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->set('photo_family_card', $photoFamilyCard)
            ->call('saveRegistration');

        $registration = Registration::first();
        $pathSelf = $registration->photo_self;
        $pathIdentity = $registration->photo_identity;
        $pathFamilyCard = $registration->photo_family_card;

        // Verify files exist
        Storage::disk('public')->assertExists($pathSelf);
        Storage::disk('public')->assertExists($pathIdentity);
        Storage::disk('public')->assertExists($pathFamilyCard);

        // Delete the registration
        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->call('deleteRegistration', $registration->id);

        // Verify files are deleted
        Storage::disk('public')->assertMissing($pathSelf);
        Storage::disk('public')->assertMissing($pathIdentity);
        Storage::disk('public')->assertMissing($pathFamilyCard);
    }
}
