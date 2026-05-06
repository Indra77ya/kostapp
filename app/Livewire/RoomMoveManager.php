<?php

namespace App\Livewire;

use App\Models\RoomMove;
use App\Models\Registration;
use App\Models\Room;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Carbon\Carbon;

class RoomMoveManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $isModalOpen = false;

    // Search & Filters
    public $search = '';
    public $filterLocationId = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';

    // Form fields
    public $registration_id, $new_room_id, $move_date, $reason;
    public $tenant_search = '';
    public $selectedLocationId;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    public function mount()
    {
        $this->move_date = Carbon::now()->format('Y-m-d');
    }

    public function updatedRegistrationId($value)
    {
        if ($value) {
            $reg = Registration::find($value);
            $this->selectedLocationId = $reg->location_id;
        } else {
            $this->selectedLocationId = null;
        }
        $this->new_room_id = null;
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->registration_id = null;
        $this->new_room_id = null;
        $this->move_date = Carbon::now()->format('Y-m-d');
        $this->reason = '';
        $this->selectedLocationId = null;
    }

    public function saveMove()
    {
        $this->validate([
            'registration_id' => 'required|exists:registrations,id',
            'new_room_id' => 'required|exists:rooms,id',
            'move_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $registration = Registration::find($this->registration_id);

        if ($registration->room_id == $this->new_room_id) {
            $this->addError('new_room_id', 'Kamar baru tidak boleh sama dengan kamar lama.');
            return;
        }

        DB::transaction(function () use ($registration) {
            $oldRoomId = $registration->room_id;

            // 1. Create History
            RoomMove::create([
                'registration_id' => $registration->id,
                'user_id' => $registration->user_id,
                'old_room_id' => $oldRoomId,
                'new_room_id' => $this->new_room_id,
                'move_date' => $this->move_date,
                'reason' => $this->reason,
            ]);

            // 2. Update Registration
            $registration->update(['room_id' => $this->new_room_id]);

            // 3. Update Room Statuses
            Room::where('id', $oldRoomId)->update(['status' => 'available']);
            Room::where('id', $this->new_room_id)->update(['status' => 'occupied']);
        });

        NotificationSent::dispatch("Perpindahan kamar berhasil diproses.", 'success');
        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterLocationId()
    {
        $this->resetPage();
    }

    public function updatingFilterDateStart()
    {
        $this->resetPage();
    }

    public function updatingFilterDateEnd()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocationId', 'filterDateStart', 'filterDateEnd']);
        $this->resetPage();
    }

    public function selectTenant($id, $locationId)
    {
        $this->registration_id = $id;
        $this->selectedLocationId = $locationId;
        $this->new_room_id = null;
        $this->tenant_search = '';
    }

    public function render()
    {
        $query = RoomMove::with(['registration.user', 'oldRoom', 'newRoom', 'registration.location']);

        if ($this->search) {
            $query->whereHas('registration.user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })->orWhereHas('registration', function($q) {
                $q->where('registration_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterLocationId) {
            $query->whereHas('oldRoom', function($q) {
                $q->where('location_id', $this->filterLocationId);
            });
        }

        if ($this->filterDateStart) {
            $query->where('move_date', '>=', $this->filterDateStart);
        }

        if ($this->filterDateEnd) {
            $query->where('move_date', '<=', $this->filterDateEnd);
        }

        // Active registrations for the search dropdown in modal
        $activeRegistrations = [];
        if ($this->tenant_search) {
            $activeRegistrations = Registration::with('user', 'room', 'location')
                ->where('status', 'active')
                ->whereHas('user', function($q) {
                    $q->where('name', 'like', '%' . $this->tenant_search . '%');
                })
                ->get();
        } elseif (!$this->registration_id) {
            $activeRegistrations = Registration::with('user', 'room', 'location')
                ->where('status', 'active')
                ->limit(5)
                ->get();
        }

        // Available rooms for the selected location
        $availableRooms = [];
        if ($this->selectedLocationId) {
            $availableRooms = Room::where('location_id', $this->selectedLocationId)
                ->where('status', 'available')
                ->orderBy('room_number')
                ->get();
        }

        return view('livewire.room-move-manager', [
            'moves' => $query->latest()->paginate(10),
            'activeRegistrations' => $activeRegistrations,
            'availableRooms' => $availableRooms,
            'locations' => Location::orderBy('name')->get(),
            'selectedRegistration' => $this->registration_id ? Registration::with('user', 'room', 'location')->find($this->registration_id) : null,
        ]);
    }
}
