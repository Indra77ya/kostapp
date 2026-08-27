<?php

namespace App\Livewire;

use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use App\Models\FundTransfer;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FundTransferManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $startDate = '';
    public $endDate = '';

    public $isModalOpen = false;

    // Form fields
    public $transfer_date;
    public $from_account_id;
    public $to_account_id;
    public $amount;
    public $admin_fee = 0;
    public $admin_fee_account_id;
    public $notes;

    public function mount()
    {
        $this->transfer_date = now()->format('Y-m-d');
        $this->setDefaultAdminFeeAccount();
    }

    public function setDefaultAdminFeeAccount()
    {
        $defaultAdminFeeId = AccountMapping::getAccountId('bank_admin_fee');
        if (!$defaultAdminFeeId) {
            $defaultCoa = ChartOfAccount::where('code', '5-6100')->first();
            $defaultAdminFeeId = $defaultCoa?->id;
        }
        $this->admin_fee_account_id = $defaultAdminFeeId;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }

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

    public function resetForm()
    {
        $this->transfer_date = now()->format('Y-m-d');
        $this->from_account_id = null;
        $this->to_account_id = null;
        $this->amount = null;
        $this->admin_fee = 0;
        $this->notes = null;
        $this->setDefaultAdminFeeAccount();
    }

    public function save()
    {
        $rules = [
            'transfer_date' => 'required|date',
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'to_account_id' => 'required|exists:chart_of_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
            'admin_fee' => 'nullable|numeric|min:0',
            'admin_fee_account_id' => 'nullable|required_if:admin_fee,>0|exists:chart_of_accounts,id',
            'notes' => 'nullable|string|max:500',
        ];

        $messages = [
            'to_account_id.different' => 'Akun tujuan transfer harus berbeda dengan akun asal.',
            'admin_fee_account_id.required_if' => 'Akun beban admin harus dipilih jika terdapat biaya admin.',
        ];

        $this->validate($rules, $messages);

        $transfer = FundTransfer::create([
            'transfer_number' => FundTransfer::generateTransferNumber($this->transfer_date),
            'transfer_date' => $this->transfer_date,
            'from_account_id' => $this->from_account_id,
            'to_account_id' => $this->to_account_id,
            'amount' => $this->amount,
            'admin_fee' => $this->admin_fee ?: 0,
            'admin_fee_account_id' => ($this->admin_fee > 0) ? $this->admin_fee_account_id : null,
            'notes' => $this->notes,
            'created_by' => Auth::id(),
        ]);

        // Post auto double-entry journal
        AccountingService::recordTransferJournal($transfer);

        $this->dispatch('notify', message: "Transfer dana {$transfer->transfer_number} berhasil dicatat.", type: 'success');
        $this->closeModal();
    }

    public function render()
    {
        $query = FundTransfer::with(['fromAccount', 'toAccount', 'adminFeeAccount', 'creator']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transfer_number', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('fromAccount', function ($fa) {
                      $fa->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('code', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('toAccount', function ($ta) {
                      $ta->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('code', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->startDate) {
            $query->whereDate('transfer_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('transfer_date', '<=', $this->endDate);
        }

        $transfers = $query->orderBy('transfer_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Assets / Cash & Bank accounts for source/destination
        $cashBankAccounts = ChartOfAccount::where('type', 'asset')
            ->where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        // Expense accounts for admin fee
        $expenseAccounts = ChartOfAccount::where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        return view('livewire.fund-transfer-manager', [
            'transfers' => $transfers,
            'cashBankAccounts' => $cashBankAccounts,
            'expenseAccounts' => $expenseAccounts,
        ]);
    }
}
