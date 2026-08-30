<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Fasilitas</h2>
        </div>
        <div class="col-12 col-md-auto ms-md-auto text-end">
            <div class="btn-list justify-content-md-end">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Ekspor
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" wire:click.prevent="exportData('xlsx')">Excel (.xlsx)</a></li>
                        <li><a class="dropdown-item" href="#" wire:click.prevent="exportData('csv')">CSV (.csv)</a></li>
                    </ul>
                </div>
                <button class="btn btn-outline-primary" wire:click="openImportModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 9l5 -5l5 5" /><path d="M12 4l0 12" /></svg>
                    Impor Data
                </button>
                <button class="btn btn-success" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    Tambah Fasilitas
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-7">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama fasilitas..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">Semua Kategori</option>
                        <option value="Kamar">Fasilitas Kamar</option>
                        <option value="Umum">Fasilitas Umum</option>
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

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Fasilitas</th>
                        <th>Kategori</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facilities as $facility)
                    <tr>
                        <td class="fw-bold">{{ $facility->name }}</td>
                        <td>
                            <span class="badge {{ $facility->category === 'Kamar' ? 'bg-blue' : 'bg-green' }} text-white">
                                {{ $facility->category }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $facility->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteFacility({{ $facility->id }})" wire:confirm="Yakin ingin menghapus fasilitas ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-secondary">
                            Tidak ada fasilitas yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($facilities->hasPages())
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary small d-none d-md-block">
                Menampilkan {{ $facilities->firstItem() }} sampai {{ $facilities->lastItem() }} dari {{ $facilities->total() }} fasilitas
            </p>
            <div class="ms-auto">
                {{$facilities->links(data: ['scrollTo' => false])}}
            </div>
        </div>
        @endif
    </div>

    <!-- Import Modal -->
    <div class="modal modal-blur fade {{ $isImportModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isImportModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Impor Data Fasilitas</h5>
                    <button type="button" class="btn-close" wire:click="closeImportModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="importData">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Unduh Template File</label>
                            <p class="text-secondary small mb-2">Gunakan template resmi untuk memastikan format kolom sesuai. Template telah dilengkapi contoh pengisian data.</p>
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-outline-secondary" wire:click="downloadTemplate('xlsx')">
                                    Template Excel (.xlsx)
                                </button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="downloadTemplate('csv')">
                                    Template CSV (.csv)
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Pilih File Impor</label>
                            <input type="file" class="form-control @error('importFile') is-invalid @enderror" wire:model="importFile" accept=".xlsx,.xls,.csv">
                            <div wire:loading wire:target="importFile" class="text-info small mt-1">Membaca file...</div>
                            @error('importFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small text-secondary mt-2">
                                <ul class="ps-3 mb-0">
                                    <li>Format file yang didukung: <strong>.xlsx, .xls, .csv</strong>.</li>
                                    <li>Untuk memperbarui data fasilitas yang ada, sertakan nilai <strong>ID Ekspor</strong> dari file ekspor sistem.</li>
                                    <li>Jika terdapat duplikasi data nama fasilitas, seluruh proses impor akan dibatalkan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeImportModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto" wire:loading.attr="disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 9l5 -5l5 5" /><path d="M12 4l0 12" /></svg>
                            Mulai Impor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $facilityId ? 'Edit Fasilitas' : 'Tambah Fasilitas Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveFacility">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Fasilitas</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Contoh: AC, WiFi, Parkir Motor">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select @error('category') is-invalid @enderror" wire:model="category">
                                <option value="Kamar">Fasilitas Kamar</option>
                                <option value="Umum">Fasilitas Umum</option>
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Fasilitas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
