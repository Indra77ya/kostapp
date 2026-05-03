<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\SystemSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner']);
        Role::create(['name' => 'developer']);
        Role::create(['name' => 'tenant']);
    }

    public function test_unauthorized_user_cannot_access_settings()
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_settings()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertOk();
    }

    public function test_backup_can_be_downloaded()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        Livewire::actingAs($user)
            ->test(SystemSettings::class)
            ->call('downloadBackup')
            ->assertStatus(200);
    }
}
