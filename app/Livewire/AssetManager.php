<?php

namespace App\Livewire;

use App\Models\AccountMapping;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\ChartOfAccount;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\Room;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AssetManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Search and filters
    public $search = '';
    public $filterLocationId = '';
    public $filterCondition = '';
    public $filterStatus = '';
    public $filterCategory = '';

    // Modals
    public $isModalOpen = false;
    public $isDepreciationModalOpen = false;
    public $isDetailModalOpen = false;

    // Asset Form fields
    public $assetId = null;
    public $code = '';
    public $name = '';
    public $category = '';
    public $customCategory = '';
    public $location_id = null;
    public $room_id = null;
    public $purchase_date = '';
    public $purchase_cost = null;
    public $purchase_source_type = 'cash'; // cash, equity, existing
    public $payment_method_id = null;
    public $condition = 'Baik';
    public $status = 'Aktif';
    public $useful_life_months = null;
    public $salvage_value = 0;
    public $chart_of_account_id = null;
    public $accumulated_depreciation_account_id = null;
    public $depreciation_expense_account_id = null;
    public $notes = '';

    // Selected asset & Depreciation form fields
    public $selectedAssetId = null;
    public $selectedAsset = null;
    public $depreciation_period_date = '';
    public $depreciation_amount = 0;
    public $depreciation_notes = '';

    public function mount()
    {
        $this->purchase_date = Carbon::now()->format('Y-m-d');
        $this->depreciation_period_date = Carbon::now()->startOfMonth()->format('Y-m-d');
    }

    public function generateAssetCode()
    {
        $dateStr = Carbon::now()->format('Ymd');
        $prefix = "AST-{$dateStr}-";

        $lastAsset = Asset::where('code', 'like', "{$prefix}%")
            ->when($this->assetId, fn($q) => $q->where('id', '!=', $this->assetId))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastAsset) {
            $lastSeq = (int) substr($lastAsset->code, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        $this->code = $prefix . $nextSeq;
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->assetId = $id;
            $asset = Asset::findOrFail($id);
            $this->code = $asset->code;
            $this->name = $asset->name;
            $this->category = $asset->category;
            $this->location_id = $asset->location_id;
            $this->room_id = $asset->room_id;
            $this->purchase_date = $asset->purchase_date->format('Y-m-d');
            $this->purchase_cost = $asset->purchase_cost;
            $this->purchase_source_type = $asset->purchase_source_type ?? 'cash';
            $this->payment_method_id = $asset->payment_method_id;
            $this->condition = $asset->condition;
            $this->status = $asset->status;
            $this->useful_life_months = $asset->useful_life_months;
            $this->salvage_value = $asset->salvage_value;
            $this->chart_of_account_id = $asset->chart_of_account_id;
            $this->accumulated_depreciation_account_id = $asset->accumulated_depreciation_account_id;
            $this->depreciation_expense_account_id = $asset->depreciation_expense_account_id;
            $this->notes = $asset->notes;
        } else {
            $this->generateAssetCode();
            // Default account mappings fallback
            $this->chart_of_account_id = AccountMapping::getAccountId('default_asset_account')
                ?? AccountingService::getAccountByCode('1-7100')?->id;
            $this->accumulated_depreciation_account_id = AccountMapping::getAccountId('default_asset_accum_depr')
                ?? AccountingService::getAccountByCode('1-7900')?->id;
            $this->depreciation_expense_account_id = AccountMapping::getAccountId('default_asset_depr_expense')
                ?? AccountingService::getAccountByCode('5-7000')?->id;
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
        $this->assetId = null;
        $this->code = '';
        $this->name = '';
        $this->category = '';
        $this->customCategory = '';
        $this->location_id = null;
        $this->room_id = null;
        $this->purchase_date = Carbon::now()->format('Y-m-d');
        $this->purchase_cost = null;
        $this->purchase_source_type = 'cash';
        $this->payment_method_id = null;
        $this->condition = 'Baik';
        $this->status = 'Aktif';
        $this->useful_life_months = null;
        $this->salvage_value = 0;
        $this->chart_of_account_id = null;
        $this->accumulated_depreciation_account_id = null;
        $this->depreciation_expense_account_id = null;
        $this->notes = '';
    }

    public function updatedLocationId($value)
    {
        // Reset room_id when location changes
        $this->room_id = null;
    }

    public function save()
    {
        $finalCategory = ($this->category === 'NEW') ? trim($this->customCategory) : $this->category;

        $rules = [
            'code' => 'required|string|unique:assets,code' . ($this->assetId ? ',' . $this->assetId : ''),
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'customCategory' => $this->category === 'NEW' ? 'required|string|max:100' : 'nullable',
            'location_id' => 'nullable|exists:locations,id',
            'room_id' => 'nullable|exists:rooms,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'purchase_source_type' => 'required|in:cash,equity,existing',
            'payment_method_id' => $this->purchase_source_type === 'cash' ? 'nullable|exists:payment_methods,id' : 'nullable',
            'condition' => 'required|in:Baik,Perlu Perbaikan,Rusak',
            'status' => 'required|in:Aktif,Non-Aktif,Afkir',
            'useful_life_months' => 'nullable|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
            'chart_of_account_id' => 'nullable|exists:chart_of_accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:chart_of_accounts,id',
            'depreciation_expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'notes' => 'nullable|string',
        ];

        $this->validate($rules);

        DB::transaction(function () use ($finalCategory) {
            $data = [
                'code' => $this->code,
                'name' => $this->name,
                'category' => $finalCategory,
                'location_id' => $this->location_id ?: null,
                'room_id' => $this->room_id ?: null,
                'purchase_date' => $this->purchase_date,
                'purchase_cost' => $this->purchase_cost,
                'purchase_source_type' => $this->purchase_source_type,
                'payment_method_id' => $this->purchase_source_type === 'cash' ? ($this->payment_method_id ?: null) : null,
                'condition' => $this->condition,
                'status' => $this->status,
                'useful_life_months' => $this->useful_life_months ?: null,
                'salvage_value' => $this->salvage_value ?: 0,
                'chart_of_account_id' => $this->chart_of_account_id ?: null,
                'accumulated_depreciation_account_id' => $this->accumulated_depreciation_account_id ?: null,
                'depreciation_expense_account_id' => $this->depreciation_expense_account_id ?: null,
                'notes' => $this->notes,
            ];

            if ($this->assetId) {
                $asset = Asset::find($this->assetId);
                $asset->update($data);
            } else {
                $asset = Asset::create($data);
            }

            // Automatically record Asset Purchase journal entry if configured
            AccountingService::recordAssetPurchaseJournal($asset);
        });

        $this->dispatch('notify', message: 'Data aset berhasil disimpan.', type: 'success');
        $this->closeModal();
    }

    public function delete($id)
    {
        $asset = Asset::find($id);
        if ($asset) {
            $asset->delete();
            $this->dispatch('notify', message: 'Data aset berhasil dihapus.', type: 'success');
        }
    }

    public function openDetailModal($id)
    {
        $this->selectedAsset = Asset::with(['location', 'room', 'paymentMethod', 'purchaseJournalEntry', 'chartOfAccount', 'accumulatedDepreciationAccount', 'depreciationExpenseAccount', 'depreciations.journalEntry'])->findOrFail($id);
        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->selectedAsset = null;
    }

    public function openDepreciationModal($id)
    {
        $this->selectedAssetId = $id;
        $this->selectedAsset = Asset::findOrFail($id);

        if (!$this->selectedAsset->useful_life_months || $this->selectedAsset->useful_life_months <= 0) {
            $this->dispatch('notify', message: 'Aset ini belum memiliki masa manfaat (bulan) untuk dihitung penyusutannya.', type: 'error');
            return;
        }

        $this->depreciation_period_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->depreciation_amount = $this->selectedAsset->monthly_depreciation;
        $this->depreciation_notes = "Penyusutan bulanan " . Carbon::parse($this->depreciation_period_date)->format('F Y');
        $this->isDepreciationModalOpen = true;
    }

    public function closeDepreciationModal()
    {
        $this->isDepreciationModalOpen = false;
        $this->selectedAssetId = null;
        $this->selectedAsset = null;
    }

    public function processDepreciation()
    {
        $this->validate([
            'depreciation_period_date' => 'required|date',
            'depreciation_amount' => 'required|numeric|min:0.01',
            'depreciation_notes' => 'nullable|string',
        ]);

        if (!$this->selectedAsset) {
            return;
        }

        $periodDate = Carbon::parse($this->depreciation_period_date)->startOfMonth()->toDateString();

        // Check if depreciation already processed for this asset and period
        $existing = AssetDepreciation::where('asset_id', $this->selectedAsset->id)
            ->whereDate('period_date', $periodDate)
            ->first();

        if ($existing) {
            $this->dispatch('notify', message: 'Penyusutan untuk periode bulan ini sudah pernah diproses.', type: 'error');
            return;
        }

        DB::transaction(function () use ($periodDate) {
            $depreciation = AssetDepreciation::create([
                'asset_id' => $this->selectedAsset->id,
                'period_date' => $periodDate,
                'depreciation_amount' => $this->depreciation_amount,
                'notes' => $this->depreciation_notes,
            ]);

            // Create journal entry automatically
            AccountingService::recordAssetDepreciationJournal($depreciation);
        });

        $this->dispatch('notify', message: 'Penyusutan aset berhasil diproses dan dicatat ke Jurnal Umum.', type: 'success');
        $this->closeDepreciationModal();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterLocationId() { $this->resetPage(); }
    public function updatingFilterCondition() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterCategory() { $this->resetPage(); }

    public function render()
    {
        $query = Asset::with(['location', 'room', 'chartOfAccount', 'depreciations']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('category', 'like', '%' . $this->search . '%')
                    ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterLocationId) {
            $query->where('location_id', $this->filterLocationId);
        }

        if ($this->filterCondition) {
            $query->where('condition', $this->filterCondition);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(12);

        $selectableRooms = $this->location_id
            ? Room::where('location_id', $this->location_id)->orderBy('room_number')->get()
            : Room::orderBy('room_number')->get();

        $existingCategories = Asset::distinct()->whereNotNull('category')->pluck('category')->toArray();
        $defaultCategories = ['Elektronik', 'Meubel & Furnitur', 'Bangunan & Konstruksi', 'Perlengkapan', 'Kendaraan Operasional'];
        $categories = array_unique(array_merge($defaultCategories, $existingCategories));

        return view('livewire.asset-manager', [
            'assets' => $assets,
            'locations' => Location::orderBy('name')->get(),
            'rooms' => $selectableRooms,
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'categories' => $categories,
            'assetAccounts' => ChartOfAccount::where('type', 'asset')->where('is_active', true)->orderBy('code')->get(),
            'expenseAccounts' => ChartOfAccount::where('type', 'expense')->where('is_active', true)->orderBy('code')->get(),
        ]);
    }
}
