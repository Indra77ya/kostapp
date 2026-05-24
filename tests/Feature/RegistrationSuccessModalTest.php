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

class RegistrationSuccessModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
        Storage::fake('public');
    }

    public function test_success_modal_opens_after_new_registration()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price_monthly' => 1000000,
            'facilities' => 'Bed, AC',
            'status' => 'available'
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
            ->call('saveRegistration')
            ->assertSet('isSuccessModalOpen', true)
            ->assertSet('newlyCreatedRegistrationId', function($id) {
                return !is_null($id);
            })
            ->assertSee('Check In Berhasil!')
            ->assertSee('John Doe')
            ->assertSee('Kost A - Kamar 101')
            ->assertSee('Cetak Data')
            ->assertSee('Kirim WA');
    }

    public function test_success_modal_does_not_open_after_edit_registration()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'type' => 'Standard',
            'price_monthly' => 1000000,
            'facilities' => 'Bed, AC',
            'status' => 'available'
        ]);

        $tenant = User::factory()->create(['email' => 'existing@example.com']);
        $tenant->assignRole('tenant');

        $registration = \App\Models\Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-123',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123',
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'photo_self' => 'self.jpg',
            'photo_identity' => 'ktp.jpg',
        ]);

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->call('openModal', $registration->id)
            ->set('name', 'John Updated')
            ->call('saveRegistration')
            ->assertSet('isSuccessModalOpen', false)
            ->assertSet('newlyCreatedRegistrationId', null);
    }
}
