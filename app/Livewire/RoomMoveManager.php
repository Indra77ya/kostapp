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
    public $duration_type = 'monthly', $duration_value = 1, $is_open_ended = false;
    public $room_price = 0, $discount_type = 'fixed', $discount_value = 0, $total_price = 0;
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
            // Pre-fill with current registration pricing as baseline
            $this->discount_type = $reg->discount_type;
            $this->discount_value = $reg->discount_value;
            $this->duration_type = $reg->duration_type;
            $this->duration_value = $reg->duration_value;
            $this->is_open_ended = (bool) $reg->is_open_ended;
        } else {
            $this->selectedLocationId = null;
        }
        $this->new_room_id = null;
        $this->room_price = 0;
        $this->calculateTotalPrice();
    }

    public function updatedNewRoomId($value)
    {
        if ($value) {
            $room = Room::find($value);
            $this->setRoomPriceByDuration($room);
        } else {
            $this->room_price = 0;
        }
        $this->calculateTotalPrice();
    }

    public function updatedDurationType()
    {
        if ($this->new_room_id) {
            $room = Room::find($this->new_room_id);
            $this->setRoomPriceByDuration($room);
        }
        $this->calculateTotalPrice();
    }

    public function updatedDurationValue()
    {
        $this->calculateTotalPrice();
    }

    public function updatedIsOpenEnded($value)
    {
        if ($value) {
            $this->duration_value = 1;
        }
        $this->calculateTotalPrice();
    }

    private function setRoomPriceByDuration($room)
    {
        if (!$room) return;

        switch ($this->duration_type) {
            case 'daily':
                $this->room_price = $room->price_daily ?: $room->price_monthly;
                break;
            case 'weekly':
                $this->room_price = $room->price_weekly ?: $room->price_monthly;
                break;
            case 'yearly':
                $this->room_price = $room->price_yearly ?: $room->price_monthly;
                break;
            default:
                $this->room_price = $room->price_monthly;
                break;
        }
    }

    public function updatedDiscountType() { $this->calculateTotalPrice(); }
    public function updatedDiscountValue() { $this->calculateTotalPrice(); }
    public function updatedRoomPrice() { $this->calculateTotalPrice(); }

    public function calculateTotalPrice()
    {
        $price = (float) $this->room_price;
        $duration = (int) ($this->duration_value ?: 1);
        $subtotal = $price * $duration;
        $discount = (float) $this->discount_value;

        if ($this->discount_type === 'percent') {
            $this->total_price = $subtotal - ($subtotal * ($discount / 100));
        } else {
            $this->total_price = $subtotal - $discount;
        }

        if ($this->total_price < 0) $this->total_price = 0;
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
        $this->duration_type = 'monthly';
        $this->duration_value = 1;
        $this->is_open_ended = false;
        $this->room_price = 0;
        $this->discount_type = 'fixed';
        $this->discount_value = 0;
        $this->total_price = 0;
    }

    public function saveMove()
    {
        $this->validate([
            'registration_id' => 'required|exists:registrations,id',
            'new_room_id' => 'required|exists:rooms,id',
            'move_date' => 'required|date',
            'reason' => 'nullable|string',
            'room_price' => 'required|numeric|min:0',
            'discount_value' => 'required|numeric|min:0',
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

            // 2. Update Registration with new room and pricing
            $registration->update([
                'room_id' => $this->new_room_id,
                'duration_type' => $this->duration_type,
                'duration_value' => $this->duration_value,
                'is_open_ended' => $this->is_open_ended,
                'room_price' => $this->room_price,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'total_price' => $this->total_price,
            ]);

            // 3. Update Room Statuses
            Room::where('id', $oldRoomId)->update(['status' => 'available']);
            Room::where('id', $this->new_room_id)->update(['status' => 'occupied']);
        });

        $message = "Perpindahan kamar berhasil diproses.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
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
        if (strlen($this->tenant_search) >= 1) {
            $activeRegistrations = Registration::with('user', 'room', 'location')
                ->where('status', 'active')
                ->whereHas('user', function($q) {
                    $q->where('name', 'like', '%' . $this->tenant_search . '%');
                })
                ->get();
        }

        // Available rooms for the selected location
        $availableRooms = [];
        if ($this->selectedLocationId) {
            $availableRooms = Room::where('location_id', $this->selectedLocationId)
                ->where('status', 'available');

            // Exclude current room if a registration is selected
            if ($this->registration_id) {
                $currentReg = Registration::find($this->registration_id);
                if ($currentReg) {
                    $availableRooms->where('id', '!=', $currentReg->room_id);
                }
            }

            $availableRooms = $availableRooms->orderBy('room_number')->get();
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
