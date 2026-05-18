<?php

namespace App\Livewire;

use App\Models\Registration;
use App\Models\Room;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Carbon\Carbon;

class CheckOutManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $isModalOpen = false;
    public $registrationId;

    // Search & Filters
    public $search = '';
    public $filterLocation = '';

    // Form fields
    public $check_out_date;
    public $check_out_notes;
    public $registration_data;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    public function mount()
    {
        $this->check_out_date = Carbon::now()->format('Y-m-d');
    }

    public function openModal($id)
    {
        $this->resetValidation();
        $this->registrationId = $id;
        $this->registration_data = Registration::with('user', 'room', 'location')->find($id);
        $this->check_out_date = Carbon::now()->format('Y-m-d');
        $this->check_out_notes = '';
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->registrationId = null;
        $this->registration_data = null;
    }

    public function processCheckOut()
    {
        $this->validate([
            'check_out_date' => 'required|date',
            'check_out_notes' => 'nullable|string',
        ]);

        $regId = $this->registrationId;
        $name = $this->registration_data->user->name;

        $outDate = $this->check_out_date;
        $outNotes = $this->check_out_notes;

        DB::transaction(function () use ($regId, $outDate, $outNotes) {
            $reg = Registration::find($regId);

            // 1. Update Registration Status
            $reg->update([
                'status' => 'checked_out',
                'check_out_date' => $outDate,
                'check_out_notes' => $outNotes,
            ]);

            // 2. Revert Room Status
            Room::where('id', $reg->room_id)->update(['status' => 'available']);
        });

        $message = "Penghuni {$name} berhasil check out.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocation']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Registration::with('user', 'location', 'room')
            ->where('status', 'active');

        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('user', function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('registration_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterLocation) {
            $query->where('location_id', $this->filterLocation);
        }

        return view('livewire.check-out-manager', [
            'registrations' => $query->latest()->paginate(10),
            'locations' => Location::all(),
        ]);
    }
}
