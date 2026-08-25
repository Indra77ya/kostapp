<?php

namespace App\Livewire;

use App\Models\ChartOfAccount;
use Livewire\Component;
use Livewire\WithPagination;

class ChartOfAccountManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterType = '';
    public $isModalOpen = false;
    public $accountId;

    public $code;
    public $name;
    public $type = 'expense';
    public $normal_balance = 'debit';
    public $category;
    public $description;
    public $is_active = true;

    public function updatedType($value)
    {
        if (in_array($value, ['asset', 'expense'])) {
            $this->normal_balance = 'debit';
        } else {
            $this->normal_balance = 'credit';
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->accountId = $id;
            $account = ChartOfAccount::find($id);
            $this->code = $account->code;
            $this->name = $account->name;
            $this->type = $account->type;
            $this->normal_balance = $account->normal_balance;
            $this->category = $account->category;
            $this->description = $account->description;
            $this->is_active = $account->is_active;
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
        $this->accountId = null;
        $this->code = '';
        $this->name = '';
        $this->type = 'expense';
        $this->normal_balance = 'debit';
        $this->category = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function seedDefaultAccounts()
    {
        $seeder = new \Database\Seeders\ChartOfAccountSeeder();
        $seeder->run();

        $this->dispatch('notify', message: 'Bagan akun standar berhasil dimuat.', type: 'success');
    }

    public function save()
    {
        $rules = [
            'code' => 'required|string|unique:chart_of_accounts,code' . ($this->accountId ? ',' . $this->accountId : ''),
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ];

        $this->validate($rules);

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'normal_balance' => $this->normal_balance,
            'category' => $this->category,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->accountId) {
            ChartOfAccount::find($this->accountId)->update($data);
        } else {
            ChartOfAccount::create($data);
        }

        $this->dispatch('notify', message: 'Bagan Akun berhasil disimpan.', type: 'success');
        $this->closeModal();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }

    public function render()
    {
        $query = ChartOfAccount::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        $accounts = $query->orderBy('code', 'asc')->paginate(15);

        return view('livewire.chart-of-account-manager', [
            'accounts' => $accounts,
        ]);
    }
}
