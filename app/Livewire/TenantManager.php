<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;

class TenantManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $viewType = 'table'; // 'grid' or 'table'
    public $isModalOpen = false;
    public $tenantId;

    // Search & Filters
    public $search = '';
    public $filterStatus = 'active'; // 'active', 'checked_out', 'all'

    // Form fields
    public $name, $email, $phone_number, $address, $password;
    public $showPassword = false;

    // Peeking password in table
    public $peekPasswordId = null;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->tenantId,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'nullable|min:8',
        ];
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function togglePeek($id)
    {
        if ($this->peekPasswordId === $id) {
            $this->peekPasswordId = null;
        } else {
            $this->peekPasswordId = $id;
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->tenantId = $id;
            $tenant = User::find($id);
            $this->name = $tenant->name;
            $this->email = $tenant->email;
            $this->phone_number = $tenant->phone_number;
            $this->address = $tenant->address;
            $this->password = $tenant->password_plain;
        } else {
            $this->password = '12345678';
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
        $this->tenantId = null;
        $this->name = '';
        $this->email = '';
        $this->phone_number = '';
        $this->address = '';
        $this->password = '';
        $this->showPassword = false;
    }

    public function saveTenant()
    {
        if (!$this->tenantId) return;

        $this->validate();

        $tenantData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
        ];

        if ($this->password) {
            $tenantData['password'] = Hash::make($this->password);
            $tenantData['password_plain'] = $this->password;
        }

        $tenant = User::find($this->tenantId);
        $tenant->update($tenantData);

        NotificationSent::dispatch("Data penghuni {$tenant->name} berhasil diperbarui.", 'success');

        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function deleteTenant($id)
    {
        $tenant = User::find($id);
        $name = $tenant->name;
        $tenant->delete();

        DatabaseUpdated::dispatch();
        NotificationSent::dispatch("Penghuni {$name} berhasil dihapus.", 'success');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function setView($type)
    {
        $this->viewType = $type;
    }

    public function render()
    {
        $query = User::role('tenant');

        if ($this->filterStatus !== 'all') {
            $query->whereHas('registrations', function($q) {
                $q->where('status', $this->filterStatus);
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.tenant-manager', [
            'tenants' => $query->orderBy('name')->paginate(12),
        ]);
    }
}
