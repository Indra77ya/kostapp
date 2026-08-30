<?php

namespace App\Livewire;

use App\Models\Facility;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;

class FacilityManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    public $isModalOpen = false;
    public $facilityId;

    // Import
    public $isImportModalOpen = false;
    public $importFile;

    // Search & Filter
    public $search = '';
    public $filterCategory = '';

    // Form fields
    public $name, $category;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:facilities,name,' . $this->facilityId,
            'category' => 'required|string|max:255',
        ];
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->facilityId = $id;
            $facility = Facility::find($id);
            $this->name = $facility->name;
            $this->category = $facility->category;
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->facilityId = null;
        $this->name = '';
        $this->category = 'Kamar';
    }

    public function saveFacility()
    {
        $this->validate($this->rules());

        $data = [
            'name' => $this->name,
            'category' => $this->category,
        ];

        if ($this->facilityId) {
            $facility = Facility::find($this->facilityId);
            $facility->update($data);
            $message = "Fasilitas {$facility->name} telah diperbarui.";
            $type = 'info';
            $this->dispatch('notify', message: $message, type: $type, hideInBell: true);
            broadcast(new NotificationSent($message, $type, hideInBell: true))->toOthers();
        } else {
            $facility = Facility::create($data);
            $message = "Fasilitas baru {$facility->name} telah ditambahkan.";
            $type = 'success';
            $this->dispatch('notify', message: $message, type: $type, hideInBell: true);
            broadcast(new NotificationSent($message, $type, hideInBell: true))->toOthers();
        }

        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deleteFacility($id)
    {
        $facility = Facility::find($id);
        $facilityName = $facility->name;
        $facility->delete();

        $message = "Fasilitas {$facilityName} telah dihapus.";
        $type = 'warning';
        $this->dispatch('notify', message: $message, type: $type, hideInBell: true);
        broadcast(new NotificationSent($message, $type, hideInBell: true))->toOthers();
        DatabaseUpdated::dispatch();
    }

    public function openImportModal()
    {
        $this->reset(['importFile']);
        $this->resetValidation();
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->reset(['importFile']);
    }

    public function downloadTemplate($format = 'xlsx')
    {
        return app(\App\Services\MasterDataImportExportService::class)->downloadTemplate('facilities', $format);
    }

    public function exportData($format = 'xlsx')
    {
        return app(\App\Services\MasterDataImportExportService::class)->export('facilities', $format);
    }

    public function importData()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'importFile.required' => 'Pilih file terlebih dahulu.',
            'importFile.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'importFile.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $path = $this->importFile->getRealPath();
            $result = app(\App\Services\MasterDataImportExportService::class)->import('facilities', $path);

            $msg = "Impor data fasilitas berhasil! ({$result['created']} ditambahkan, {$result['updated']} diperbarui).";
            $this->dispatch('notify', message: $msg, type: 'success', hideInBell: true);
            broadcast(new NotificationSent($msg, 'success', hideInBell: true))->toOthers();
            DatabaseUpdated::dispatch();
            $this->closeImportModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', message: "Gagal impor: " . $e->getMessage(), type: 'error');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCategory']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Facility::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        $facilities = $query->orderBy('category')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.facility-manager', [
            'facilities' => $facilities
        ]);
    }
}
