<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use Livewire\Component;

class DashboardStats extends Component
{
    public $totalRooms;
    public $availableRooms;
    public $activeBookings;

    protected $listeners = ['echo:stats,DatabaseUpdated' => 'refreshStats'];

    public function mount()
    {
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $this->totalRooms = Room::count();
        $this->availableRooms = Room::where('status', 'available')->count();
        $this->activeBookings = Booking::where('status', 'confirmed')->count();
    }

    public function render()
    {
        return view('livewire/dashboard-stats');
    }
}
