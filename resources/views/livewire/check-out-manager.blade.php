<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Check Out</h2>
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
                        <input type="text" class="form-control" placeholder="Cari nama, email, no. registrasi..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-6">
                    <select class="form-select" wire:model.live="filterLocation">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Registrasi</th>
                        <th>Penghuni</th>
                        <th>Lokasi / Kamar</th>
                        <th>Tgl Mulai Inap</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    <tr>
                        <td><code>{{ $reg->registration_number }}</code></td>
                        <td>
                            <div class="font-weight-medium">{{ $reg->user->name }}</div>
                            <div class="text-secondary small">{{ $reg->user->email }}</div>
                        </td>
                        <td>
                            <div>{{ $reg->location->name }}</div>
                            <div class="text-secondary small">Kamar {{ $reg->room->room_number }}</div>
                        </td>
                        <td>{{ $reg->stay_start_date->format('d M Y') }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" wire:click="openModal({{ $reg->id }})">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-logout" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                                Check Out
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-secondary">Tidak ada penghuni aktif ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $registrations->links() }}
        </div>
    </div>

    <!-- Modal Check Out -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Check Out</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()"></button>
                </div>
                @if($registration_data)
                <form wire:submit.prevent="processCheckOut">
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="text-secondary mb-2">Anda akan melakukan check out untuk:</div>
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            @if($registration_data->user->avatar)
                                                <span class="avatar avatar-md rounded" style="background-image: url({{ asset('storage/' . $registration_data->user->avatar) }})"></span>
                                            @else
                                                <span class="avatar avatar-md rounded">{{ substr($registration_data->user->name, 0, 2) }}</span>
                                            @endif
                                        </div>
                                        <div class="col">
                                            <div class="fw-bold">{{ $registration_data->user->name }}</div>
                                            <div class="text-secondary small">{{ $registration_data->location->name }} - Kamar {{ $registration_data->room->room_number }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Tanggal Check Out</label>
                            <input type="date" class="form-control @error('check_out_date') is-invalid @enderror" wire:model="check_out_date">
                            @error('check_out_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Check Out</label>
                            <textarea class="form-control @error('check_out_notes') is-invalid @enderror" rows="3" wire:model="check_out_notes" placeholder="Misal: Kunci sudah dikembalikan, Listrik sudah lunas..."></textarea>
                            @error('check_out_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-danger ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-logout" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                            Konfirmasi Check Out
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
