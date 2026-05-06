<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Riwayat Pindah Kamar</h2>
        </div>
        <div class="col-12 col-md-auto ms-md-auto">
            <div class="btn-list justify-content-md-end">
                <button class="btn btn-primary" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrows-exchange" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10h14l-4 -4" /><path d="M17 14h-14l4 4" /></svg>
                    Pindah Kamar Baru
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari penghuni / reg..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterLocationId">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="filterDateStart" title="Tanggal Mulai">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="filterDateEnd" title="Tanggal Akhir">
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
                        <th>Penghuni</th>
                        <th>Kamar Lama</th>
                        <th>Kamar Baru</th>
                        <th>Tanggal Pindah</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($moves as $move)
                    <tr>
                        <td>
                            <div class="font-weight-medium">{{ $move->registration->user->name }}</div>
                            <div class="text-secondary small">{{ $move->registration->registration_number }}</div>
                        </td>
                        <td>
                            <div>{{ $move->oldRoom->room_number }}</div>
                            <div class="text-secondary small">{{ $move->oldRoom->location->name }}</div>
                        </td>
                        <td>
                            <div>{{ $move->newRoom->room_number }}</div>
                            <div class="text-secondary small">{{ $move->newRoom->location->name }}</div>
                        </td>
                        <td class="text-secondary">
                            {{ $move->move_date->format('d M Y') }}
                        </td>
                        <td class="text-secondary">
                            {{ $move->reason ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-secondary">
                            Belum ada riwayat perpindahan kamar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($moves->hasPages())
        <div class="card-footer d-flex align-items-center">
            {{ $moves->links() }}
        </div>
        @endif
    </div>

    <!-- Modal -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proses Pindah Kamar</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveMove">
                    <div class="modal-body">
                        <div class="mb-3 position-relative">
                            <label class="form-label">Pilih Penghuni</label>
                            @if(!$registration_id)
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                    </span>
                                    <input type="text" class="form-control @error('registration_id') is-invalid @enderror"
                                           placeholder="Ketik nama penghuni untuk mencari..."
                                           wire:model.live.debounce.300ms="tenant_search">
                                </div>
                                @if(strlen($tenant_search) >= 1 && !empty($activeRegistrations))
                                    <div class="list-group list-group-flush position-absolute w-100 mt-1 shadow-lg border rounded bg-white" style="z-index: 1060; max-height: 200px; overflow-y: auto;">
                                        @foreach($activeRegistrations as $reg)
                                            <button type="button" class="list-group-item list-group-item-action py-2"
                                                    wire:click="selectTenant({{ $reg->id }}, {{ $reg->location_id }})">
                                                <div class="d-flex align-items-center">
                                                    @if($reg->user->avatar)
                                                        <span class="avatar avatar-xs me-2" style="background-image: url({{ asset('storage/' . $reg->user->avatar) }})"></span>
                                                    @else
                                                        <span class="avatar avatar-xs me-2">{{ substr($reg->user->name, 0, 2) }}</span>
                                                    @endif
                                                    <div>
                                                        <div class="font-weight-medium">{{ $reg->user->name }}</div>
                                                        <div class="text-secondary small">{{ $reg->room->room_number }} - {{ $reg->location->name }}</div>
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="d-flex align-items-center border p-2 rounded bg-light">
                                    @if($selectedRegistration->user->avatar)
                                        <span class="avatar avatar-sm me-2" style="background-image: url({{ asset('storage/' . $selectedRegistration->user->avatar) }})"></span>
                                    @else
                                        <span class="avatar avatar-sm me-2">{{ substr($selectedRegistration->user->name, 0, 2) }}</span>
                                    @endif
                                    <div class="flex-fill">
                                        <div class="font-weight-medium">{{ $selectedRegistration->user->name }}</div>
                                        <div class="text-secondary small">Kamar: {{ $selectedRegistration->room->room_number }} ({{ $selectedRegistration->location->name }})</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-ghost-danger btn-icon" wire:click="$set('registration_id', null)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            @endif
                            @error('registration_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        @if($registration_id)
                        <div class="mb-3">
                            <label class="form-label">Pilih Kamar Baru</label>
                            <select class="form-select @error('new_room_id') is-invalid @enderror" wire:model="new_room_id">
                                <option value="">-- Pilih Kamar Tersedia --</option>
                                @foreach($availableRooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->room_number }} (Rp {{ number_format($room->price, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                            @error('new_room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="form-hint">Hanya menampilkan kamar tersedia di lokasi yang sama.</small>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Tanggal Pindah</label>
                            <input type="date" class="form-control @error('move_date') is-invalid @enderror" wire:model="move_date">
                            @error('move_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan Pindah</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" rows="3" wire:model="reason" placeholder="Contoh: AC rusak, ingin pindah ke lantai bawah, dll."></textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Perpindahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
