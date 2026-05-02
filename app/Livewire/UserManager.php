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
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'role' => 'required|exists:roles,name',
        ];
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
            $this->role = 'tenant';
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

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
            $userData['password_plain'] = $this->password;
        }

        if ($this->userId) {
            $user = User::find($this->userId);
            $user->update($userData);
            $user->syncRoles([$this->role]);

            NotificationSent::dispatch("Pengguna {$user->name} berhasil diperbarui.", 'success');
        } else {
            $user = User::create($userData);
            $user->assignRole($this->role);

            NotificationSent::dispatch("Pengguna baru {$user->name} berhasil ditambahkan.", 'success');
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
        NotificationSent::dispatch("Password {$user->name} berhasil direset ke 12345678.", 'info');
    }

    public function deleteUser($id)
    {
        if ($id === auth()->id()) {
            NotificationSent::dispatch("Anda tidak bisa menghapus diri sendiri!", 'danger');
            return;
        }

        $user = User::find($id);
        $name = $user->name;
        $user->delete();

        DatabaseUpdated::dispatch();
        NotificationSent::dispatch("Pengguna {$name} berhasil dihapus.", 'success');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterRole) {
            $query->role($this->filterRole);
        }

        $roles = Role::all();

        return view('livewire.user-manager', [
            'users' => $query->with('roles')->orderBy('name')->paginate(12),
            'roles' => $roles
        ]);
    }
}
