<div>
    <div class="row mb-3 align-items-center">
        <div class="col-12 col-md-auto mb-3 mb-md-0">
            <h2 class="page-title">Manajemen Lokasi</h2>
        </div>
        <div class="col-12 col-md ms-md-auto">
            <div class="btn-list">
                <button class="btn btn-success" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    Tambah Lokasi
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="input-icon">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                </span>
                <input type="text" class="form-control" placeholder="Cari nama atau alamat lokasi..." wire:model.live.debounce.300ms="search">
            </div>
        </div>
    </div>

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
                            <span class="badge bg-blue text-white">{{ $location->rooms_count ?? $location->rooms()->count() }} Kamar</span>
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
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">Menampilkan <span>{{ $locations->firstItem() }}</span> sampai <span>{{ $locations->lastItem() }}</span> dari <span>{{ $locations->total() }}</span> lokasi</p>
            <div class="ms-auto">
                {{ $locations->links() }}
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
