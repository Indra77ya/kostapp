<?php

namespace App\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryItem;
use Livewire\Component;
use Carbon\Carbon;

class ProfitLossManager extends Component
{
    public $periodType = 'monthly'; // monthly, yearly, custom
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
        // Revenues (Sisi Kiri pada Skontro)
        $revenueAccounts = ChartOfAccount::where('type', 'revenue')->orderBy('code')->get();
        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $items = JournalEntryItem::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    if ($this->dateStart) $q->whereDate('entry_date', '>=', $this->dateStart);
                    if ($this->dateEnd) $q->whereDate('entry_date', '<=', $this->dateEnd);
                })->get();

            $amount = $items->sum('credit') - $items->sum('debit');
            if ($amount != 0) {
                $revenues[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalRevenue += $amount;
            }
        }

        // Expenses (Sisi Kanan pada Skontro)
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->orderBy('code')->get();
        $expenses = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $account) {
            $items = JournalEntryItem::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    if ($this->dateStart) $q->whereDate('entry_date', '>=', $this->dateStart);
                    if ($this->dateEnd) $q->whereDate('entry_date', '<=', $this->dateEnd);
                })->get();

            $amount = $items->sum('debit') - $items->sum('credit');
            if ($amount != 0) {
                $expenses[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalExpense += $amount;
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        return view('livewire.profit-loss-manager', [
            'revenues' => $revenues,
            'totalRevenue' => $totalRevenue,
            'expenses' => $expenses,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
        ]);
    }
}
