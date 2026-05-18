<div>
    <style>
        .carousel-control-prev, .carousel-control-next {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .card:hover .carousel-control-prev, .card:hover .carousel-control-next {
            opacity: 1;
        }
        .carousel-indicators {
            margin-bottom: 0.5rem;
        }
        .carousel-indicators [data-bs-target] {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 3px;
            margin-left: 3px;
        }
    </style>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Kamar</h2>
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
                <button class="btn btn-success" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    Tambah Kamar
                </button>
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
                        <input type="text" class="form-control" placeholder="Cari nomor kamar, tipe, atau fasilitas..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="available">Tersedia</option>
                        <option value="occupied">Terisi</option>
                        <option value="maintenance">Perbaikan</option>
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
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterRentalType">
                        <option value="">Semua Tipe</option>
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="sortOrder">
                        <option value="room_number_asc">No. Kamar (↑)</option>
                        <option value="room_number_desc">No. Kamar (↓)</option>
                        <option value="price_asc">Harga Termurah</option>
                        <option value="price_desc">Harga Termahal</option>
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
            Menampilkan {{ $rooms->firstItem() }} sampai {{ $rooms->lastItem() }} dari {{ $rooms->total() }} kamar
        </div>
        <div>
            {{ $rooms->links() }}
        </div>
    </div>

    @if($viewType === 'grid')
    <div class="row row-cards">
        @forelse($rooms as $room)
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="position-relative">
                    @if($room->images->count() > 0 || $room->image)
                        <div id="carousel-{{ $room->id }}" class="carousel slide" data-bs-ride="carousel">
                            @if($room->images->count() > 0)
                                <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#carousel-{{ $room->id }}" data-bs-slide-to="0" class="active"></button>
                                    @foreach($room->images as $index => $img)
                                        <button type="button" data-bs-target="#carousel-{{ $room->id }}" data-bs-slide-to="{{ $index + 1 }}"></button>
                                    @endforeach
                                </div>
                            @endif
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    @if($room->image)
                                        <img src="{{ Storage::url($room->image) }}" class="d-block w-100 card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" wire:click="openLightbox('{{ Storage::url($room->image) }}')">
                                    @else
                                        <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                                            No Image
                                        </div>
                                    @endif
                                </div>
                                @foreach($room->images as $img)
                                    <div class="carousel-item">
                                        <img src="{{ Storage::url($img->image_path) }}" class="d-block w-100 card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" wire:click="openLightbox('{{ Storage::url($img->image_path) }}')">
                                    </div>
                                @endforeach
                            </div>
                            @if($room->images->count() > 0)
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $room->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $room->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                            No Image
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <div><strong>Kamar {{ $room->room_number }}</strong></div>
                            <div class="text-secondary small">
                                @if($room->location)
                                    <span class="text-primary fw-bold">{{ $room->location->name }}</span><br>
                                @endif
                                {{ $room->room_type }}{{ $room->room_type && $room->floor ? ' - ' : '' }}{{ $room->floor ? 'Lantai ' . $room->floor : '' }}
                            </div>
                            <div class="mt-1">
                                <span class="badge {{ $room->status === 'available' ? 'bg-success' : ($room->status === 'occupied' ? 'bg-danger' : 'bg-warning') }} text-white">
                                    {{ $room->status === 'available' ? 'Tersedia' : ($room->status === 'occupied' ? 'Terisi' : 'Perbaikan') }}
                                </span>
                            </div>
                            <div class="mt-2">
                                @if($room->price_daily)
                                    <div class="small text-secondary">Rp {{ number_format($room->price_daily, 0, ',', '.') }} <small>/Hari</small></div>
                                @endif
                                @if($room->price_weekly)
                                    <div class="small text-secondary">Rp {{ number_format($room->price_weekly, 0, ',', '.') }} <small>/Minggu</small></div>
                                @endif
                                <div class="small text-secondary">Rp {{ number_format($room->price_monthly, 0, ',', '.') }} <small>/Bulan</small></div>
                                @if($room->price_yearly)
                                    <div class="small text-secondary">Rp {{ number_format($room->price_yearly, 0, ',', '.') }} <small>/Tahun</small></div>
                                @endif
                            </div>
                            @if($room->facilities)
                            <div class="mt-2">
                                @foreach(explode(',', $room->facilities) as $facility)
                                <span class="badge badge-outline text-secondary fw-normal badge-pill mb-1">{{ trim($facility) }}</span>
                                @endforeach
                            </div>
                            @endif
                            @if($room->description)
                            <div class="text-secondary small mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $room->description }}">
                                {{ $room->description }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" wire:click="openModal({{ $room->id }})">Edit</button>
                        <button class="btn btn-outline-danger btn-sm flex-fill" wire:click="deleteRoom({{ $room->id }})" wire:confirm="Yakin ingin menghapus kamar ini?">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card card-md">
                <div class="card-body text-center py-4">
                    <div class="text-secondary mb-3">Tidak ada kamar yang sesuai dengan kriteria pencarian.</div>
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
                        <th>No. Kamar</th>
                        <th>Lokasi</th>
                        <th>Tipe / Lantai</th>
                        <th>Fasilitas & Deskripsi</th>
                        <th>Status</th>
                        <th>Harga</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                    <tr>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->location->name ?? '-' }}</td>
                        <td class="text-secondary">
                            {{ $room->room_type }}{{ $room->room_type && $room->floor ? ' / ' : '' }}{{ $room->floor ? 'Lantai ' . $room->floor : '' }}
                        </td>
                        <td>
                            <div class="small text-truncate" style="max-width: 150px;" title="{{ $room->facilities }}">
                                <strong>{{ $room->facilities }}</strong>
                            </div>
                            <div class="small text-secondary text-truncate" style="max-width: 150px;" title="{{ $room->description }}">
                                {{ $room->description }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $room->status === 'available' ? 'bg-success' : ($room->status === 'occupied' ? 'bg-danger' : 'bg-warning') }} text-white">
                                {{ $room->status === 'available' ? 'Tersedia' : ($room->status === 'occupied' ? 'Terisi' : 'Perbaikan') }}
                            </span>
                        </td>
                        <td>
                            @if($room->price_daily) <div class="small text-muted">Rp {{ number_format($room->price_daily, 0, ',', '.') }} /Hari</div> @endif
                            @if($room->price_weekly) <div class="small text-muted">Rp {{ number_format($room->price_weekly, 0, ',', '.') }} /Mgg</div> @endif
                            <div class="small text-muted">Rp {{ number_format($room->price_monthly, 0, ',', '.') }} /Bln</div>
                            @if($room->price_yearly) <div class="small text-muted">Rp {{ number_format($room->price_yearly, 0, ',', '.') }} /Thn</div> @endif
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $room->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteRoom({{ $room->id }})" wire:confirm="Yakin ingin menghapus kamar ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">
                            Tidak ada kamar yang sesuai dengan kriteria pencarian.
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
                    <h5 class="modal-title">{{ $roomId ? 'Edit Kamar' : 'Tambah Kamar Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveRoom">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <select class="form-select @error('location_id') is-invalid @enderror" wire:model="location_id">
                                        <option value="">Pilih Lokasi</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Nomor Kamar</label>
                                    <input type="text" class="form-control @error('room_number') is-invalid @enderror" wire:model="room_number">
                                    @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Harga Harian</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('price_daily') is-invalid @enderror" wire:model="price_daily">
                                    </div>
                                    @error('price_daily') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Harga Mingguan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('price_weekly') is-invalid @enderror" wire:model="price_weekly">
                                    </div>
                                    @error('price_weekly') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Harga Bulanan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('price_monthly') is-invalid @enderror" wire:model="price_monthly">
                                    </div>
                                    @error('price_monthly') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Harga Tahunan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('price_yearly') is-invalid @enderror" wire:model="price_yearly">
                                    </div>
                                    @error('price_yearly') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Tipe Kamar</label>
                                    <input type="text" class="form-control @error('room_type') is-invalid @enderror" wire:model="room_type" placeholder="e.g. VIP, Reguler">
                                    @error('room_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Lantai</label>
                                    <input type="text" class="form-control @error('floor') is-invalid @enderror" wire:model="floor">
                                    @error('floor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                        <option value="available">Tersedia</option>
                                        <option value="occupied">Terisi</option>
                                        <option value="maintenance">Perbaikan</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fasilitas</label>
                            <div class="card card-body">
                                <div class="row">
                                    @foreach($allFacilities as $category => $items)
                                        <div class="col-md-6 mb-3">
                                            <div class="form-label small text-uppercase text-primary fw-bold mb-2 border-bottom pb-1">{{ $category }}</div>
                                            <div class="row g-2">
                                                @foreach($items as $item)
                                                    <div class="col-6">
                                                        <label class="form-check cursor-pointer">
                                                            <input class="form-check-input" type="checkbox" value="{{ $item->name }}" wire:model="facilities">
                                                            <span class="form-check-label">{{ $item->name }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('facilities') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Foto Utama</label>
                                    @if ($newImage)
                                        <div class="mb-2">
                                            <img src="{{ $newImage->temporaryUrl() }}" class="rounded border" style="height: 100px; width: 100%; object-fit: cover;">
                                        </div>
                                    @elseif ($image)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($image) }}" class="rounded border" style="height: 100px; width: 100%; object-fit: cover;">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('newImage') is-invalid @enderror" wire:model="newImage">
                                    <div wire:loading wire:target="newImage" class="text-info small">Uploading...</div>
                                    @error('newImage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Galeri Foto (Multiple)</label>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach($gallery as $img)
                                            <div class="position-relative">
                                                <img src="{{ Storage::url($img['image_path']) }}" class="rounded border" style="height: 45px; width: 45px; object-fit: cover;">
                                                <button type="button" class="btn btn-danger btn-icon btn-sm position-absolute top-0 end-0 p-0" style="width: 15px; height: 15px; font-size: 10px;" wire:click="deleteGalleryImage({{ $img['id'] }})" wire:confirm="Hapus foto ini dari galeri?">×</button>
                                            </div>
                                        @endforeach
                                        @foreach($newGallery as $index => $photo)
                                            <div class="position-relative">
                                                <img src="{{ $photo->temporaryUrl() }}" class="rounded border" style="height: 45px; width: 45px; object-fit: cover; opacity: 0.6;">
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="file" class="form-control @error('newGallery.*') is-invalid @enderror" wire:model="newGallery" multiple>
                                    <div wire:loading wire:target="newGallery" class="text-info small">Uploading...</div>
                                    @error('newGallery.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Kamar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div class="modal modal-blur fade {{ $isLightboxOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isLightboxOpen ? 'background: rgba(0,0,0,0.8)' : '' }}" wire:click.self="closeLightbox()">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" wire:click="closeLightbox()" aria-label="Close"></button>
                    @if($lightboxImageUrl)
                        <img src="{{ $lightboxImageUrl }}" class="img-fluid rounded shadow-lg">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
