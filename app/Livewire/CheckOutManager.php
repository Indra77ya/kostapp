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
use App\Helpers\BroadcastHelper;
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
    public $filterPaymentStatus = '';
    public $filterDurationType = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';
    public $sort = 'latest';

    // Form fields
    public $check_out_date;
    public $check_out_notes;
    public $registration_data;
    public $deposit_deduction = 0;
    public $deposit_refund = 0;
    public $deduction_notes = '';

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
            'deposit_deduction' => 'numeric|min:0',
            'deposit_refund' => 'numeric|min:0',
        ]);

        $regId = $this->registrationId;
        $name = $this->registration_data->user->name;

        $outDate = $this->check_out_date;
        $outNotes = $this->check_out_notes;
        $deduction = $this->deposit_deduction;
        $refund = $this->deposit_refund;
        $deductionNotes = $this->deduction_notes;

        DB::transaction(function () use ($regId, $outDate, $outNotes, $deduction, $refund, $deductionNotes) {
            $reg = Registration::find($regId);

            // 0. Handle Deposit Deductions and Refunds
            if ($deduction > 0) {
                \App\Models\Deposit::create([
                    'registration_id' => $regId,
                    'amount' => $deduction,
                    'type' => 'debit',
                    'description' => 'Potongan Deposit saat Check-out: ' . ($deductionNotes ?: 'Kerusakan/Denda'),
                    'transaction_date' => $outDate,
                ]);
            }

            if ($refund > 0) {
                \App\Models\Deposit::create([
                    'registration_id' => $regId,
                    'amount' => $refund,
                    'type' => 'debit',
                    'description' => 'Pengembalian (Refund) Deposit saat Check-out',
                    'transaction_date' => $outDate,
                ]);
            }

            // 1. Update Registration Status
            $reg->update([
                'status' => 'checked_out',
                'check_out_date' => $outDate,
                'check_out_notes' => $outNotes,
            ]);

            // 2. Revert Room Status
            Room::where('id', $reg->room_id)->update(['status' => 'available']);

            // 3. Record journal entry for Check Out deposit deduction & refund
            \App\Services\AccountingService::recordCheckOutJournal($reg, (float) $deduction, (float) $refund);
        });

        $message = "Penghuni {$name} berhasil check out.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        BroadcastHelper::safeBroadcast(new NotificationSent($message, $type));
        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingFilterPaymentStatus() { $this->resetPage(); }
    public function updatingFilterDurationType() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterLocation', 'filterPaymentStatus', 'filterDurationType', 'filterDateStart', 'filterDateEnd', 'sort']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Registration::select('registrations.*')
            ->with('user', 'location', 'room')
            ->withSum('bills as total_bill', 'amount')
            ->withSum(['payments as total_paid' => function($q) {
                $q->where('status', '!=', 'Menunggu Konfirmasi');
            }], 'amount')
            ->where('registrations.status', 'active');

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

        if ($this->filterDurationType) {
            $query->where('duration_type', $this->filterDurationType);
        }

        if ($this->filterDateStart) {
            $query->where('stay_start_date', '>=', $this->filterDateStart);
        }

        if ($this->filterDateEnd) {
            $query->where('stay_start_date', '<=', $this->filterDateEnd);
        }

        if ($this->filterPaymentStatus === 'lunas') {
            $query->whereRaw('(select COALESCE(sum(amount - paid_amount), 0) from bills where bills.registration_id = registrations.id and amount > paid_amount) = 0');
        } elseif ($this->filterPaymentStatus === 'tunggakan') {
            $query->whereRaw('(select COALESCE(sum(amount - paid_amount), 0) from bills where bills.registration_id = registrations.id and amount > paid_amount) > 0');
        }

        // Sorting
        switch ($this->sort) {
            case 'name_asc':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc');
                break;
            case 'name_desc':
                $query->join('users', 'registrations.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'desc');
                break;
            case 'oldest':
                $query->orderBy('stay_start_date', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('stay_start_date', 'desc');
                break;
        }

        return view('livewire.check-out-manager', [
            'registrations' => $query->paginate(10),
            'locations' => Location::all(),
        ]);
    }
}
