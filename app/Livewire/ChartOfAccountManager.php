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
    public $filterStatus = 'active';
    public $viewType = 'table'; // 'table' or 'tree'
    public $isModalOpen = false;
    public $accountId;

    public function setView($type)
    {
        if (in_array($type, ['table', 'tree'])) {
            $this->viewType = $type;
        }
    }

    public $code;
    public $name;
    public $type = 'expense';
    public $sub_type = 'Beban Operasional';
    public $parent_id = null;
    public $normal_balance = 'debit';
    public $category;
    public $custom_category = '';
    public $description;
    public $is_active = true;

    public function getExistingCategoriesProperty()
    {
        return ChartOfAccount::where('type', $this->type)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values()
            ->all();
    }

    public function getAvailableSubTypesProperty()
    {
        return match ($this->type) {
            'asset' => [
                'Kas & Bank',
                'Piutang Usaha',
                'Perlengkapan & Persediaan',
                'Biaya Dibayar Di Muka',
                'Aset Tetap',
                'Akumulasi Penyusutan',
                'Aset Lainnya',
            ],
            'liability' => [
                'Liabilitas Jangka Pendek',
                'Utang Usaha',
                'Utang Pajak & Retribusi',
                'Beban Yang Masih Harus Dibayar',
                'Liabilitas Jangka Panjang',
            ],
            'equity' => [
                'Ekuitas Pemilik',
                'Modal Disetor',
                'Prive / Pengambilan Pemilik',
                'Laba Ditahan',
                'Laba Tahun Berjalan',
            ],
            'revenue' => [
                'Pendapatan Utama / Sewa',
                'Pendapatan Layanan / Service',
                'Pendapatan Denda & Administrasi',
                'Pendapatan Non-Operasional / Lain-lain',
            ],
            'expense' => [
                'Beban Operasional',
                'Beban Utilitas',
                'Beban Pemeliharaan & Perbaikan',
                'Beban Kebersihan & Keamanan',
                'Beban Gaji & Honor',
                'Beban Pemasaran & Promosi',
                'Beban Administrasi & Umum',
                'Beban Penyusutan',
                'Beban Non-Operasional',
            ],
            default => [],
        };
    }

    public function updatedType($value)
    {
        if (in_array($value, ['asset', 'expense'])) {
            $this->normal_balance = 'debit';
        } else {
            $this->normal_balance = 'credit';
        }

        $subTypes = $this->availableSubTypes;
        if (!in_array($this->sub_type, $subTypes)) {
            $this->sub_type = $subTypes[0] ?? null;
        }

        if ($this->parent_id) {
            $parent = ChartOfAccount::find($this->parent_id);
            if ($parent && $parent->type !== $value) {
                $this->parent_id = null;
            }
        }

        if ($this->category !== '__new__' && !in_array($this->category, $this->existingCategories)) {
            $this->category = '';
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
            $this->sub_type = $account->sub_type;
            $this->parent_id = $account->parent_id;
            $this->normal_balance = $account->normal_balance;
            $this->category = $account->category;
            $this->custom_category = '';
            $this->description = $account->description;
            $this->is_active = $account->is_active;
        } else {
            $this->sub_type = $this->availableSubTypes[0] ?? null;
            $this->category = '';
            $this->custom_category = '';
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
        $this->sub_type = 'Beban Operasional';
        $this->parent_id = null;
        $this->normal_balance = 'debit';
        $this->category = '';
        $this->custom_category = '';
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
            'sub_type' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'normal_balance' => 'required|in:debit,credit',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ];

        if ($this->accountId && $this->parent_id == $this->accountId) {
            $this->addError('parent_id', 'Akun tidak bisa menjadi induk bagi dirinya sendiri.');
            return;
        }

        if ($this->category === '__new__') {
            $this->validate([
                'custom_category' => 'required|string|max:255',
            ], [], [
                'custom_category' => 'Kategori Baru',
            ]);
            $finalCategory = trim($this->custom_category);
        } else {
            $finalCategory = $this->category;
        }

        $this->validate($rules);

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'sub_type' => $this->sub_type ?: null,
            'parent_id' => $this->parent_id ?: null,
            'normal_balance' => $this->normal_balance,
            'category' => $finalCategory ?: null,
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
    public function updatingFilterStatus() { $this->resetPage(); }

    public function toggleStatus($id)
    {
        $account = ChartOfAccount::findOrFail($id);

        if ($account->is_active) {
            // Check dependencies before deactivating
            if ($account->journalEntryItems()->exists()) {
                $this->dispatch('notify', message: 'Akun tidak dapat dinonaktifkan karena telah memiliki riwayat jurnal transaksi.', type: 'error');
                return;
            }

            if ($account->expenses()->exists()) {
                $this->dispatch('notify', message: 'Akun tidak dapat dinonaktifkan karena terhubung dengan data pengeluaran operasional.', type: 'error');
                return;
            }

            if (\App\Models\PaymentMethod::where('chart_of_account_id', $account->id)->exists()) {
                $this->dispatch('notify', message: 'Akun tidak dapat dinonaktifkan karena terhubung dengan metode pembayaran.', type: 'error');
                return;
            }

            if (\App\Models\AccountMapping::where('chart_of_account_id', $account->id)->exists()) {
                $this->dispatch('notify', message: 'Akun tidak dapat dinonaktifkan karena terhubung dengan pemetaan akun sistem.', type: 'error');
                return;
            }

            if ($account->children()->where('is_active', true)->exists()) {
                $this->dispatch('notify', message: 'Akun tidak dapat dinonaktifkan karena memiliki sub-akun/akun anak yang masih aktif.', type: 'error');
                return;
            }
        }

        $account->update(['is_active' => !$account->is_active]);

        $statusText = $account->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->dispatch('notify', message: "Akun {$account->name} ({$account->code}) berhasil {$statusText}.", type: 'success');
    }

    public function render()
    {
        $query = ChartOfAccount::query()->with(['parent']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sub_type', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        $query->withSum('journalEntryItems as total_debit', 'debit')
              ->withSum('journalEntryItems as total_credit', 'credit');

        // Fetch candidate parent accounts for modal dropdown (matching selected type, excluding self)
        $parentAccounts = ChartOfAccount::where('type', $this->type)
            ->when($this->accountId, function($q) {
                $q->where('id', '!=', $this->accountId);
            })
            ->orderBy('code', 'asc')
            ->get();

        if ($this->viewType === 'tree') {
            $allAccounts = (clone $query)->orderBy('code', 'asc')->get();

            foreach ($allAccounts as $acc) {
                $debit = $acc->total_debit ?? 0;
                $credit = $acc->total_credit ?? 0;
                $acc->current_balance = $acc->normal_balance === 'debit'
                    ? ($debit - $credit)
                    : ($credit - $debit);
            }

            $groupedAccounts = [];

            $typeLabels = [
                'asset' => 'Aset',
                'liability' => 'Liabilitas',
                'equity' => 'Ekuitas',
                'revenue' => 'Pendapatan',
                'expense' => 'Beban',
            ];

            foreach ($allAccounts as $acc) {
                $typeName = $typeLabels[$acc->type] ?? ucfirst($acc->type);
                $subTypeName = $acc->sub_type ?: ($acc->category ?: 'Lain-lain');

                if (!isset($groupedAccounts[$typeName])) {
                    $groupedAccounts[$typeName] = [];
                }
                if (!isset($groupedAccounts[$typeName][$subTypeName])) {
                    $groupedAccounts[$typeName][$subTypeName] = [];
                }
                $groupedAccounts[$typeName][$subTypeName][] = $acc;
            }

            return view('livewire.chart-of-account-manager', [
                'accounts' => $query->orderBy('code', 'asc')->paginate(15),
                'groupedAccounts' => $groupedAccounts,
                'parentAccounts' => $parentAccounts,
            ]);
        }

        $accounts = $query->orderBy('code', 'asc')->paginate(15);

        foreach ($accounts as $acc) {
            $debit = $acc->total_debit ?? 0;
            $credit = $acc->total_credit ?? 0;
            $acc->current_balance = $acc->normal_balance === 'debit'
                ? ($debit - $credit)
                : ($credit - $debit);
        }

        return view('livewire.chart-of-account-manager', [
            'accounts' => $accounts,
            'groupedAccounts' => [],
            'parentAccounts' => $parentAccounts,
        ]);
    }
}
