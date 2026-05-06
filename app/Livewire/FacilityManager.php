<?php

namespace App\Livewire;

use App\Models\Facility;
use Livewire\Component;
use Livewire\WithPagination;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;

class FacilityManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $isModalOpen = false;
    public $facilityId;

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
            NotificationSent::dispatch("Fasilitas {$facility->name} telah diperbarui.", 'info');
        } else {
            $facility = Facility::create($data);
            NotificationSent::dispatch("Fasilitas baru {$facility->name} telah ditambahkan.", 'success');
        }

        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deleteFacility($id)
    {
        $facility = Facility::find($id);
        $facilityName = $facility->name;
        $facility->delete();

        NotificationSent::dispatch("Fasilitas {$facilityName} telah dihapus.", 'warning');
        DatabaseUpdated::dispatch();
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
