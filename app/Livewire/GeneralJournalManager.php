<?php

namespace App\Livewire;

use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Services\AccountingService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GeneralJournalManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';

    // Manual journal modal
    public $isModalOpen = false;
    public $entry_date;
    public $description;
    public $items = [];

    public function mount()
    {
        $this->entry_date = Carbon::now()->format('Y-m-d');
        $this->resetJournalItems();
    }

    public function resetJournalItems()
    {
        $this->items = [
            ['chart_of_account_id' => '', 'debit' => 0, 'credit' => 0, 'memo' => ''],
            ['chart_of_account_id' => '', 'debit' => 0, 'credit' => 0, 'memo' => ''],
        ];
    }

    public function addItem()
    {
        $this->items[] = ['chart_of_account_id' => '', 'debit' => 0, 'credit' => 0, 'memo' => ''];
    }

    public function removeItem($index)
    {
        if (count($this->items) > 2) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->entry_date = Carbon::now()->format('Y-m-d');
        $this->description = '';
        $this->resetJournalItems();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function saveJournal()
    {
        $this->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'items.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.debit' => 'numeric|min:0',
            'items.*.credit' => 'numeric|min:0',
        ]);

        $totalDebit = collect($this->items)->sum('debit');
        $totalCredit = collect($this->items)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01 || $totalDebit <= 0) {
            $this->addError('general', 'Total Debit dan Total Kredit harus seimbang (balance) dan lebih dari 0.');
            return;
        }

        AccountingService::createJournalEntry([
            'entry_date' => $this->entry_date,
            'description' => $this->description,
            'created_by' => Auth::id(),
            'items' => $this->items,
        ]);

        $this->dispatch('notify', message: 'Jurnal Umum manual berhasil disimpan.', type: 'success');
        $this->closeModal();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }

    public function render()
    {
        $query = JournalEntry::with(['items.chartOfAccount', 'creator']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('entry_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('items.chartOfAccount', function($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterDateStart) {
            $query->whereDate('entry_date', '>=', $this->filterDateStart);
        }

        if ($this->filterDateEnd) {
            $query->whereDate('entry_date', '<=', $this->filterDateEnd);
        }

        $entries = $query->orderBy('entry_date', 'desc')->orderBy('id', 'desc')->paginate(10);

        return view('livewire.general-journal-manager', [
            'entries' => $entries,
            'accounts' => ChartOfAccount::where('is_active', true)->orderBy('code')->get(),
        ]);
    }
}
