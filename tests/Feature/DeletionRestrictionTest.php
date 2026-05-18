<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class DeletionRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles and Owner
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'tenant']);

        $this->owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->owner->assignRole('owner');
    }

    /** @test */
    public function cannot_delete_location_with_rooms()
    {
        $location = Location::create(['name' => 'Test Location']);
        Room::create([
            'location_id' => $location->id,
            'room_number' => '101',
            'price_monthly' => 1000000,
            'status' => 'available'
        ]);

        Livewire::actingAs($this->owner)
            ->test(\App\Livewire\LocationManager::class)
            ->call('deleteLocation', $location->id);

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    /** @test */
    public function cannot_delete_occupied_room()
    {
        $room = Room::create([
            'room_number' => '102',
            'price_monthly' => 1000000,
            'status' => 'occupied'
        ]);

        Livewire::actingAs($this->owner)
            ->test(\App\Livewire\RoomManager::class)
            ->call('deleteRoom', $room->id);

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    /** @test */
    public function cannot_delete_tenant_with_active_registration()
    {
        $tenant = User::create([
            'name' => 'Tenant User',
            'email' => 'tenant@example.com',
            'password' => bcrypt('password'),
        ]);
        $tenant->assignRole('tenant');

        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '103',
            'price_monthly' => 1000000,
            'status' => 'occupied'
        ]);

        Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-001',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'status' => 'active',
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123456',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01'
        ]);

        Livewire::actingAs($this->owner)
            ->test(\App\Livewire\TenantManager::class)
            ->call('deleteTenant', $tenant->id);

        $this->assertDatabaseHas('users', ['id' => $tenant->id]);
    }

    /** @test */
    public function deleting_registration_also_deletes_tenant_and_frees_room()
    {
        $tenant = User::create([
            'name' => 'Tenant User',
            'email' => 'tenant@example.com',
            'password' => bcrypt('password'),
        ]);
        $tenant->assignRole('tenant');

        $location = Location::create(['name' => 'Test Location']);
        $room = Room::create([
            'location_id' => $location->id,
            'room_number' => '104',
            'price_monthly' => 1000000,
            'status' => 'occupied'
        ]);

        $registration = Registration::create([
            'user_id' => $tenant->id,
            'location_id' => $location->id,
            'room_id' => $room->id,
            'registration_number' => 'REG-002',
            'registration_date' => now(),
            'stay_start_date' => now(),
            'status' => 'active',
            'room_price' => 1000000,
            'total_price' => 1000000,
            'identity_type' => 'KTP',
            'identity_number' => '123456',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01'
        ]);

        Livewire::actingAs($this->owner)
            ->test(\App\Livewire\RegistrationManager::class)
            ->call('deleteRegistration', $registration->id);

        $this->assertDatabaseMissing('registrations', ['id' => $registration->id]);
        $this->assertDatabaseMissing('users', ['id' => $tenant->id]);

        $room->refresh();
        $this->assertEquals('available', $room->status);
    }
}
