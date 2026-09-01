<?php

namespace App\Livewire;

use App\Models\Bill;
use App\Models\Expense;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Room;
use Carbon\Carbon;
use Livewire\Component;

class AnalyticsManager extends Component
{
    public $location_id = 'all';
    public $period_type = 'monthly'; // 'monthly', 'yearly', 'all'
    public $month;
    public $year;

    public function mount()
    {
        $this->month = (int) Carbon::now()->month;
        $this->year = (int) Carbon::now()->year;
    }

    public function render()
    {
        $locations = Location::all();

        // 1. Filter Rooms
        $roomsQuery = Room::query();
        if ($this->location_id !== 'all' && !empty($this->location_id)) {
            $roomsQuery->where('location_id', $this->location_id);
        }
        $totalRooms = (clone $roomsQuery)->count();
        $occupiedRooms = (clone $roomsQuery)->where('status', 'occupied')->count();
        $availableRooms = (clone $roomsQuery)->where('status', 'available')->count();
        $maintenanceRooms = (clone $roomsQuery)->where('status', 'maintenance')->count();

        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // 2. Filter Payments (Realisasi Pendapatan)
        $paymentsQuery = Payment::where('status', 'diterima');
        if ($this->location_id !== 'all' && !empty($this->location_id)) {
            $paymentsQuery->whereHas('registration', function ($q) {
                $q->whereHas('room', function ($r) {
                    $r->where('location_id', $this->location_id);
                });
            });
        }

        if ($this->period_type === 'monthly') {
            $paymentsQuery->whereYear('payment_date', $this->year)
                          ->whereMonth('payment_date', $this->month);
        } elseif ($this->period_type === 'yearly') {
            $paymentsQuery->whereYear('payment_date', $this->year);
        }

        $revenueRealized = $paymentsQuery->sum('amount');

        // 3. Filter Bills (Tagihan & Outstanding)
        $billsQuery = Bill::query();
        if ($this->location_id !== 'all' && !empty($this->location_id)) {
            $billsQuery->whereHas('registration', function ($q) {
                $q->whereHas('room', function ($r) {
                    $r->where('location_id', $this->location_id);
                });
            });
        }

        if ($this->period_type === 'monthly') {
            $billsQuery->whereYear('due_date', $this->year)
                       ->whereMonth('due_date', $this->month);
        } elseif ($this->period_type === 'yearly') {
            $billsQuery->whereYear('due_date', $this->year);
        }

        $totalBillsAmount = (clone $billsQuery)->sum('amount');
        $totalBillsPaidAmount = (clone $billsQuery)->sum('paid_amount');

        // Outstanding Bills (unpaid or partially paid)
        $outstandingBillsQuery = Bill::with(['registration.user', 'registration.room.location'])
            ->whereRaw('paid_amount < amount');

        if ($this->location_id !== 'all' && !empty($this->location_id)) {
            $outstandingBillsQuery->whereHas('registration', function ($q) {
                $q->whereHas('room', function ($r) {
                    $r->where('location_id', $this->location_id);
                });
            });
        }

        if ($this->period_type === 'monthly') {
            $outstandingBillsQuery->whereYear('due_date', $this->year)
                                  ->whereMonth('due_date', $this->month);
        } elseif ($this->period_type === 'yearly') {
            $outstandingBillsQuery->whereYear('due_date', $this->year);
        }

        $outstandingBillsList = $outstandingBillsQuery->orderBy('due_date', 'asc')->get();
        $totalOutstanding = $outstandingBillsList->sum(fn($b) => $b->remaining_amount);

        // 4. Expenses (Pengeluaran Operasional)
        $expensesQuery = Expense::query();
        if ($this->location_id !== 'all' && !empty($this->location_id)) {
            $expensesQuery->where('location_id', $this->location_id);
        }

        if ($this->period_type === 'monthly') {
            $expensesQuery->whereYear('expense_date', $this->year)
                          ->whereMonth('expense_date', $this->month);
        } elseif ($this->period_type === 'yearly') {
            $expensesQuery->whereYear('expense_date', $this->year);
        }

        $totalExpenses = $expensesQuery->sum('amount');
        $netOperatingIncome = $revenueRealized - $totalExpenses;

        // 5. Breakdown Per Location
        $locationBreakdown = $locations->map(function ($loc) {
            $locTotalRooms = Room::where('location_id', $loc->id)->count();
            $locOccupiedRooms = Room::where('location_id', $loc->id)->where('status', 'occupied')->count();
            $locOccupancyRate = $locTotalRooms > 0 ? round(($locOccupiedRooms / $locTotalRooms) * 100, 1) : 0;

            // Monthly Revenue Realized for this location
            $locPaymentQuery = Payment::where('status', 'diterima')
                ->whereHas('registration', function ($q) use ($loc) {
                    $q->whereHas('room', function ($r) use ($loc) {
                        $r->where('location_id', $loc->id);
                    });
                });

            if ($this->period_type === 'monthly') {
                $locPaymentQuery->whereYear('payment_date', $this->year)->whereMonth('payment_date', $this->month);
            } elseif ($this->period_type === 'yearly') {
                $locPaymentQuery->whereYear('payment_date', $this->year);
            }

            $locRevenue = $locPaymentQuery->sum('amount');

            // Outstanding for this location
            $locBillQuery = Bill::whereRaw('paid_amount < amount')
                ->whereHas('registration', function ($q) use ($loc) {
                    $q->whereHas('room', function ($r) use ($loc) {
                        $r->where('location_id', $loc->id);
                    });
                });

            if ($this->period_type === 'monthly') {
                $locBillQuery->whereYear('due_date', $this->year)->whereMonth('due_date', $this->month);
            } elseif ($this->period_type === 'yearly') {
                $locBillQuery->whereYear('due_date', $this->year);
            }

            $locOutstanding = $locBillQuery->get()->sum(fn($b) => $b->remaining_amount);

            return [
                'location' => $loc,
                'total_rooms' => $locTotalRooms,
                'occupied_rooms' => $locOccupiedRooms,
                'occupancy_rate' => $locOccupancyRate,
                'revenue' => $locRevenue,
                'outstanding' => $locOutstanding,
            ];
        });

        // 6. Monthly Trend (For current year or past 6 months)
        $monthlyTrend = [];
        for ($m = 1; $m <= 12; $m++) {
            $dt = Carbon::createFromDate($this->year, $m, 1);
            $monthName = $dt->translatedFormat('M');

            $mRevQuery = Payment::where('status', 'diterima')
                ->whereYear('payment_date', $this->year)
                ->whereMonth('payment_date', $m);

            if ($this->location_id !== 'all' && !empty($this->location_id)) {
                $mRevQuery->whereHas('registration', function ($q) {
                    $q->whereHas('room', function ($r) {
                        $r->where('location_id', $this->location_id);
                    });
                });
            }

            $mRev = $mRevQuery->sum('amount');

            $mExpQuery = Expense::whereYear('expense_date', $this->year)->whereMonth('expense_date', $m);
            if ($this->location_id !== 'all' && !empty($this->location_id)) {
                $mExpQuery->where('location_id', $this->location_id);
            }
            $mExp = $mExpQuery->sum('amount');

            $monthlyTrend[] = [
                'month' => $monthName,
                'month_num' => $m,
                'revenue' => $mRev,
                'expense' => $mExp,
                'net' => $mRev - $mExp,
            ];
        }

        return view('livewire.analytics-manager', [
            'locations' => $locations,
            'totalRooms' => $totalRooms,
            'occupiedRooms' => $occupiedRooms,
            'availableRooms' => $availableRooms,
            'maintenanceRooms' => $maintenanceRooms,
            'occupancyRate' => $occupancyRate,
            'revenueRealized' => $revenueRealized,
            'totalBillsAmount' => $totalBillsAmount,
            'totalOutstanding' => $totalOutstanding,
            'outstandingBillsList' => $outstandingBillsList,
            'totalExpenses' => $totalExpenses,
            'netOperatingIncome' => $netOperatingIncome,
            'locationBreakdown' => $locationBreakdown,
            'monthlyTrend' => $monthlyTrend,
        ]);
    }
}
