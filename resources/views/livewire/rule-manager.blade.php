<div>
    <div class="row mb-3 align-items-center">
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
                <button class="btn btn-success" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    Tambah Peraturan
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
                        <input type="text" class="form-control" placeholder="Cari judul, deskripsi, atau kategori..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md">
                    <select class="form-select" wire:model.live="filterLocation">
                        <option value="">Semua Lokasi</option>
                        <option value="global">Global (Semua Lokasi)</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non-aktif</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-secondary small">
            Menampilkan {{ $rules->firstItem() }} sampai {{ $rules->lastItem() }} dari {{ $rules->total() }} peraturan
        </div>
        <div>
            {{ $rules->links() }}
        </div>
    </div>

    @if($viewType === 'grid')
    <div class="row row-cards">
        @forelse($rules as $rule)
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <div class="fw-bold">{{ $rule->title }}</div>
                            <div class="text-secondary small">
                                <span class="badge bg-blue-lt">{{ $rule->category }}</span>
                                @if($rule->location)
                                    <span class="badge bg-purple-lt">{{ $rule->location->name }}</span>
                                @else
                                    <span class="badge bg-green-lt">Global</span>
                                @endif
                            </div>
                        </div>
                        <div class="ms-auto">
                            <label class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" {{ $rule->is_active ? 'checked' : '' }} wire:click="toggleStatus({{ $rule->id }})">
                            </label>
                        </div>
                    </div>
                    <div class="mt-3 text-secondary small" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $rule->description }}">
                        {{ $rule->description ?: 'Tidak ada deskripsi.' }}
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" wire:click="openModal({{ $rule->id }})">Edit</button>
                        <button class="btn btn-outline-danger btn-sm flex-fill" wire:click="deleteRule({{ $rule->id }})" wire:confirm="Yakin ingin menghapus peraturan ini?">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card card-md">
                <div class="card-body text-center py-4">
                    <div class="text-secondary mb-3">Tidak ada peraturan yang sesuai dengan kriteria pencarian.</div>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    @else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $rule->title }}</div>
                        </td>
                        <td><span class="badge bg-blue-lt">{{ $rule->category }}</span></td>
                        <td>
                            @if($rule->location)
                                <span class="badge bg-purple-lt">{{ $rule->location->name }}</span>
                            @else
                                <span class="badge bg-green-lt">Global</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-truncate" style="max-width: 200px;" title="{{ $rule->description }}">
                                {{ $rule->description ?: '-' }}
                            </div>
                        </td>
                        <td>
                            <label class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" {{ $rule->is_active ? 'checked' : '' }} wire:click="toggleStatus({{ $rule->id }})">
                            </label>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $rule->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteRule({{ $rule->id }})" wire:confirm="Yakin ingin menghapus peraturan ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">
                            Tidak ada peraturan yang sesuai dengan kriteria pencarian.
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $ruleId ? 'Edit Peraturan' : 'Tambah Peraturan Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveRule">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Judul Peraturan</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model="title" placeholder="e.g. Dilarang Merokok">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <input type="text" class="form-control @error('category') is-invalid @enderror" wire:model="category" list="category-options" placeholder="Pilih atau ketik kategori">
                                    <datalist id="category-options">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">
                                        @endforeach
                                        <option value="Keamanan">
                                        <option value="Kebersihan">
                                        <option value="Tamu">
                                        <option value="Parkir">
                                        <option value="Umum">
                                    </datalist>
                                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi (Kosongkan jika Global)</label>
                                    <select class="form-select @error('location_id') is-invalid @enderror" wire:model="location_id">
                                        <option value="">Global (Semua Lokasi)</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Detail</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="4" wire:model="description" placeholder="Jelaskan detail peraturan ini..."></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" wire:model="is_active">
                                <span class="form-check-label">Status Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Peraturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
