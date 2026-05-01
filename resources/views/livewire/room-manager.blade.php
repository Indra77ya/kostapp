<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Kamar</h2>
        </div>
        <div class="col-auto ms-auto">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M11 12l10 0" /></svg>
                    Tambah Kamar
                </button>
            </div>
        </div>
    </div>

    @if($viewType === 'grid')
    <div class="row row-cards">
        @foreach($rooms as $room)
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                @if($room->image)
                <a href="#" class="d-block"><img src="{{ Storage::url($room->image) }}" class="card-img-top" style="height: 150px; object-fit: cover;"></a>
                @else
                <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 150px;">
                    No Image
                </div>
                @endif
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <div><strong>Kamar {{ $room->room_number }}</strong></div>
                            <div class="text-secondary small">{{ $room->room_type }} - Lantai {{ $room->floor }}</div>
                            <div class="mt-1">
                                <span class="badge {{ $room->status === 'available' ? 'bg-success' : ($room->status === 'occupied' ? 'bg-danger' : 'bg-warning') }} text-white">
                                    {{ $room->status === 'available' ? 'Tersedia' : ($room->status === 'occupied' ? 'Terisi' : 'Perbaikan') }}
                                </span>
                            </div>
                            <div class="mt-2 h3">Rp {{ number_format($room->price, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm w-100" wire:click="openModal({{ $room->id }})">Edit</button>
                        <button class="btn btn-ghost-danger btn-sm w-100 mt-1" wire:click="deleteRoom({{ $room->id }})" wire:confirm="Yakin ingin menghapus kamar ini?">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Kamar</th>
                        <th>Tipe / Lantai</th>
                        <th>Status</th>
                        <th>Harga</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $room)
                    <tr>
                        <td>{{ $room->room_number }}</td>
                        <td class="text-secondary">
                            {{ $room->room_type }} / Lantai {{ $room->floor }}
                        </td>
                        <td>
                            <span class="badge {{ $room->status === 'available' ? 'bg-success' : ($room->status === 'occupied' ? 'bg-danger' : 'bg-warning') }} text-white">
                                {{ $room->status === 'available' ? 'Tersedia' : ($room->status === 'occupied' ? 'Terisi' : 'Perbaikan') }}
                            </span>
                        </td>
                        <td>Rp {{ number_format($room->price, 0, ',', '.') }}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $room->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteRoom({{ $room->id }})" wire:confirm="Yakin ingin menghapus kamar ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
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
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Kamar</label>
                                    <input type="text" class="form-control @error('room_number') is-invalid @enderror" wire:model="room_number">
                                    @error('room_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Harga (Bulan)</label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" wire:model="price">
                                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <textarea class="form-control @error('facilities') is-invalid @enderror" rows="2" wire:model="facilities" placeholder="e.g. AC, Kamar mandi dalam, Kasur"></textarea>
                            @error('facilities') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Kamar</label>
                            @if ($newImage)
                                <div class="mb-2">
                                    <img src="{{ $newImage->temporaryUrl() }}" style="height: 100px;">
                                </div>
                            @elseif ($image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($image) }}" style="height: 100px;">
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
                            Simpan Kamar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
