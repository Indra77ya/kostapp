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
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterDurationType">
                        <option value="">Jenis Sewa: Semua</option>
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterIsOpenEnded">
                        <option value="">Status Durasi: Semua</option>
                        <option value="1">Hingga Keluar</option>
                        <option value="0">Durasi Tetap</option>
                    </select>
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
                    <div class="mt-3 btn-list">
                        <button class="btn btn-sm btn-outline-primary w-100" wire:click="viewDetails({{ $tenant->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-info-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                            Lihat Data
                        </button>
                        @if($latestReg)
                            <a href="{{ route('registrations.invoice', $latestReg->id) }}" target="_blank" class="btn btn-sm btn-outline-info w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                Cetak
                            </a>
                        @endif
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
                        @php $latestReg = $tenant->registrations->first(); @endphp
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
                                <button class="btn btn-white btn-sm" wire:click="viewDetails({{ $tenant->id }})">Lihat Data</button>
                                @if($latestReg)
                                    <a href="{{ route('registrations.invoice', $latestReg->id) }}" target="_blank" class="btn btn-white btn-sm" title="Cetak Data Diri">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                    </a>
                                @endif
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

    <!-- Detail Modal -->
    <div class="modal modal-blur fade {{ $isDetailModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isDetailModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penghuni: {{ $viewingRegistration->user->name ?? '' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeDetailModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($viewingRegistration)
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card card-sm">
                                <div class="card-body p-2 text-center">
                                    <div class="mb-2">
                                        <strong>Foto Diri</strong>
                                    </div>
                                    @if($viewingRegistration->photo_self)
                                        <a href="{{ asset('storage/' . $viewingRegistration->photo_self) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $viewingRegistration->photo_self) }}"
                                                 class="img-fluid rounded border cursor-pointer"
                                                 style="max-height: 200px; width: 100%; object-fit: cover;"
                                                 title="Klik untuk memperbesar (Tab Baru)">
                                        </a>
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded border" style="height: 200px;">
                                            <span class="text-secondary small">Tidak ada foto</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card mb-3">
                                <div class="card-header"><h3 class="card-title">Informasi Pribadi</h3></div>
                                <div class="card-body p-0">
                                    <table class="table table-vcenter card-table">
                                        <tr><td class="text-secondary w-25">No. Registrasi</td><td><span class="badge bg-blue-lt">{{ $viewingRegistration->registration_number }}</span></td></tr>
                                        <tr><td class="text-secondary">Nama Lengkap</td><td>{{ $viewingRegistration->user->name }}</td></tr>
                                        <tr><td class="text-secondary">Email</td><td>{{ $viewingRegistration->user->email }}</td></tr>
                                        <tr><td class="text-secondary">No. Telepon</td><td>{{ $viewingRegistration->user->phone_number ?? '-' }}</td></tr>
                                        <tr><td class="text-secondary">Jenis Kelamin</td><td>{{ $viewingRegistration->gender }}</td></tr>
                                        <tr><td class="text-secondary">Tempat, Tgl Lahir</td><td>{{ $viewingRegistration->birth_place ?? '-' }}, {{ $viewingRegistration->birth_date ? $viewingRegistration->birth_date->format('d M Y') : '-' }}</td></tr>
                                        <tr><td class="text-secondary">Alamat</td><td>{{ $viewingRegistration->user->address ?? '-' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header"><h3 class="card-title">Informasi Kamar & Biaya</h3></div>
                                <div class="card-body p-0">
                                    <table class="table table-vcenter card-table">
                                        <tr><td class="text-secondary w-50">Lokasi</td><td>{{ $viewingRegistration->location->name }}</td></tr>
                                        <tr><td class="text-secondary">Kamar</td><td>{{ $viewingRegistration->room->room_number }} ({{ $viewingRegistration->room->room_type }})</td></tr>
                                        <tr><td class="text-secondary">Lantai</td><td>Lantai {{ $viewingRegistration->room->floor }}</td></tr>
                                        <tr><td class="text-secondary">Tgl Mulai Kost</td><td>{{ $viewingRegistration->stay_start_date->format('d M Y') }}</td></tr>
                                        <tr><td class="text-secondary">Harga Kamar</td><td>Rp {{ number_format($viewingRegistration->room_price, 0, ',', '.') }}</td></tr>
                                        <tr><td class="text-secondary">Diskon</td><td>{{ $viewingRegistration->discount_type === 'percent' ? $viewingRegistration->discount_value . '%' : 'Rp ' . number_format($viewingRegistration->discount_value, 0, ',', '.') }}</td></tr>
                                        <tr><td class="text-secondary">Total Biaya/Bulan</td><td><strong class="text-primary">Rp {{ number_format($viewingRegistration->total_price, 0, ',', '.') }}</strong></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header"><h3 class="card-title">Identitas & Instansi</h3></div>
                                <div class="card-body p-0">
                                    <table class="table table-vcenter card-table">
                                        <tr><td class="text-secondary w-50">Tipe Identitas</td><td>{{ $viewingRegistration->identity_type }}</td></tr>
                                        <tr><td class="text-secondary">No. Identitas</td><td>{{ $viewingRegistration->identity_number }}</td></tr>
                                        <tr><td class="text-secondary">No. KK</td><td>{{ $viewingRegistration->family_card_number ?? '-' }}</td></tr>
                                        <tr><td class="text-secondary">Nama Instansi</td><td>{{ $viewingRegistration->institution_name ?? '-' }}</td></tr>
                                        <tr><td class="text-secondary">No. Telp Instansi</td><td>{{ $viewingRegistration->institution_phone ?? '-' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title">Kontak Darurat</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Hubungan</th>
                                            <th>No. Telepon</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($viewingRegistration->emergencyContacts as $contact)
                                        <tr>
                                            <td>{{ $contact->name }}</td>
                                            <td>{{ $contact->relationship }}</td>
                                            <td>{{ $contact->phone_number }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-secondary py-3">Tidak ada data kontak darurat</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Foto Identitas ({{ $viewingRegistration->identity_type }})</h3></div>
                                <div class="card-body p-2 text-center">
                                    @if($viewingRegistration->photo_identity)
                                        <a href="{{ asset('storage/' . $viewingRegistration->photo_identity) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $viewingRegistration->photo_identity) }}"
                                                 class="img-fluid rounded border cursor-pointer"
                                                 style="max-height: 250px;"
                                                 title="Klik untuk memperbesar (Tab Baru)">
                                        </a>
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded border" style="height: 150px;">
                                            <span class="text-secondary small">Tidak ada foto identitas</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header"><h3 class="card-title">Foto Kartu Keluarga</h3></div>
                                <div class="card-body p-2 text-center">
                                    @if($viewingRegistration->photo_family_card)
                                        <a href="{{ asset('storage/' . $viewingRegistration->photo_family_card) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $viewingRegistration->photo_family_card) }}"
                                                 class="img-fluid rounded border cursor-pointer"
                                                 style="max-height: 250px;"
                                                 title="Klik untuk memperbesar (Tab Baru)">
                                        </a>
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded border" style="height: 150px;">
                                            <span class="text-secondary small">Tidak ada foto KK</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ms-auto" wire:click="closeDetailModal()">Tutup</button>
                </div>
            </div>
        </div>
    </div>


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
