<?php

namespace Tests\Feature;

use App\Livewire\RegistrationManager;
use App\Models\Location;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantReRegistrationAndLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('owner');
        Role::findOrCreate('admin');
        Role::findOrCreate('tenant');
    }

    public function test_can_re_register_checked_out_tenant_with_same_email()
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Lokasi Test', 'address' => 'Alamat']);
        $room1 = Room::create(['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1000000, 'status' => 'available']);
        $room2 = Room::create(['location_id' => $location->id, 'room_number' => '102', 'price_monthly' => 1000000, 'status' => 'available']);

        // Existing checked out tenant
        $tenantUser = User::factory()->create([
            'name' => 'Penghuni Lama',
            'email' => 'tenant.lama@example.com',
        ]);
        $tenantUser->assignRole('tenant');

        Registration::create([
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room1->id,
            'registration_number' => 'REG-OLD-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123456789',
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-01-01',
            'status' => 'checked_out',
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room2->id)
            ->set('stay_start_date', now()->format('Y-m-d'))
            ->set('duration_type', 'monthly')
            ->set('duration_value', 1)
            ->set('name', 'Penghuni Lama Update')
            ->set('email', 'tenant.lama@example.com')
            ->set('identity_number', '123456789')
            ->set('gender', 'Laki-laki')
            ->set('birth_place', 'Jakarta')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->call('saveRegistration')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('registrations', [
            'user_id' => $tenantUser->id,
            'room_id' => $room2->id,
            'status' => 'active',
        ]);

        $this->assertEquals('Penghuni Lama Update', $tenantUser->fresh()->name);
        $this->assertEquals(2, Registration::where('user_id', $tenantUser->id)->count());
    }

    public function test_cannot_register_with_email_of_active_tenant()
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $location = Location::create(['name' => 'Lokasi Test', 'address' => 'Alamat']);
        $room1 = Room::create(['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1000000, 'status' => 'occupied']);
        $room2 = Room::create(['location_id' => $location->id, 'room_number' => '102', 'price_monthly' => 1000000, 'status' => 'available']);

        $activeTenant = User::factory()->create([
            'email' => 'active.tenant@example.com',
        ]);
        $activeTenant->assignRole('tenant');

        Registration::create([
            'user_id' => $activeTenant->id,
            'location_id' => $location->id,
            'room_id' => $room1->id,
            'registration_number' => 'REG-ACTIVE-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123456789',
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-01-01',
            'status' => 'active',
        ]);

        $photoSelf = UploadedFile::fake()->image('self.jpg');
        $photoIdentity = UploadedFile::fake()->image('ktp.jpg');

        Livewire::actingAs($admin)
            ->test(RegistrationManager::class)
            ->set('location_id', $location->id)
            ->set('room_id', $room2->id)
            ->set('stay_start_date', now()->format('Y-m-d'))
            ->set('duration_type', 'monthly')
            ->set('duration_value', 1)
            ->set('name', 'Penghuni Aktif Coba Lagi')
            ->set('email', 'active.tenant@example.com')
            ->set('identity_number', '123456789')
            ->set('gender', 'Laki-laki')
            ->set('birth_place', 'Jakarta')
            ->set('birth_date', '1995-01-01')
            ->set('photo_self', $photoSelf)
            ->set('photo_identity', $photoIdentity)
            ->call('saveRegistration')
            ->assertHasErrors(['email' => 'Penghuni dengan email ini masih memiliki status sewa aktif.']);
    }

    public function test_checked_out_tenant_cannot_login()
    {
        $tenantUser = User::factory()->create([
            'email' => 'checkedout@example.com',
            'password' => bcrypt('password123'),
        ]);
        $tenantUser->assignRole('tenant');

        $location = Location::create(['name' => 'Lokasi Test', 'address' => 'Alamat']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1000000, 'status' => 'available']);

        Registration::create([
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-OLD-002',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123456789',
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-01-01',
            'status' => 'checked_out',
        ]);

        $response = $this->post('/login', [
            'email' => 'checkedout@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_active_tenant_can_login()
    {
        $tenantUser = User::factory()->create([
            'email' => 'active@example.com',
            'password' => bcrypt('password123'),
        ]);
        $tenantUser->assignRole('tenant');

        $location = Location::create(['name' => 'Lokasi Test', 'address' => 'Alamat']);
        $room = Room::create(['location_id' => $location->id, 'room_number' => '101', 'price_monthly' => 1000000, 'status' => 'occupied']);

        Registration::create([
            'user_id' => $tenantUser->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-ACT-002',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123456789',
            'gender' => 'Laki-laki',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-01-01',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($tenantUser);
    }
}
