<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Rule;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\RuleManager;
use Spatie\Permission\Models\Role;

class RuleManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles if not exists
        if (Role::count() === 0) {
            Role::create(['name' => 'owner']);
            Role::create(['name' => 'developer']);
            Role::create(['name' => 'tenant']);
        }
    }

    public function test_rule_manager_is_accessible_by_owner()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->get(route('rules.index'))
            ->assertStatus(200);
    }

    public function test_rule_manager_is_not_accessible_by_tenant()
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)
            ->get(route('rules.index'))
            ->assertStatus(403);
    }

    public function test_can_create_rule()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(RuleManager::class)
            ->set('title', 'Dilarang Berisik')
            ->set('description', 'Harap tenang setelah jam 10 malam')
            ->set('category', 'Keamanan')
            ->call('saveRule')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rules', [
            'title' => 'Dilarang Berisik',
            'category' => 'Keamanan'
        ]);
    }

    public function test_can_create_rule_with_location()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');
        $location = Location::create(['name' => 'Kost A', 'address' => 'Jl. A']);

        Livewire::actingAs($owner)
            ->test(RuleManager::class)
            ->set('title', 'Parkir Motor')
            ->set('category', 'Parkir')
            ->set('location_id', $location->id)
            ->call('saveRule')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rules', [
            'title' => 'Parkir Motor',
            'location_id' => $location->id
        ]);
    }

    public function test_can_update_rule()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $rule = Rule::create([
            'title' => 'Aturan Lama',
            'category' => 'Umum',
            'is_active' => true
        ]);

        Livewire::actingAs($owner)
            ->test(RuleManager::class)
            ->call('openModal', $rule->id)
            ->set('title', 'Aturan Baru')
            ->call('saveRule')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rules', [
            'id' => $rule->id,
            'title' => 'Aturan Baru'
        ]);
    }

    public function test_can_toggle_rule_status()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $rule = Rule::create([
            'title' => 'Aturan Aktif',
            'category' => 'Umum',
            'is_active' => true
        ]);

        Livewire::actingAs($owner)
            ->test(RuleManager::class)
            ->call('toggleStatus', $rule->id);

        $this->assertDatabaseHas('rules', [
            'id' => $rule->id,
            'is_active' => false
        ]);
    }

    public function test_can_delete_rule()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $rule = Rule::create([
            'title' => 'Aturan Dihapus',
            'category' => 'Umum'
        ]);

        Livewire::actingAs($owner)
            ->test(RuleManager::class)
            ->call('deleteRule', $rule->id);

        $this->assertDatabaseMissing('rules', ['id' => $rule->id]);
    }

    public function test_can_open_preview_modal()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $rule = Rule::create([
            'title' => 'Aturan Preview',
            'category' => 'Umum',
            'description' => 'Isi preview'
        ]);

        Livewire::actingAs($owner)
            ->test(RuleManager::class)
            ->call('openPreviewModal', $rule->id)
            ->assertSet('isPreviewModalOpen', true)
            ->assertSet('previewRule.id', $rule->id)
            ->call('closePreviewModal')
            ->assertSet('isPreviewModalOpen', false)
            ->assertSet('previewRule', null);
    }
}
