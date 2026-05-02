<div>
    <div class="row mb-3 align-items-center">
        <div class="col-12 col-md-auto mb-3 mb-md-0">
            <h2 class="page-title">Manajemen Pengguna</h2>
        </div>
        <div class="col-12 col-md ms-md-auto">
            <div class="btn-list">
                <div class="btn-group">
                    <button type="button" class="btn {{ $viewType === 'grid' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setView('grid')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-grid" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M14 4m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M4 14m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M14 14m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
                        Grid
                    </button>
                    <button type="button" class="btn {{ $viewType === 'table' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setView('table')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l11 0" /><path d="M9 12l11 0" /><path d="M9 18l11 0" /><path d="M5 6l0 .01" /><path d="M5 12l0 .01" /><path d="M5 18l0 .01" /></svg>
                        Table
                    </button>
                </div>
                <button class="btn btn-success" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>
                    Tambah Pengguna
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama atau email..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-6">
                    <select class="form-select" wire:model.live="filterRole">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-secondary small">
            Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} pengguna
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>

    @if($viewType === 'grid')
    <div class="row row-cards">
        @forelse($users as $user)
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body p-4 text-center">
                    @if($user->avatar)
                        <span class="avatar avatar-xl mb-3 rounded" style="background-image: url({{ asset('storage/' . $user->avatar) }})"></span>
                    @else
                        <span class="avatar avatar-xl mb-3 rounded">{{ substr($user->name, 0, 2) }}</span>
                    @endif
                    <h3 class="m-0 mb-1 text-truncate">{{ $user->name }}</h3>
                    <div class="text-secondary text-truncate">{{ $user->email }}</div>
                    <div class="mt-3">
                        <span class="badge bg-purple-lt">{{ ucfirst($user->getRoleNames()->first()) }}</span>
                    </div>
                        <div class="mt-2 text-secondary small d-flex align-items-center justify-content-center">
                            <span class="me-1">Password:</span>
                        @if($peekPasswordId === $user->id)
                            <span class="fw-bold">{{ $user->password_plain ?? '-' }}</span>
                        @else
                            <span>••••••••</span>
                        @endif
                            <a href="#" class="ms-1 text-secondary" wire:click.prevent="togglePeek({{ $user->id }})">
                                @if($peekPasswordId === $user->id)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3l18 18" /><path d="M10.584 10.587a2 2 0 0 0 2.828 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.772 1.287 -1.663 2.332 -2.67 3.136" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                @endif
                            </a>
                            <a href="#" class="text-warning ms-1" wire:click.prevent="resetPassword({{ $user->id }})" wire:confirm="Reset password pengguna ini ke 12345678?" title="Reset Password">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                        </a>
                    </div>
                </div>
                <div class="d-flex">
                    <a href="#" class="card-btn" wire:click.prevent="openModal({{ $user->id }})">
                        Edit
                    </a>
                    <a href="#" class="card-btn text-danger" wire:click.prevent="deleteUser({{ $user->id }})" wire:confirm="Yakin ingin menghapus pengguna ini?">
                        Hapus
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-4">
            <div class="text-secondary">Tidak ada pengguna yang ditemukan.</div>
        </div>
        @endforelse
    </div>
    @else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Password</th>
                        <th>Dibuat Pada</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex py-1 align-items-center">
                                @if($user->avatar)
                                    <span class="avatar me-2" style="background-image: url({{ asset('storage/' . $user->avatar) }})"></span>
                                @else
                                    <span class="avatar me-2">{{ substr($user->name, 0, 2) }}</span>
                                @endif
                                <div class="flex-fill">
                                    <div class="font-weight-medium">{{ $user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-purple-lt">{{ ucfirst($user->getRoleNames()->first()) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2">
                                    @if($peekPasswordId === $user->id)
                                        <code>{{ $user->password_plain ?? '-' }}</code>
                                    @else
                                        <span>••••••••</span>
                                    @endif
                                </span>
                                <a href="#" class="text-secondary" wire:click.prevent="togglePeek({{ $user->id }})">
                                    @if($peekPasswordId === $user->id)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3l18 18" /><path d="M10.584 10.587a2 2 0 0 0 2.828 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.772 1.287 -1.663 2.332 -2.67 3.136" /></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                    @endif
                                </a>
                                <a href="#" class="text-warning ms-2" wire:click.prevent="resetPassword({{ $user->id }})" wire:confirm="Reset password pengguna ini ke 12345678?" title="Reset Password">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                                </a>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $user->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteUser({{ $user->id }})" wire:confirm="Yakin ingin menghapus pengguna ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">
                            Tidak ada pengguna yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Modal -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $userId ? 'Edit Pengguna: ' . $name : 'Tambah Pengguna Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveUser">
                    <div class="modal-body">
                        @if(!$userId)
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select @error('role') is-invalid @enderror" wire:model="role">
                                <option value="">Pilih Role</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password {{ $userId ? '(Isi jika ingin ganti)' : '' }}</label>
                            <div class="input-group input-group-flat">
                                <input type="{{ $showPassword ? 'text' : 'password' }}" class="form-control @error('password') is-invalid @enderror" wire:model="password" autocomplete="off">
                                <span class="input-group-text px-2">
                                    <a href="#" class="link-secondary" wire:click.prevent="togglePassword" title="{{ $showPassword ? 'Sembunyikan' : 'Tampilkan' }}">
                                        @if($showPassword)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3l18 18" /><path d="M10.584 10.587a2 2 0 0 0 2.828 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.772 1.287 -1.663 2.332 -2.67 3.136" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        @endif
                                    </a>
                                </span>
                            </div>
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @if(!$userId)
                                <small class="form-hint">Default: 12345678</small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
