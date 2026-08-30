<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Check Out</h2>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 mb-2">
                <div class="col-md-11">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama, email, no. registrasi..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-icon w-100" title="Reset Filter" wire:click="resetFilters">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-rotate" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5" /></svg>
                    </button>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterLocation">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterDurationType">
                        <option value="">Semua Jenis</option>
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterPaymentStatus">
                        <option value="">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="tunggakan">Tunggakan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="sort">
                        <option value="latest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                        <option value="name_asc">Nama A-Z</option>
                        <option value="name_desc">Nama Z-A</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text small">Tgl Inap:</span>
                        <input type="date" class="form-control px-2" wire:model.live="filterDateStart">
                        <span class="input-group-text small px-1">-</span>
                        <input type="date" class="form-control px-2" wire:model.live="filterDateEnd">
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
                        <th>No. Registrasi</th>
                        <th>Penghuni</th>
                        <th>Lokasi / Kamar</th>
                        <th>Tgl Mulai Inap</th>
                        <th>Status Pembayaran</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    <tr>
                        <td><code>{{ $reg->registration_number }}</code></td>
                        <td>
                            <div class="font-weight-medium">{{ $reg->user->name }}</div>
                            <div class="text-secondary small">
                                @switch($reg->duration_type)
                                    @case('daily') Harian @break
                                    @case('weekly') Mingguan @break
                                    @case('monthly') Bulanan @break
                                    @case('yearly') Tahunan @break
                                @endswitch
                            </div>
                        </td>
                        <td>
                            <div>{{ $reg->location->name }}</div>
                            <div class="text-secondary small">Kamar {{ $reg->room->room_number }}</div>
                        </td>
                        <td>{{ $reg->stay_start_date->format('d M Y') }}</td>
                        <td>
                            @if($reg->total_debt > 0)
                                <div class="badge bg-warning-lt mb-1">Tunggakan: Rp {{ number_format($reg->total_debt, 0, ',', '.') }}</div>
                            @endif
                            @if($reg->deposit_balance > 0)
                                <div class="badge bg-primary-lt">Deposit: Rp {{ number_format($reg->deposit_balance, 0, ',', '.') }}</div>
                            @endif
                            @if($reg->total_debt <= 0 && $reg->deposit_balance <= 0)
                                <span class="badge bg-success-lt">Lunas</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm" wire:click="openModal({{ $reg->id }})">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-logout" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                                Check Out
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">Tidak ada penghuni aktif ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{$registrations->links(data: ['scrollTo' => false])}}
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

                        @if($registration_data->deposit_balance > 0)
                        <div class="card bg-primary-lt border-0 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-primary">Saldo Deposit Penghuni:</span>
                                    <span class="fs-3 fw-bold text-primary">Rp {{ number_format($registration_data->deposit_balance, 0, ',', '.') }}</span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Pengembalian (Refund) Deposit</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control @error('deposit_refund') is-invalid @enderror" wire:model="deposit_refund" min="0" step="1000">
                                        </div>
                                        @error('deposit_refund') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Potongan Deposit (Kerusakan/Denda)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control @error('deposit_deduction') is-invalid @enderror" wire:model="deposit_deduction" min="0" step="1000">
                                        </div>
                                        @error('deposit_deduction') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label small mb-1">Alasan Potongan (Jika ada)</label>
                                        <input type="text" class="form-control form-control-sm" wire:model="deduction_notes" placeholder="Misal: Kerusakan kran air, denda keterlambatan...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

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
