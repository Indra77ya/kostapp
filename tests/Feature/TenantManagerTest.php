<?php

namespace Tests\Feature;

use App\Livewire\TenantManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Migrations will run, and my migration adds 'tenant' role.
        // We should ensure roles exist if they are not already there.
        Role::firstOrCreate(['name' => 'developer']);
        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'tenant']);
    }


    public function test_can_edit_tenant()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $tenant = User::factory()->create(['name' => 'Old Name']);
        $tenant->assignRole('tenant');

        Livewire::actingAs($admin)
            ->test(TenantManager::class)
            ->call('openModal', $tenant->id)
            ->set('name', 'New Name')
            ->call('saveTenant');

        $this->assertEquals('New Name', $tenant->fresh()->name);
    }

    public function test_can_delete_tenant()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        Livewire::actingAs($admin)
            ->test(TenantManager::class)
            ->call('deleteTenant', $tenant->id);

        $this->assertDatabaseMissing('users', ['id' => $tenant->id]);
    }
}
