<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\RoomImage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class RoomManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $viewType = 'grid'; // 'grid' or 'table'
    public $isModalOpen = false;
    public $isLightboxOpen = false;
    public $lightboxImageUrl = '';
    public $roomId;

    // Search and Filters
    public $search = '';
    public $filterStatus = '';
    public $filterFloor = '';
    public $filterRentalType = '';

    // Form fields
    public $location_id, $room_number, $price_monthly, $price_daily, $price_weekly, $price_yearly, $status, $description, $image, $facilities = [], $room_type, $floor;
    public $newImage;
    public $gallery = [];
    public $newGallery = [];

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    protected $rules = [
        'location_id' => 'nullable|exists:locations,id',
        'room_number' => 'required|unique:rooms,room_number',
        'price_monthly' => 'required|numeric',
        'price_daily' => 'nullable|numeric',
        'price_weekly' => 'nullable|numeric',
        'price_yearly' => 'nullable|numeric',
        'status' => 'required|in:available,occupied,maintenance',
        'description' => 'nullable',
        'facilities' => 'nullable',
        'room_type' => 'nullable',
        'floor' => 'nullable',
        'newImage' => 'nullable|image|max:1024', // 1MB Max
    ];

    public function setView($type)
    {
        $this->viewType = $type;
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->roomId = $id;
            $room = Room::with('images')->find($id);
            $this->location_id = $room->location_id;
            $this->room_number = $room->room_number;
            $this->price_monthly = $room->price_monthly;
            $this->price_daily = $room->price_daily;
            $this->price_weekly = $room->price_weekly;
            $this->price_yearly = $room->price_yearly;
            $this->status = $room->status;
            $this->description = $room->description;
            $this->facilities = $room->facilities;
            $this->room_type = $room->room_type;
            $this->floor = $room->floor;
            $this->image = $room->image;
            $this->facilities = $room->facilities ? explode(', ', $room->facilities) : [];
            $this->gallery = $room->images->toArray();
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function openLightbox($url)
    {
        $this->lightboxImageUrl = $url;
        $this->isLightboxOpen = true;
    }

    public function closeLightbox()
    {
        $this->isLightboxOpen = false;
    }

    private function resetForm()
    {
        $this->roomId = null;
        $this->location_id = null;
        $this->room_number = '';
        $this->price_monthly = '';
        $this->price_daily = '';
        $this->price_weekly = '';
        $this->price_yearly = '';
        $this->status = 'available';
        $this->description = '';
        $this->facilities = [];
        $this->room_type = '';
        $this->floor = '';
        $this->image = null;
        $this->newImage = null;
        $this->gallery = [];
        $this->newGallery = [];
    }

    public function saveRoom()
    {
        $rules = $this->rules;
        if ($this->roomId) {
            $rules['room_number'] = 'required|unique:rooms,room_number,' . $this->roomId;
        }

        $this->validate($rules);

        $data = [
            'location_id' => $this->location_id ?: null,
            'room_number' => $this->room_number,
            'price_monthly' => $this->price_monthly,
            'price_daily' => $this->price_daily ?: null,
            'price_weekly' => $this->price_weekly ?: null,
            'price_yearly' => $this->price_yearly ?: null,
            'status' => $this->status,
            'description' => $this->description,
            'facilities' => is_array($this->facilities) ? implode(', ', $this->facilities) : $this->facilities,
            'room_type' => $this->room_type,
            'floor' => $this->floor,
        ];

        if ($this->newImage) {
            $imagePath = $this->newImage->store('rooms', 'public');
            $data['image'] = $imagePath;

            // Delete old image if exists
            if ($this->roomId) {
                $oldRoom = Room::find($this->roomId);
                if ($oldRoom->image) {
                    Storage::disk('public')->delete($oldRoom->image);
                }
            }
        }

        if ($this->roomId) {
            $room = Room::find($this->roomId);
            $room->update($data);
        } else {
            $room = Room::create($data);
        }

        // Handle Gallery
        if ($this->newGallery) {
            foreach ($this->newGallery as $photo) {
                $photoPath = $photo->store('rooms/gallery', 'public');
                $room->images()->create(['image_path' => $photoPath]);
            }
        }

        $this->closeModal();
    }

    public function deleteRoom($id)
    {
        $room = Room::with('images')->find($id);

        // Delete main image
        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }

        // Delete gallery images
        foreach ($room->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        $room->delete();
    }

    public function deleteGalleryImage($imageId)
    {
        $image = RoomImage::find($imageId);
        if ($image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();

            // Refresh gallery
            if ($this->roomId) {
                $this->gallery = RoomImage::where('room_id', $this->roomId)->get()->toArray();
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterFloor()
    {
        $this->resetPage();
    }

    public function updatingFilterRentalType()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatus', 'filterFloor', 'filterRentalType']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Room::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('room_number', 'like', '%' . $this->search . '%')
                  ->orWhere('room_type', 'like', '%' . $this->search . '%')
                  ->orWhere('facilities', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterFloor) {
            $query->where('floor', $this->filterFloor);
        }

        if ($this->filterRentalType) {
            $column = 'price_' . $this->filterRentalType;
            $query->whereNotNull($column)->where($column, '>', 0);
        }

        $floors = Room::whereNotNull('floor')->distinct()->pluck('floor')->sort();
        $locations = \App\Models\Location::orderBy('name')->get();
        $allFacilities = \App\Models\Facility::orderBy('category')->orderBy('name')->get()->groupBy('category');

        return view('livewire.room-manager', [
            'rooms' => $query->with(['images', 'location'])->orderBy('room_number')->paginate(12),
            'floors' => $floors,
            'locations' => $locations,
            'allFacilities' => $allFacilities
        ]);
    }
}
