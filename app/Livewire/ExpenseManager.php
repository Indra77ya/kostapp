<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\PaymentMethod;
use App\Services\AccountingService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExpenseManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterAccountId = '';
    public $filterDateStart = '';
    public $filterDateEnd = '';
    public $isModalOpen = false;
    public $expenseId;

    public $expense_number;
    public $expense_date;
    public $chart_of_account_id;
    public $payment_method_id;
    public $amount;
    public $title;
    public $notes;
    public $attachment;

    public function mount()
    {
        $this->expense_date = Carbon::now()->format('Y-m-d');
        $this->generateExpenseNumber();
    }

    public function generateExpenseNumber()
    {
        $dateStr = Carbon::now()->format('Ymd');
        $prefix = "EXP-{$dateStr}-";

        $lastExpense = Expense::where('expense_number', 'like', "{$prefix}%")
            ->when($this->expenseId, fn($q) => $q->where('id', '!=', $this->expenseId))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastExpense) {
            $lastSeq = (int) substr($lastExpense->expense_number, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        $this->expense_number = $prefix . $nextSeq;
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->expenseId = $id;
            $expense = Expense::find($id);
            $this->expense_number = $expense->expense_number;
            $this->expense_date = $expense->expense_date->format('Y-m-d');
            $this->chart_of_account_id = $expense->chart_of_account_id;
            $this->payment_method_id = $expense->payment_method_id;
            $this->amount = $expense->amount;
            $this->title = $expense->title;
            $this->notes = $expense->notes;
        } else {
            $this->generateExpenseNumber();
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->expenseId = null;
        $this->expense_number = '';
        $this->expense_date = Carbon::now()->format('Y-m-d');
        $this->chart_of_account_id = null;
        $this->payment_method_id = null;
        $this->amount = null;
        $this->title = '';
        $this->notes = '';
        $this->attachment = null;
        $this->generateExpenseNumber();
    }

    public function save()
    {
        $rules = [
            'expense_number' => 'required|string|unique:expenses,expense_number' . ($this->expenseId ? ',' . $this->expenseId : ''),
            'expense_date' => 'required|date',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'required|numeric|min:1',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:3072',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            $data = [
                'expense_number' => $this->expense_number,
                'expense_date' => $this->expense_date,
                'chart_of_account_id' => $this->chart_of_account_id,
                'payment_method_id' => $this->payment_method_id,
                'amount' => $this->amount,
                'title' => $this->title,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ];

            if ($this->expenseId) {
                $expense = Expense::find($this->expenseId);
                if ($this->attachment) {
                    if ($expense->attachment_path) {
                        Storage::disk('public')->delete($expense->attachment_path);
                    }
                    $data['attachment_path'] = $this->attachment->store('expenses', 'public');
                }
                $expense->update($data);
            } else {
                if ($this->attachment) {
                    $data['attachment_path'] = $this->attachment->store('expenses', 'public');
                }
                $expense = Expense::create($data);
            }

            // Post journal entry
            AccountingService::recordExpenseJournal($expense);
        });

        $this->dispatch('notify', message: 'Pengeluaran operasional berhasil disimpan.', type: 'success');
        $this->closeModal();
    }

    public function delete($id)
    {
        $expense = Expense::find($id);
        if ($expense) {
            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }
            $expense->delete();
            $this->dispatch('notify', message: 'Pengeluaran berhasil dihapus.', type: 'success');
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterAccountId() { $this->resetPage(); }
    public function updatingFilterDateStart() { $this->resetPage(); }
    public function updatingFilterDateEnd() { $this->resetPage(); }

    public function render()
    {
        $query = Expense::with(['account', 'paymentMethod', 'creator']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('expense_number', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterAccountId) {
            $query->where('chart_of_account_id', $this->filterAccountId);
        }

        if ($this->filterDateStart) {
            $query->whereDate('expense_date', '>=', $this->filterDateStart);
        }

        if ($this->filterDateEnd) {
            $query->whereDate('expense_date', '<=', $this->filterDateEnd);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(12);

        return view('livewire.expense-manager', [
            'expenses' => $expenses,
            'expenseAccounts' => ChartOfAccount::where('type', 'expense')->where('is_active', true)->orderBy('code')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
