<?php

namespace App\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryItem;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class GeneralLedgerManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $selectedAccountId = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';

    public function mount()
    {
        $this->filterDateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->filterDateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

        $firstAccount = ChartOfAccount::orderBy('code')->first();
        if ($firstAccount) {
            $this->selectedAccountId = $firstAccount->id;
        }
    }

    public function updatingSelectedAccountId() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }

    public function render()
    {
        $accounts = ChartOfAccount::orderBy('code')->get();
        $account = $this->selectedAccountId ? ChartOfAccount::find($this->selectedAccountId) : null;

        $openingBalance = 0;
        $items = collect();

        if ($account) {
            // Calculate opening balance before filterDateStart
            if ($this->filterDateStart) {
                $prevItems = JournalEntryItem::where('chart_of_account_id', $account->id)
                    ->whereHas('journalEntry', function($q) {
                        $q->whereDate('entry_date', '<', $this->filterDateStart);
                    })->get();

                $sumDebit = $prevItems->sum('debit');
                $sumCredit = $prevItems->sum('credit');

                if ($account->normal_balance === 'debit') {
                    $openingBalance = $sumDebit - $sumCredit;
                } else {
                    $openingBalance = $sumCredit - $sumDebit;
                }
            }

            $query = JournalEntryItem::with('journalEntry')
                ->where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    if ($this->filterDateStart) {
                        $q->whereDate('entry_date', '>=', $this->filterDateStart);
                    }
                    if ($this->filterDateEnd) {
                        $q->whereDate('entry_date', '<=', $this->filterDateEnd);
                    }
                });

            $items = $query->join('journal_entries', 'journal_entry_items.journal_entry_id', '=', 'journal_entries.id')
                ->orderBy('journal_entries.entry_date', 'asc')
                ->orderBy('journal_entries.id', 'asc')
                ->select('journal_entry_items.*')
                ->paginate(20);
        }

        return view('livewire.general-ledger-manager', [
            'accounts' => $accounts,
            'account' => $account,
            'items' => $items,
            'openingBalance' => $openingBalance,
        ]);
    }
}
