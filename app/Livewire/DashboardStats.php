<?php

namespace App\Livewire;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Room;
use Carbon\Carbon;
use Livewire\Component;

class DashboardStats extends Component
{
    public $totalRooms;
    public $occupiedRooms;
    public $availableRooms;
    public $occupancyRate;
    public $activeTenants;
    public $monthlyRevenue;
    public $totalOutstanding;

    protected $listeners = ['echo:stats,DatabaseUpdated' => 'refreshStats'];

    public function mount()
    {
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $this->totalRooms = Room::count();
        $this->occupiedRooms = Room::where('status', 'occupied')->count();
        $this->availableRooms = Room::where('status', 'available')->count();
        $this->occupancyRate = $this->totalRooms > 0 ? round(($this->occupiedRooms / $this->totalRooms) * 100, 1) : 0;

        $this->activeTenants = Registration::where('status', 'active')->count();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Current month paid revenue
        $this->monthlyRevenue = Payment::where('status', 'diterima')
            ->whereYear('payment_date', $currentYear)
            ->whereMonth('payment_date', $currentMonth)
            ->sum('amount');

        // Total outstanding bills
        $this->totalOutstanding = Bill::whereRaw('paid_amount < amount')
            ->get()
            ->sum(fn($b) => $b->remaining_amount);
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
