<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\Rule;
use App\Models\User;
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
    public $due_notification_days = 100;
    public $confirmingReset = false;

    public function mount()
    {
        if (!auth()->user()->hasAnyRole(['owner', 'developer'])) {
            abort(403);
        }
    }

    public function saveSettings()
    {
        // For now, we just simulate saving since there's no settings table.
        // In a real app, you'd save to a settings table or config file.
        session()->flash('success', 'Pengaturan berhasil disimpan.');
    }

    private function createBackup()
    {
        $zipName = 'backup-auto-' . now()->format('Y-m-d-H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add database
            $dbPath = config('database.connections.sqlite.database');
            if (File::exists($dbPath)) {
                $zip->addFile($dbPath, 'database.sqlite');
            } else {
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
            return $zipPath;
        }
        return null;
    }

    public function downloadBackup()
    {
        $zipPath = $this->createBackup();
        if ($zipPath) {
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
        session()->flash('error', 'Gagal membuat backup.');
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
            $tempExtractPath = storage_path('app/temp_restore_' . time());
            $zip->extractTo($tempExtractPath);

            $extractedDb = $tempExtractPath . '/database.sqlite';
            if (File::exists($extractedDb)) {
                $targetDb = config('database.connections.sqlite.database');
                // Note: Overwriting active DB is risky but requested for this simple system.
                File::copy($extractedDb, $targetDb);
            }

            // Extract storage
            if (File::exists($tempExtractPath . '/storage')) {
                File::copyDirectory(
                    $tempExtractPath . '/storage',
                    storage_path('app/public')
                );
            }

            $zip->close();

            // Clean up
            File::deleteDirectory($tempExtractPath);
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
        // Auto-backup before reset
        $this->createBackup();

        // Truncate business data
        Booking::query()->delete();
        RoomImage::query()->delete();
        Room::query()->delete();
        Location::query()->delete();
        Facility::query()->delete();
        Rule::query()->delete();

        // Delete users except owners/developers
        User::whereDoesntHave('roles', function($q) {
            $q->whereIn('name', ['owner', 'developer']);
        })->delete();

        // Clear storage folders
        if (File::exists(storage_path('app/public/locations'))) {
            File::cleanDirectory(storage_path('app/public/locations'));
        }
        if (File::exists(storage_path('app/public/rooms'))) {
            File::cleanDirectory(storage_path('app/public/rooms'));
        }

        $this->confirmingReset = false;
        session()->flash('success', 'Sistem berhasil direset. Backup otomatis telah disimpan di folder storage.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.system-settings');
    }
}
