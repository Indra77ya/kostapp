<?php

namespace App\Livewire;

use App\Models\Location;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;

class LocationManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $viewType = 'grid'; // 'grid' or 'table'
    public $isModalOpen = false;
    public $locationId;

    // Search
    public $search = '';

    // Form fields
    public $name, $address, $google_maps_link, $phone, $description, $image;
    public $newImage;

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string',
        'google_maps_link' => 'nullable|url',
        'phone' => 'nullable|string',
        'description' => 'nullable|string',
        'newImage' => 'nullable|image|max:1024',
    ];

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->locationId = $id;
            $location = Location::find($id);
            $this->name = $location->name;
            $this->address = $location->address;
            $this->google_maps_link = $location->google_maps_link;
            $this->phone = $location->phone;
            $this->description = $location->description;
            $this->image = $location->image;
        }

        $this->isModalOpen = true;
    }

    public function setView($type)
    {
        $this->viewType = $type;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->locationId = null;
        $this->name = '';
        $this->address = '';
        $this->google_maps_link = '';
        $this->phone = '';
        $this->description = '';
        $this->image = null;
        $this->newImage = null;
    }

    public function saveLocation()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'google_maps_link' => $this->google_maps_link,
            'phone' => $this->phone,
            'description' => $this->description,
        ];

        if ($this->newImage) {
            $imagePath = $this->newImage->store('locations', 'public');
            $data['image'] = $imagePath;

            if ($this->locationId) {
                $oldLocation = Location::find($this->locationId);
                if ($oldLocation->image) {
                    Storage::disk('public')->delete($oldLocation->image);
                }
            }
        }

        if ($this->locationId) {
            $location = Location::find($this->locationId);
            $location->update($data);
            NotificationSent::dispatch("Lokasi {$location->name} telah diperbarui.", 'info');
        } else {
            $location = Location::create($data);
            NotificationSent::dispatch("Lokasi baru {$location->name} telah ditambahkan.", 'success');
        }

        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deleteLocation($id)
    {
        $location = Location::find($id);
        if ($location->image) {
            Storage::disk('public')->delete($location->image);
        }
        $locationName = $location->name;
        $location->delete();

        NotificationSent::dispatch("Lokasi {$locationName} telah dihapus.", 'warning');
        DatabaseUpdated::dispatch();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function render()
    {
        $locations = Location::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('address', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.location-manager', [
            'locations' => $locations
        ]);
    }
}
