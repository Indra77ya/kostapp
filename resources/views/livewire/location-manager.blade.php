<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Lokasi</h2>
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
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 9l5 -5l5 5" /><path d="M12 4l0 12" /></svg>
                        Ekspor
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" wire:click.prevent="exportData('xlsx')">Excel (.xlsx)</a></li>
                        <li><a class="dropdown-item" href="#" wire:click.prevent="exportData('csv')">CSV (.csv)</a></li>
                    </ul>
                </div>
                <button class="btn btn-outline-primary" wire:click="openImportModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                    Impor Data
                </button>
                <button class="btn btn-success" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    Tambah Lokasi
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama atau alamat lokasi..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-icon" title="Reset Filter" wire:click="resetFilters">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-rotate" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-secondary small">
            Menampilkan {{ $locations->firstItem() }} sampai {{ $locations->lastItem() }} dari {{ $locations->total() }} lokasi
        </div>
        <div>
            {{$locations->links(data: ['scrollTo' => false])}}
        </div>
    </div>

    @if($viewType === 'grid')
    <div class="row row-cards">
        @forelse($locations as $location)
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="position-relative">
                    @if($location->image)
                        <img src="{{ Storage::url($location->image) }}" class="d-block w-100 card-img-top" style="height: 180px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                            <span class="h1">{{ substr($location->name, 0, 2) }}</span>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="fw-bold h3 mb-0">{{ $location->name }}</div>
                        <div class="ms-auto">
                            <span class="badge bg-blue text-white">{{ $location->rooms()->count() }} Kamar</span>
                        </div>
                    </div>
                    <div class="text-secondary small mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
                        {{ $location->address ?: 'Alamat belum diatur' }}
                    </div>
                    @if($location->phone)
                    <div class="text-secondary small mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-phone" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>
                        {{ $location->phone }}
                    </div>
                    @endif
                    @if($location->google_maps_link)
                    <div class="mb-3">
                        <a href="{{ $location->google_maps_link }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5.5" /><path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" /><path d="M19 18v.01" /></svg>
                            Buka Google Maps
                        </a>
                    </div>
                    @endif
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" wire:click="openModal({{ $location->id }})">Edit</button>
                        <button class="btn btn-outline-danger btn-sm flex-fill" wire:click="deleteLocation({{ $location->id }})" wire:confirm="Yakin ingin menghapus lokasi ini? Semua kamar terkait akan kehilangan referensi lokasinya.">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card card-md">
                <div class="card-body text-center py-4">
                    <div class="text-secondary mb-3">Tidak ada lokasi yang ditemukan.</div>
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
                        <th class="w-1">Foto</th>
                        <th>Nama Lokasi</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th>Kamar</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                    <tr>
                        <td>
                            @if($location->image)
                                <span class="avatar avatar-sm" style="background-image: url({{ Storage::url($location->image) }})"></span>
                            @else
                                <span class="avatar avatar-sm">{{ substr($location->name, 0, 2) }}</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $location->name }}</td>
                        <td class="text-secondary small">
                            {{ $location->address }}
                            @if($location->google_maps_link)
                                <div class="mt-1">
                                    <a href="{{ $location->google_maps_link }}" target="_blank" class="text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5.5" /><path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" /><path d="M19 18v.01" /></svg>
                                        Buka Maps
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td class="text-secondary">{{ $location->phone ?: '-' }}</td>
                        <td>
                            <span class="badge bg-blue text-white">{{ $location->rooms()->count() }} Kamar</span>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $location->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteLocation({{ $location->id }})" wire:confirm="Yakin ingin menghapus lokasi ini? Semua kamar terkait akan kehilangan referensi lokasinya.">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">
                            Tidak ada lokasi yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Import Modal -->
    <div class="modal modal-blur fade {{ $isImportModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isImportModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Impor Data Lokasi</h5>
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
                                    <li>Untuk memperbarui data lokasi yang ada, sertakan nilai <strong>ID Ekspor</strong> dari file ekspor sistem.</li>
                                    <li>Jika terdapat duplikasi data nama lokasi, seluruh proses impor akan dibatalkan.</li>
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $locationId ? 'Edit Lokasi' : 'Tambah Lokasi Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveLocation">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lokasi</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Contoh: Kost Pusat, Kost Cabang Melati">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Telepon Pengelola</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model="phone" placeholder="+62...">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Link Google Maps</label>
                                    <input type="url" class="form-control @error('google_maps_link') is-invalid @enderror" wire:model="google_maps_link" placeholder="https://goo.gl/maps/...">
                                    @error('google_maps_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" rows="2" wire:model="address"></textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Lokasi</label>
                            @if ($newImage)
                                <div class="mb-2">
                                    <img src="{{ $newImage->temporaryUrl() }}" class="rounded border" style="height: 150px; width: 100%; object-fit: cover;">
                                </div>
                            @elseif ($image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($image) }}" class="rounded border" style="height: 150px; width: 100%; object-fit: cover;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('newImage') is-invalid @enderror" wire:model="newImage">
                            <div wire:loading wire:target="newImage" class="text-info small">Uploading...</div>
                            @error('newImage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
