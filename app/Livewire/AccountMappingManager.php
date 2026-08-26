<?php

namespace App\Livewire;

use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use Livewire\Component;

class AccountMappingManager extends Component
{
    public array $mappings = [];

    public function mount()
    {
        AccountMapping::seedDefaults();
        $this->loadMappings();
    }

    public function loadMappings()
    {
        $records = AccountMapping::all();
        $this->mappings = [];

        foreach ($records as $record) {
            $this->mappings[$record->key] = $record->chart_of_account_id;
        }
    }

    public function updateMapping($key, $chartOfAccountId)
    {
        $mapping = AccountMapping::where('key', $key)->first();
        if ($mapping) {
            $mapping->update([
                'chart_of_account_id' => $chartOfAccountId ?: null,
            ]);
            session()->flash('success', "Pemetaan untuk '{$mapping->name}' berhasil diperbarui.");
        }
        $this->loadMappings();
    }

    public function resetToDefaults()
    {
        $defaults = AccountMapping::defaultMappings();
        foreach ($defaults as $key => $config) {
            $coa = ChartOfAccount::where('code', $config['default_code'])->first();
            AccountMapping::where('key', $key)->update([
                'chart_of_account_id' => $coa?->id,
            ]);
        }
        $this->loadMappings();
        session()->flash('success', 'Semua pemetaan akun berhasil dikembalikan ke standar default.');
    }

    public function render()
    {
        $mappingRecords = AccountMapping::with('chartOfAccount')->get();
        $accounts = ChartOfAccount::orderBy('code')->get();

        return view('livewire.account-mapping-manager', [
            'mappingRecords' => $mappingRecords,
            'accounts' => $accounts,
        ]);
    }
}
