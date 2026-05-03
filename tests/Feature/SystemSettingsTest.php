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

    public function test_restore_works_with_valid_zip()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        // Create a valid zip using UploadedFile::fake() as it's better integrated with Livewire testing
        \Illuminate\Support\Facades\Storage::fake('local');

        $zipContent = '';
        $tempZip = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new \ZipArchive();
        if ($zip->open($tempZip, \ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('database.sqlite', 'fake-db-content');
            $zip->close();
            $zipContent = file_get_contents($tempZip);
            unlink($tempZip);
        }

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('backup.zip', $zipContent);

        Livewire::actingAs($user)
            ->test(SystemSettings::class)
            ->set('backupFile', $file)
            ->call('restore')
            ->assertRedirect(route('dashboard'));

        $this->assertEquals('Sistem berhasil direstore.', session('success'));
    }
}
