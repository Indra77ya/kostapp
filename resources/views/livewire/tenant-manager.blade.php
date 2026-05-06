<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Daftar Penghuni</h2>
        </div>
        <div class="col-12 col-md-auto ms-md-auto">
            <div class="btn-list justify-content-md-end">
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
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama, email, atau telepon..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="active">Penghuni Aktif</option>
                        <option value="checked_out">Sudah Check Out</option>
                        <option value="all">Semua Status</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterLocation">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterType">
                        <option value="">Semua Tipe</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterFloor">
                        <option value="">Semua Lantai</option>
                        @foreach($floors as $floor)
                            <option value="{{ $floor }}">Lantai {{ $floor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-icon w-100" title="Reset Filter" wire:click="resetFilters">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-rotate" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-secondary small">
            Menampilkan {{ $tenants->firstItem() }} sampai {{ $tenants->lastItem() }} dari {{ $tenants->total() }} penghuni
        </div>
        <div>
            {{ $tenants->links() }}
        </div>
    </div>

    @if($viewType === 'grid')
    <div class="row row-cards">
        @forelse($tenants as $tenant)
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body p-4 text-center">
                    @if($tenant->avatar)
                        <span class="avatar avatar-xl mb-3 rounded" style="background-image: url({{ asset('storage/' . $tenant->avatar) }})"></span>
                    @else
                        <span class="avatar avatar-xl mb-3 rounded">{{ substr($tenant->name, 0, 2) }}</span>
                    @endif
                    <div class="mb-2">
                        @php $latestReg = $tenant->registrations->first(); @endphp
                        @if($latestReg)
                            @if($latestReg->status === 'active')
                                <span class="badge bg-success-lt">Check In</span>
                            @else
                                <span class="badge bg-secondary-lt">Check Out</span>
                            @endif
                        @else
                            <span class="badge bg-warning-lt">No Data</span>
                        @endif
                    </div>
                    <h3 class="m-0 mb-1 text-truncate">{{ $tenant->name }}</h3>
                    <div class="text-secondary text-truncate">{{ $tenant->email }}</div>
                    <div class="mt-2 text-secondary small">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>
                        {{ $tenant->phone_number ?? '-' }}
                    </div>
                    <div class="mt-2 text-secondary small">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
                        {{ $latestReg->location->name ?? '-' }}
                    </div>
                    <div class="mt-1 text-secondary small">
                        @if($latestReg && $latestReg->room)
                            {{ $latestReg->room->room_type ?? '-' }} - Lantai {{ $latestReg->room->floor ?? '-' }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="mt-2 text-secondary small d-flex align-items-center justify-content-center">
                        <span class="me-1">Password:</span>
                        @if($peekPasswordId === $tenant->id)
                            <span class="fw-bold">{{ $tenant->password_plain ?? '-' }}</span>
                        @else
                            <span>••••••••</span>
                        @endif
                        <a href="#" class="ms-1 text-secondary" wire:click.prevent="togglePeek({{ $tenant->id }})">
                            @if($peekPasswordId === $tenant->id)
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3l18 18" /><path d="M10.584 10.587a2 2 0 0 0 2.828 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.772 1.287 -1.663 2.332 -2.67 3.136" /></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                            @endif
                        </a>
                    </div>
                </div>
                <div class="d-flex">
                    <a href="#" class="card-btn" wire:click.prevent="openModal({{ $tenant->id }})">
                        Edit
                    </a>
                    <a href="#" class="card-btn text-danger" wire:click.prevent="deleteTenant({{ $tenant->id }})" wire:confirm="Yakin ingin menghapus penghuni ini?">
                        Hapus
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-4">
            <div class="text-secondary">Tidak ada penghuni yang ditemukan.</div>
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
                        <th>Status</th>
                        <th>Lokasi</th>
                        <th>Tipe / Lantai</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Password</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <div class="d-flex py-1 align-items-center">
                                @if($tenant->avatar)
                                    <span class="avatar me-2" style="background-image: url({{ asset('storage/' . $tenant->avatar) }})"></span>
                                @else
                                    <span class="avatar me-2">{{ substr($tenant->name, 0, 2) }}</span>
                                @endif
                                <div class="flex-fill">
                                    <div class="font-weight-medium">{{ $tenant->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php $latestReg = $tenant->registrations->first(); @endphp
                            @if($latestReg)
                                @if($latestReg->status === 'active')
                                    <span class="badge bg-success-lt">Check In</span>
                                @else
                                    <span class="badge bg-secondary-lt">Check Out</span>
                                @endif
                            @else
                                <span class="badge bg-warning-lt">No Data</span>
                            @endif
                        </td>
                        <td class="text-secondary">
                            {{ $latestReg->location->name ?? '-' }}
                        </td>
                        <td class="text-secondary">
                            @if($latestReg && $latestReg->room)
                                {{ $latestReg->room->room_type ?? '-' }} / Lantai {{ $latestReg->room->floor ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-secondary">{{ $tenant->email }}</td>
                        <td class="text-secondary">{{ $tenant->phone_number ?? '-' }}</td>
                        <td class="text-secondary text-truncate" style="max-width: 150px;">{{ $tenant->address ?? '-' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="me-2">
                                    @if($peekPasswordId === $tenant->id)
                                        <code>{{ $tenant->password_plain ?? '-' }}</code>
                                    @else
                                        <span>••••••••</span>
                                    @endif
                                </span>
                                <a href="#" class="text-secondary" wire:click.prevent="togglePeek({{ $tenant->id }})">
                                    @if($peekPasswordId === $tenant->id)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3l18 18" /><path d="M10.584 10.587a2 2 0 0 0 2.828 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.772 1.287 -1.663 2.332 -2.67 3.136" /></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                    @endif
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $tenant->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteTenant({{ $tenant->id }})" wire:confirm="Yakin ingin menghapus penghuni ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">
                            Tidak ada penghuni yang ditemukan.
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
                    <h5 class="modal-title">Edit Penghuni: {{ $name }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveTenant">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" wire:model="phone_number">
                            @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" rows="3" wire:model="address"></textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password {{ $tenantId ? '(Isi jika ingin ganti)' : '' }}</label>
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
                            @if(!$tenantId)
                                <small class="form-hint">Default: 12345678</small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
