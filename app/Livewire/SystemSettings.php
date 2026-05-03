<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use ZipArchive;

class SystemSettings extends Component
{
    use WithFileUploads;

    public $backupFile;
    public $confirmingReset = false;

    public function downloadBackup()
    {
        $zipName = 'backup-' . now()->format('Y-m-d-H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add database
            $dbPath = config('database.connections.sqlite.database');
            if (File::exists($dbPath)) {
                $zip->addFile($dbPath, 'database.sqlite');
            } else {
                // For testing or if file doesn't exist, create an empty one in zip
                $zip->addFromString('database.sqlite', '');
            }

            // Add storage/app/public
            if (File::exists(storage_path('app/public'))) {
                $files = File::allFiles(storage_path('app/public'));
                foreach ($files as $file) {
                    $relativePath = 'storage/' . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }

            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function restore()
    {
        $this->validate([
            'backupFile' => 'required|file|mimes:zip|max:51200', // 50MB max
        ]);

        $path = $this->backupFile->store('temp');
        $fullPath = storage_path('app/' . $path);

        $zip = new ZipArchive;
        if ($zip->open($fullPath) === TRUE) {
            // Extract database
            $zip->extractTo(storage_path('app/temp_restore'));

            $extractedDb = storage_path('app/temp_restore/database.sqlite');
            if (File::exists($extractedDb)) {
                $targetDb = config('database.connections.sqlite.database');
                File::copy($extractedDb, $targetDb);
            }

            // Extract storage
            if (File::exists(storage_path('app/temp_restore/storage'))) {
                File::copyDirectory(
                    storage_path('app/temp_restore/storage'),
                    storage_path('app/public')
                );
            }

            $zip->close();

            // Clean up
            File::deleteDirectory(storage_path('app/temp_restore'));
            Storage::delete($path);

            session()->flash('success', 'Sistem berhasil direstore.');
            return redirect()->route('dashboard');
        } else {
            session()->flash('error', 'Gagal membuka file backup.');
        }
    }

    public function confirmReset()
    {
        $this->confirmingReset = true;
    }

    public function cancelReset()
    {
        $this->confirmingReset = false;
    }

    public function resetSystem()
    {
        // Truncate business data
        Booking::query()->delete();
        RoomImage::query()->delete();
        Room::query()->delete();
        Location::query()->delete();
        Facility::query()->delete();
        Rule::query()->delete();

        // Delete users except owners/developers
        User::role(['owner', 'developer'])->get(); // Just a check
        User::whereDoesntHave('roles', function($q) {
            $q->whereIn('name', ['owner', 'developer']);
        })->delete();

        // Clear storage
        File::cleanDirectory(storage_path('app/public/locations'));
        File::cleanDirectory(storage_path('app/public/rooms'));
        // Keep avatars? Maybe better clear them too if they are not owners
        // But for now, let's just clear the main data folders.

        $this->confirmingReset = false;
        session()->flash('success', 'Sistem berhasil direset.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.system-settings');
    }
}
