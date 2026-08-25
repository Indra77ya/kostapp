<?php

namespace App\Livewire;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryItem;
use Livewire\Component;
use Carbon\Carbon;

class TrialBalanceManager extends Component
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

    public function updatedPeriodType()
    {
        $this->updateDates();
    }

    public function updatedMonth()
    {
        $this->updateDates();
    }

    public function updatedYear()
    {
        $this->updateDates();
    }

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
        $accounts = ChartOfAccount::orderBy('code')->get();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $items = JournalEntryItem::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    if ($this->dateStart) {
                        $q->whereDate('entry_date', '>=', $this->dateStart);
                    }
                    if ($this->dateEnd) {
                        $q->whereDate('entry_date', '<=', $this->dateEnd);
                    }
                })->get();

            $sumDebit = $items->sum('debit');
            $sumCredit = $items->sum('credit');

            $netDebit = 0;
            $netCredit = 0;

            if ($account->normal_balance === 'debit') {
                $net = $sumDebit - $sumCredit;
                if ($net >= 0) {
                    $netDebit = $net;
                } else {
                    $netCredit = abs($net);
                }
            } else {
                $net = $sumCredit - $sumDebit;
                if ($net >= 0) {
                    $netCredit = $net;
                } else {
                    $netDebit = abs($net);
                }
            }

            if ($sumDebit > 0 || $sumCredit > 0 || $netDebit > 0 || $netCredit > 0) {
                $rows[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'normal_balance' => $account->normal_balance,
                    'mutation_debit' => $sumDebit,
                    'mutation_credit' => $sumCredit,
                    'ending_debit' => $netDebit,
                    'ending_credit' => $netCredit,
                ];

                $totalDebit += $netDebit;
                $totalCredit += $netCredit;
            }
        }

        return view('livewire.trial-balance-manager', [
            'rows' => $rows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'isBalanced' => abs($totalDebit - $totalCredit) < 0.01,
        ]);
    }
}
