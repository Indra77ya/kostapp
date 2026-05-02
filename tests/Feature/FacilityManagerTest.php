<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\FacilityManager;
use Spatie\Permission\Models\Role;

class FacilityManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles if not exists
        if (Role::count() === 0) {
            Role::create(['name' => 'owner']);
            Role::create(['name' => 'tenant']);
        }
    }

    public function test_facility_manager_is_accessible_by_owner()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->get(route('facilities.index'))
            ->assertStatus(200);
    }

    public function test_facility_manager_is_not_accessible_by_tenant()
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)
            ->get(route('facilities.index'))
            ->assertStatus(403);
    }

    public function test_can_create_facility()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(FacilityManager::class)
            ->set('name', 'AC Baru')
            ->set('category', 'Kamar')
            ->call('saveFacility')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('facilities', [
            'name' => 'AC Baru',
            'category' => 'Kamar'
        ]);
    }

    public function test_can_update_facility()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $facility = Facility::create(['name' => 'WiFi Lama', 'category' => 'Umum']);

        Livewire::actingAs($owner)
            ->test(FacilityManager::class)
            ->call('openModal', $facility->id)
            ->set('name', 'WiFi Cepat')
            ->call('saveFacility')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'WiFi Cepat'
        ]);
    }

    public function test_can_delete_facility()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $facility = Facility::create(['name' => 'TV', 'category' => 'Kamar']);

        Livewire::actingAs($owner)
            ->test(FacilityManager::class)
            ->call('deleteFacility', $facility->id);

        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
    }
}
