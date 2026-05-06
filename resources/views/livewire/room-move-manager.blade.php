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
                <div class="col-md-12">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama penghuni..." wire:model.live.debounce.300ms="search">
                    </div>
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
                        <div class="mb-3">
                            <label class="form-label">Pilih Penghuni</label>
                            <select class="form-select @error('registration_id') is-invalid @enderror" wire:model.live="registration_id">
                                <option value="">-- Pilih Penghuni --</option>
                                @foreach($activeRegistrations as $reg)
                                    <option value="{{ $reg->id }}">{{ $reg->user->name }} ({{ $reg->room->room_number }} - {{ $reg->location->name }})</option>
                                @endforeach
                            </select>
                            @error('registration_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
