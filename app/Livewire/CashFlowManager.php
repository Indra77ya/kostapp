<?php

namespace App\Livewire;

use App\Models\JournalEntryItem;
use App\Models\ChartOfAccount;
use Livewire\Component;
use Carbon\Carbon;

class CashFlowManager extends Component
{
    public $periodType = 'monthly';
    public $month;
    public $year;
    public $dateStart;
    public $dateEnd;

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
        $this->dateStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedPeriodType() { $this->updateDates(); }
    public function updatedMonth() { $this->updateDates(); }
    public function updatedYear() { $this->updateDates(); }

    private function updateDates()
    {
        if ($this->periodType === 'monthly') {
            $dt = Carbon::createFromDate($this->year, $this->month, 1);
            $this->dateStart = $dt->startOfMonth()->format('Y-m-d');
            $this->dateEnd = $dt->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($this->periodType === 'yearly') {
            $dt = Carbon::createFromDate($this->year, 1, 1);
            $this->dateStart = $dt->startOfYear()->format('Y-m-d');
            $this->dateEnd = $dt->copy()->endOfYear()->format('Y-m-d');
        }
    }

    public function render()
    {
        $pmAccountIds = \App\Models\PaymentMethod::whereNotNull('chart_of_account_id')->pluck('chart_of_account_id')->toArray();
        $cashAccounts = ChartOfAccount::where('category', 'Kas & Setara Kas')
            ->orWhereIn('id', $pmAccountIds)
            ->pluck('id');

        // Initial Cash Balance before dateStart
        $initialBalance = 0;
        if ($this->dateStart && count($cashAccounts) > 0) {
            $prevItems = JournalEntryItem::whereIn('chart_of_account_id', $cashAccounts)
                ->whereHas('journalEntry', function($q) {
                    $q->whereDate('entry_date', '<', $this->dateStart);
                })->get();
            $initialBalance = $prevItems->sum('debit') - $prevItems->sum('credit');
        }

        // Cash Inflows (Debits to Cash accounts)
        $inflowItems = JournalEntryItem::with(['journalEntry'])
            ->whereIn('chart_of_account_id', $cashAccounts)
            ->where('debit', '>', 0)
            ->whereHas('journalEntry', function($q) {
                if ($this->dateStart) $q->whereDate('entry_date', '>=', $this->dateStart);
                if ($this->dateEnd) $q->whereDate('entry_date', '<=', $this->dateEnd);
            })->get();

        $totalInflow = $inflowItems->sum('debit');

        // Cash Outflows (Credits to Cash accounts)
        $outflowItems = JournalEntryItem::with(['journalEntry'])
            ->whereIn('chart_of_account_id', $cashAccounts)
            ->where('credit', '>', 0)
            ->whereHas('journalEntry', function($q) {
                if ($this->dateStart) $q->whereDate('entry_date', '>=', $this->dateStart);
                if ($this->dateEnd) $q->whereDate('entry_date', '<=', $this->dateEnd);
            })->get();

        $totalOutflow = $outflowItems->sum('credit');

        $netCashFlow = $totalInflow - $totalOutflow;
        $endingBalance = $initialBalance + $netCashFlow;

        return view('livewire.cash-flow-manager', [
            'initialBalance' => $initialBalance,
            'inflowItems' => $inflowItems,
            'totalInflow' => $totalInflow,
            'outflowItems' => $outflowItems,
            'totalOutflow' => $totalOutflow,
            'netCashFlow' => $netCashFlow,
            'endingBalance' => $endingBalance,
        ]);
    }
}
