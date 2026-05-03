<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\UserManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class UserManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::create(['name' => 'developer']);
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'admin']);
    }

    public function test_user_manager_is_accessible_by_owner()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $response = $this->actingAs($owner)->get('/users');

        $response->assertStatus(200);
        $response->assertSeeLivewire(UserManager::class);
    }


    public function test_can_create_user()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(UserManager::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('role', 'admin')
            ->set('password', '12345678')
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($newUser->hasRole('admin'));
    }

    public function test_can_update_user_role()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('admin');

        Livewire::actingAs($owner)
            ->test(UserManager::class)
            ->call('openModal', $targetUser->id)
            ->set('role', 'owner')
            ->call('saveUser')
            ->assertHasNoErrors();

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('owner'));
        $this->assertFalse($targetUser->hasRole('admin'));
    }

    public function test_can_delete_user()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('admin');

        Livewire::actingAs($owner)
            ->test(UserManager::class)
            ->call('deleteUser', $targetUser->id);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_cannot_delete_self()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(UserManager::class)
            ->call('deleteUser', $owner->id);

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
        ]);
    }
}
