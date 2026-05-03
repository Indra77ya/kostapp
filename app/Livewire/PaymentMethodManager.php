<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
    public $name, $category, $account_number, $account_name, $instructions, $logo, $is_active = true;
    public $oldLogo;

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'category' => 'required',
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
            $this->account_number = $paymentMethod->account_number;
            $this->account_name = $paymentMethod->account_name;
            $this->instructions = $paymentMethod->instructions;
            $this->oldLogo = $paymentMethod->logo;
            $this->is_active = $paymentMethod->is_active;
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
        $this->paymentMethodId = null;
        $this->name = '';
        $this->category = '';
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
        } else {
            PaymentMethod::create($data);
        }

        $this->closeModal();
    }

    public function deletePaymentMethod($id)
    {
        $this->authorize('access-master-data');
        $paymentMethod = PaymentMethod::findOrFail($id);
        if ($paymentMethod->logo) {
            Storage::disk('public')->delete($paymentMethod->logo);
        }
        $paymentMethod->delete();
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

    public function render()
    {
        $query = PaymentMethod::query();

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

        $categories = PaymentMethod::distinct()->pluck('category')->filter()->sort();

        return view('livewire.payment-method-manager', [
            'paymentMethods' => $query->orderBy('category')->orderBy('name')->paginate(12),
            'categories' => $categories
        ]);
    }
}
