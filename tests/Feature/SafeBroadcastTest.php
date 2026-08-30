<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use App\Livewire\RegistrationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SafeBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'tenant']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);
    }

    public function test_sanitize_socket_id_header_middleware_removes_invalid_headers(): void
    {
        $response = $this->withHeaders([
            'X-Socket-ID' => 'undefined',
        ])->get('/dashboard');

        $response->assertStatus(200);
        $this->assertNull(request()->header('X-Socket-ID'));
    }

    public function test_check_in_registration_succeeds_even_with_undefined_socket_id_header(): void
    {
        Storage::fake('public');

        $location = Location::create(['name' => 'Location Safe Broadcast']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => 'SB-101',
            'type' => 'Standard',
            'price_monthly' => 1500000,
            'status' => 'available',
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        $component = Livewire::withHeaders([
            'X-Socket-ID' => 'undefined',
        ])
        ->test(RegistrationManager::class)
        ->set('location_id', $location->id)
        ->set('room_id', $room->id)
        ->set('stay_start_date', '2026-09-01')
        ->set('name', 'John Safe Broadcast')
        ->set('email', 'john.safebroadcast@example.com')
        ->set('identity_number', '1234567890123456')
        ->set('gender', 'Laki-laki')
        ->set('birth_date', '1995-05-15')
        ->set('photo_self', $photoSelf)
        ->set('photo_identity', $photoIdentity)
        ->call('saveRegistration');

        $component->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'john.safebroadcast@example.com',
        ]);

        $this->assertDatabaseHas('registrations', [
            'room_id' => $room->id,
            'location_id' => $location->id,
        ]);
    }
}
