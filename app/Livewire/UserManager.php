<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;

class UserManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $viewType = 'table'; // 'grid' or 'table'
    public $isModalOpen = false;
    public $userId;

    // Search and Filters
    public $search = '';
    public $filterRole = '';

    // Form fields
    public $name, $email, $role, $password;
    public $showPassword = false;

    // Peeking password in table
    public $peekPasswordId = null;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    protected function rules()
    {
        $rules = [
            'role' => 'required|exists:roles,name',
        ];

        if (!$this->userId) {
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|email|unique:users,email';
        }

        return $rules;
    }

    public function setView($type)
    {
        $this->viewType = $type;
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
            $this->userId = $id;
            $user = User::find($id);
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->getRoleNames()->first();
            $this->password = $user->password_plain;
        } else {
            $this->password = '12345678';
            $this->role = 'admin';
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
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->role = '';
        $this->password = '';
        $this->showPassword = false;
    }

    public function saveUser()
    {
        $this->validate();

        $userData = [];

        if (!$this->userId) {
            $userData['name'] = $this->name;
            $userData['email'] = $this->email;
        }

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
            $userData['password_plain'] = $this->password;
        }

        if ($this->userId) {
            $user = User::find($this->userId);
            $user->update($userData);
            $user->syncRoles([$this->role]);

            $message = "Pengguna {$user->name} berhasil diperbarui.";
            $type = 'success';
            $this->dispatch('notify', message: $message, type: $type);
            broadcast(new NotificationSent($message, $type))->toOthers();
        } else {
            $user = User::create($userData);
            $user->assignRole($this->role);

            $message = "Pengguna baru {$user->name} berhasil ditambahkan.";
            $type = 'success';
            $this->dispatch('notify', message: $message, type: $type);
            broadcast(new NotificationSent($message, $type))->toOthers();
        }

        DatabaseUpdated::dispatch();
        $this->closeModal();
    }

    public function resetPassword($id)
    {
        $user = User::find($id);
        $defaultPass = '12345678';
        $user->update([
            'password' => Hash::make($defaultPass),
            'password_plain' => $defaultPass
        ]);

        DatabaseUpdated::dispatch();
        $message = "Password {$user->name} berhasil direset ke 12345678.";
        $type = 'info';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
    }

    public function deleteUser($id)
    {
        if ($id === auth()->id()) {
            $message = "Anda tidak bisa menghapus diri sendiri!";
            $type = 'danger';
            $this->dispatch('notify', message: $message, type: $type);
            broadcast(new NotificationSent($message, $type))->toOthers();
            return;
        }

        $user = User::find($id);
        $name = $user->name;
        $user->delete();

        DatabaseUpdated::dispatch();
        $message = "Pengguna {$name} berhasil dihapus.";
        $type = 'success';
        $this->dispatch('notify', message: $message, type: $type);
        broadcast(new NotificationSent($message, $type))->toOthers();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterRole']);
        $this->resetPage();
    }

    public function render()
    {
        $query = User::query()->whereDoesntHave('roles', function($q) {
            $q->whereIn('name', ['developer', 'tenant']);
        });

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterRole) {
            $query->role($this->filterRole);
        }

        $roles = Role::whereNotIn('name', ['developer', 'tenant'])->get();

        return view('livewire.user-manager', [
            'users' => $query->with('roles')->orderBy('name')->paginate(12),
            'roles' => $roles
        ]);
    }
}
