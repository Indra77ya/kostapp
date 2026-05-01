<?php

namespace App\Livewire;

use App\Models\Room;
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
    public $roomId;

    // Form fields
    public $room_number, $price, $status, $description, $image, $facilities, $room_type, $floor;
    public $newImage;

    protected $listeners = ['echo:stats,DatabaseUpdated' => 'loadRooms'];

    protected $rules = [
        'room_number' => 'required|unique:rooms,room_number',
        'price' => 'required|numeric',
        'status' => 'required|in:available,occupied,maintenance',
        'description' => 'nullable',
        'facilities' => 'nullable',
        'room_type' => 'nullable',
        'floor' => 'nullable',
        'newImage' => 'nullable|image|max:1024', // 1MB Max
    ];

    public function mount()
    {
        $this->loadRooms();
    }

    public function loadRooms()
    {
        $this->rooms = Room::all();
    }

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
            $room = Room::find($id);
            $this->room_number = $room->room_number;
            $this->price = $room->price;
            $this->status = $room->status;
            $this->description = $room->description;
            $this->facilities = $room->facilities;
            $this->room_type = $room->room_type;
            $this->floor = $room->floor;
            $this->image = $room->image;
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
        $this->roomId = null;
        $this->room_number = '';
        $this->price = '';
        $this->status = 'available';
        $this->description = '';
        $this->facilities = '';
        $this->room_type = '';
        $this->floor = '';
        $this->image = null;
        $this->newImage = null;
    }

    public function saveRoom()
    {
        $rules = $this->rules;
        if ($this->roomId) {
            $rules['room_number'] = 'required|unique:rooms,room_number,' . $this->roomId;
        }

        $this->validate($rules);

        $data = [
            'room_number' => $this->room_number,
            'price' => $this->price,
            'status' => $this->status,
            'description' => $this->description,
            'facilities' => $this->facilities,
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
            Room::find($this->roomId)->update($data);
        } else {
            Room::create($data);
        }

        $this->closeModal();
        $this->loadRooms();
    }

    public function deleteRoom($id)
    {
        $room = Room::find($id);
        if ($room->image) {
            Storage::disk('public')->delete($room->image);
        }
        $room->delete();
        $this->loadRooms();
    }

    public function render()
    {
        return view('livewire.room-manager');
    }
}
