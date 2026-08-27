<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Transfer Dana (Kas & Bank)</h2>
            <div class="text-secondary mt-1">Pencatatan pemindahan dana antar akun kas/bank dan pemostingan jurnal otomatis</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <button class="btn btn-primary" wire:click="openModal">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Tambah Transfer
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Cari Transfer</label>
                    <input type="text" class="form-control" placeholder="No. Transfer, Akun, atau Keterangan..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    @if($search || $startDate || $endDate)
                        <button class="btn btn-outline-secondary w-100" wire:click="$set('search', ''); $set('startDate', ''); $set('endDate', '')">
                            Reset Filter
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Transfers List Table -->
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th>No. Transfer</th>
                        <th>Tanggal</th>
                        <th>Dari Akun (Asal)</th>
                        <th>Ke Akun (Tujuan)</th>
                        <th class="text-end">Jumlah Transfer</th>
                        <th class="text-end">Biaya Admin</th>
                        <th>Keterangan</th>
                        <th>Dibuat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td>
                                <span class="font-weight-medium badge bg-blue-lt">{{ $transfer->transfer_number }}</span>
                            </td>
                            <td>{{ $transfer->transfer_date ? $transfer->transfer_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <div><strong>{{ $transfer->fromAccount?->name }}</strong></div>
                                <div class="text-secondary small">{{ $transfer->fromAccount?->code }}</div>
                            </td>
                            <td>
                                <div><strong>{{ $transfer->toAccount?->name }}</strong></div>
                                <div class="text-secondary small">{{ $transfer->toAccount?->code }}</div>
                            </td>
                            <td class="text-end font-weight-medium text-success">
                                Rp {{ number_format($transfer->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                @if($transfer->admin_fee > 0)
                                    <span class="text-danger">Rp {{ number_format($transfer->admin_fee, 0, ',', '.') }}</span>
                                    @if($transfer->adminFeeAccount)
                                        <div class="text-secondary small">{{ $transfer->adminFeeAccount->name }}</div>
                                    @endif
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>{{ $transfer->notes ?: '-' }}</td>
                            <td>{{ $transfer->creator?->name ?: 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">
                                Tidak ada data transfer dana ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $transfers->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    <!-- Create Transfer Modal -->
    @if($isModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Transfer Dana</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Tanggal Transfer</label>
                                <input type="date" class="form-control @error('transfer_date') is-invalid @enderror" wire:model="transfer_date">
                                @error('transfer_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Dari Akun (Asal)</label>
                                    <select class="form-select @error('from_account_id') is-invalid @enderror" wire:model.live="from_account_id">
                                        <option value="">-- Pilih Akun Asal --</option>
                                        @foreach($cashBankAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Ke Akun (Tujuan)</label>
                                    <select class="form-select @error('to_account_id') is-invalid @enderror" wire:model.live="to_account_id">
                                        <option value="">-- Pilih Akun Tujuan --</option>
                                        @foreach($cashBankAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Jumlah Transfer (Rp)</label>
                                <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" placeholder="0" wire:model="amount">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Biaya Admin (Rp)</label>
                                    <input type="number" step="0.01" class="form-control @error('admin_fee') is-invalid @enderror" placeholder="0" wire:model.live="admin_fee">
                                    @error('admin_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label @if($admin_fee > 0) required @endif">Akun Beban Admin</label>
                                    <select class="form-select @error('admin_fee_account_id') is-invalid @enderror" wire:model="admin_fee_account_id" @if(!($admin_fee > 0)) disabled @endif>
                                        <option value="">-- Pilih Akun Beban --</option>
                                        @foreach($expenseAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('admin_fee_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan / Catatan</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Catatan tambahan transfer..." wire:model="notes"></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                Simpan Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
