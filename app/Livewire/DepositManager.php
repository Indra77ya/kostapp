<?php

namespace App\Livewire;

use App\Models\Registration;
use App\Models\Deposit;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Carbon\Carbon;

class DepositManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $viewMode = 'list'; // 'list' or 'history'
    public $selectedRegistrationId;
    public $isModalOpen = false;

    // Search & Filters
    public $search = '';
    public $filterLocation = '';
    public $sort = 'name_asc';

    // Form fields
    public $amount, $type = 'credit', $description, $transaction_date;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    public function mount()
    {
        $this->transaction_date = Carbon::now()->format('Y-m-d');
    }

    public function selectRegistration($id)
    {
        $this->selectedRegistrationId = $id;
        $this->viewMode = 'history';
        $this->resetPage();
    }

    public function backToList()
    {
        $this->viewMode = 'list';
        $this->selectedRegistrationId = null;
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->amount = null;
        $this->type = 'credit';
        $this->description = '';
        $this->transaction_date = Carbon::now()->format('Y-m-d');
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function saveAdjustment()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:credit,debit',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
        ]);

        if ($this->type === 'debit') {
            $reg = Registration::find($this->selectedRegistrationId);
            if ($this->amount > $reg->deposit_balance) {
                $this->addError('amount', 'Saldo tidak mencukupi untuk penarikan/pemotongan ini.');
                return;
            }
        }

        Deposit::create([
            'registration_id' => $this->selectedRegistrationId,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description . ' (Penyesuaian Manual)',
            'transaction_date' => $this->transaction_date,
        ]);

        $this->dispatch('notify', message: 'Penyesuaian deposit berhasil disimpan.', type: 'success');
        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deleteDeposit($id)
    {
        $deposit = Deposit::find($id);
        if ($deposit->payment_id) {
            $this->dispatch('notify', message: 'Data deposit yang terkait dengan pembayaran tidak bisa dihapus dari sini. Hapus pembayarannya jika perlu.', type: 'warning');
            return;
        }

        $deposit->delete();
        $this->dispatch('notify', message: 'Data deposit berhasil dihapus.', type: 'success');
        DatabaseUpdated::dispatch();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocation() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }

    public function render()
    {
        if ($this->viewMode === 'history') {
            $registration = Registration::with('user', 'room', 'location')->find($this->selectedRegistrationId);
            $history = Deposit::where('registration_id', $this->selectedRegistrationId)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('livewire.deposit-manager', [
                'registration' => $registration,
                'history' => $history,
            ]);
        }

        $query = Registration::with('user', 'room', 'location')
            ->where('status', 'active');

        if ($this->search) {
            $query->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterLocation) {
            $query->where('location_id', $this->filterLocation);
        }

        // Apply sorting
        if ($this->sort === 'name_asc') {
            $query->join('users', 'registrations.user_id', '=', 'users.id')
                  ->orderBy('users.name', 'asc')
                  ->select('registrations.*');
        } elseif ($this->sort === 'name_desc') {
            $query->join('users', 'registrations.user_id', '=', 'users.id')
                  ->orderBy('users.name', 'desc')
                  ->select('registrations.*');
        }

        $registrations = $query->paginate(10);

        return view('livewire.deposit-manager', [
            'registrations' => $registrations,
            'locations' => Location::all(),
        ]);
    }
}
