<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Livewire\LocationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class LocationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner']);
    }

    public function test_location_manager_is_accessible_by_owner()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $response = $this->actingAs($owner)->get('/locations');

        $response->assertStatus(200);
        $response->assertSeeLivewire(LocationManager::class);
    }


    public function test_can_create_location()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(LocationManager::class)
            ->set('name', 'Kost Baru')
            ->set('address', 'Jl. Baru No. 123')
            ->call('saveLocation');

        $this->assertTrue(Location::where('name', 'Kost Baru')->exists());
    }

    public function test_can_update_location()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');
        $location = Location::create(['name' => 'Old Name']);

        Livewire::actingAs($owner)
            ->test(LocationManager::class)
            ->call('openModal', $location->id)
            ->set('name', 'New Name')
            ->call('saveLocation');

        $this->assertEquals('New Name', $location->refresh()->name);
    }

    public function test_can_delete_location()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');
        $location = Location::create(['name' => 'To Delete']);

        Livewire::actingAs($owner)
            ->test(LocationManager::class)
            ->call('deleteLocation', $location->id);

        $this->assertFalse(Location::where('id', $location->id)->exists());
    }
}
