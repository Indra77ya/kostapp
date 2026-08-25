<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use App\Models\ChartOfAccount;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Events\NotificationSent;

class PaymentMethodManager extends Component
{
    use WithPagination, WithFileUploads, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';
    public $viewType = 'grid'; // 'grid' or 'table'
    public $isModalOpen = false;
    public $paymentMethodId;

    // Search and Filters
    public $search = '';
    public $filterCategory = '';
    public $filterStatus = '';

    // Form fields
    public $name, $category, $chart_of_account_id, $account_number, $account_name, $instructions, $logo, $is_active = true;
    public $oldLogo;

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'category' => 'required',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'account_number' => 'nullable',
            'account_name' => 'nullable',
            'instructions' => 'nullable',
            'logo' => 'nullable|image|max:1024', // 1MB Max
            'is_active' => 'boolean',
        ];
    }

    public function mount()
    {
        $this->authorize('access-master-data');
    }

    public function setView($type)
    {
        $this->setViewType($type);
    }

    private function setViewType($type)
    {
        $this->viewType = $type;
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->paymentMethodId = $id;
            $paymentMethod = PaymentMethod::findOrFail($id);
            $this->name = $paymentMethod->name;
            $this->category = $paymentMethod->category;
            $this->chart_of_account_id = $paymentMethod->chart_of_account_id;
            $this->account_number = $paymentMethod->account_number;
            $this->account_name = $paymentMethod->account_name;
            $this->instructions = $paymentMethod->instructions;
            $this->oldLogo = $paymentMethod->logo;
            $this->is_active = $paymentMethod->is_active;
        }

        $this->isModalOpen = true;
        $this->dispatch('isModalOpenChanged');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->paymentMethodId = null;
        $this->name = '';
        $this->category = '';
        $this->chart_of_account_id = null;
        $this->account_number = '';
        $this->account_name = '';
        $this->instructions = '';
        $this->logo = null;
        $this->oldLogo = null;
        $this->is_active = true;
    }

    public function savePaymentMethod()
    {
        $this->authorize('access-master-data');
        $this->validate();

        $data = [
            'name' => $this->name,
            'category' => $this->category,
            'chart_of_account_id' => $this->chart_of_account_id,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'instructions' => $this->instructions,
            'is_active' => $this->is_active,
        ];

        if ($this->logo) {
            // Delete old logo if exists
            if ($this->paymentMethodId) {
                $oldPm = PaymentMethod::find($this->paymentMethodId);
                if ($oldPm && $oldPm->logo) {
                    Storage::disk('public')->delete($oldPm->logo);
                }
            }
            $data['logo'] = $this->logo->store('payment_methods', 'public');
        }

        if ($this->paymentMethodId) {
            $paymentMethod = PaymentMethod::findOrFail($this->paymentMethodId);
            $paymentMethod->update($data);
            $message = "Metode pembayaran {$paymentMethod->name} telah diperbarui.";
            $type = 'info';
        } else {
            $paymentMethod = PaymentMethod::create($data);
            $message = "Metode pembayaran baru {$paymentMethod->name} telah ditambahkan.";
            $type = 'success';
        }

        $this->dispatch('notify', message: $message, type: $type, hideInBell: true);
        broadcast(new NotificationSent($message, $type, hideInBell: true))->toOthers();

        $this->closeModal();
    }

    public function deletePaymentMethod($id)
    {
        $this->authorize('access-master-data');
        $paymentMethod = PaymentMethod::findOrFail($id);
        if ($paymentMethod->logo) {
            Storage::disk('public')->delete($paymentMethod->logo);
        }
        $name = $paymentMethod->name;
        $paymentMethod->delete();

        $message = "Metode pembayaran {$name} telah dihapus.";
        $type = 'warning';
        $this->dispatch('notify', message: $message, type: $type, hideInBell: true);
        broadcast(new NotificationSent($message, $type, hideInBell: true))->toOthers();
    }

    public function toggleStatus($id)
    {
        $this->authorize('access-master-data');
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterStatus']);
        $this->resetPage();
    }

    public function render()
    {
        $query = PaymentMethod::with('account');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('account_number', 'like', '%' . $this->search . '%')
                  ->orWhere('account_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus);
        }

        $defaults = collect(['Bank', 'E-Wallet', 'Tunai']);
        $existing = PaymentMethod::distinct()->pluck('category')->filter();
        $categories = $defaults->concat($existing)->unique()->sort();

        return view('livewire.payment-method-manager', [
            'paymentMethods' => $query->orderBy('category')->orderBy('name')->paginate(12),
            'categories' => $categories,
            'chartOfAccounts' => ChartOfAccount::where('is_active', true)->orderBy('code')->get(),
        ]);
    }
}
