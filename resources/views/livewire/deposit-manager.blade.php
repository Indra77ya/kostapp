<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Deposit</h2>
        </div>
        @if($viewMode === 'history')
        <div class="col-auto ms-auto btn-list">
            <button class="btn btn-white" wire:click="backToList()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                Kembali
            </button>
            <button class="btn btn-primary" wire:click="openModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Penyesuaian Saldo
            </button>
        </div>
        @endif
    </div>

    @if($viewMode === 'list')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama penghuni..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterLocation">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="sort">
                        <option value="name_asc">Nama (A-Z)</option>
                        <option value="name_desc">Nama (Z-A)</option>
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
                        <th>Penghuni</th>
                        <th>Lokasi / Kamar</th>
                        <th>Total Saldo Deposit</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    <tr>
                        <td>
                            <div class="font-weight-medium">{{ $reg->user->name }}</div>
                            <div class="text-secondary small">{{ $reg->user->email }}</div>
                        </td>
                        <td>
                            <div>{{ $reg->location->name }}</div>
                            <div class="text-secondary small">Kamar {{ $reg->room->room_number }}</div>
                        </td>
                        <td>
                            <span class="h3 mb-0 {{ $reg->deposit_balance > 0 ? 'text-primary' : 'text-secondary' }}">
                                Rp {{ number_format($reg->deposit_balance, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-white btn-sm" wire:click="selectRegistration({{ $reg->id }})">
                                Riwayat
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-secondary">Tidak ada data penghuni ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $registrations->links() }}
        </div>
    </div>
    @endif

    @if($viewMode === 'history')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">{{ $registration->user->name }}</h3>
                    <div class="text-secondary">
                        Kamar {{ $registration->room->room_number }} - {{ $registration->location->name }}
                    </div>
                </div>
                <div class="col-auto text-end">
                    <div class="text-secondary small">Total Saldo Deposit</div>
                    <div class="h1 mb-0 text-primary">Rp {{ number_format($registration->deposit_balance, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Masuk (Credit)</th>
                        <th>Keluar (Debit)</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $item)
                    <tr>
                        <td>{{ $item->transaction_date->format('d M Y') }}</td>
                        <td>
                            {{ $item->description }}
                            @if($item->payment_id)
                                <div class="small text-muted">ID Pembayaran: {{ $item->payment->payment_number }}</div>
                            @endif
                        </td>
                        <td>
                            @if($item->type === 'credit')
                                <span class="text-success fw-bold">+ Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($item->type === 'debit')
                                <span class="text-danger fw-bold">- Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if(!$item->payment_id)
                                <button class="btn btn-icon btn-white text-danger border-0" wire:click="deleteDeposit({{ $item->id }})" wire:confirm="Yakin ingin menghapus riwayat penyesuaian manual ini?">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-secondary">Belum ada riwayat transaksi deposit.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $history->links() }}
        </div>
    </div>
    @endif

    <!-- Modal Penyesuaian -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Penyesuaian Saldo Deposit</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveAdjustment">
                        <div class="mb-3">
                            <label class="form-label required">Tipe Transaksi</label>
                            <div class="form-selectgroup">
                                <label class="form-selectgroup-item">
                                    <input type="radio" wire:model="type" value="credit" class="form-selectgroup-input">
                                    <span class="form-selectgroup-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                                        Masuk (Credit)
                                    </span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" wire:model="type" value="debit" class="form-selectgroup-input">
                                    <span class="form-selectgroup-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /></svg>
                                        Keluar (Debit)
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Jumlah</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" wire:model="amount">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Keterangan</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" wire:model="description" placeholder="Misal: Penambahan saldo manual, Potongan kerusakan, dll">
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Tanggal</label>
                            <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" wire:model="transaction_date">
                            @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="modal-footer px-0 pb-0 mt-3">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto">
                                Simpan Penyesuaian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
