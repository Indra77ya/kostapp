<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\SystemSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

class SystemSettingsReproTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (!Role::where('name', 'owner')->exists()) {
            Role::create(['name' => 'owner']);
        }
    }

    public function test_restore_repro()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        $tempZip = tempnam(sys_get_temp_dir(), 'zip') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            $this->fail('Could not create temp zip');
        }
        $zip->addFromString('database.sqlite', 'fake-db-content');
        $zip->close();

        $file = new \Illuminate\Http\UploadedFile($tempZip, 'backup.zip', 'application/zip', null, true);

        $test = Livewire::actingAs($user)
            ->test(SystemSettings::class)
            ->set('backupFile', $file)
            ->call('restore');

        if (session('error')) {
            dump(session('error'));
        }

        $this->assertEquals('Sistem berhasil direstore.', session('success'));

        if (File::exists($tempZip)) {
            unlink($tempZip);
        }
    }
}
