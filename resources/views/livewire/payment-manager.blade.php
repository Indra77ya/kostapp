<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Input Pembayaran</h2>
        </div>
        <div class="col-auto ms-auto">
            <button class="btn btn-primary" wire:click="openModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Tambah Pembayaran
            </button>
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
                        <input type="text" class="form-control" placeholder="Cari nama, no. pembayaran..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterPaymentMethod">
                        <option value="">Semua Metode Pembayaran</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text small">Tgl Bayar:</span>
                        <input type="date" class="form-control" wire:model.live="filterDateStart">
                        <span class="input-group-text small">-</span>
                        <input type="date" class="form-control" wire:model.live="filterDateEnd">
                    </div>
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
                        <th>No. Pembayaran</th>
                        <th>Penghuni</th>
                        <th>Metode</th>
                        <th>Tgl Bayar</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><code>{{ $payment->payment_number }}</code></td>
                        <td>
                            <div class="font-weight-medium">{{ $payment->registration->user->name }}</div>
                            <div class="text-secondary small">Kamar {{ $payment->registration->room->room_number }}</div>
                        </td>
                        <td>{{ $payment->paymentMethod->name }}</td>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @if(strpos($payment->status, 'Belum Lunas') !== false)
                                <span class="badge bg-warning text-warning-foreground">{{ $payment->status }}</span>
                            @else
                                <span class="badge bg-success text-success-foreground">{{ $payment->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                @if($payment->proof_of_payment)
                                <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-white btn-sm">
                                    Bukti
                                </a>
                                @endif
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $payment->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deletePayment({{ $payment->id }})" wire:confirm="Yakin ingin menghapus data pembayaran ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Tidak ada data pembayaran ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $payments->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $paymentId ? 'Edit Pembayaran' : 'Tambah Pembayaran' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="savePayment">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Penghuni</label>
                                <select class="form-select @error('registration_id') is-invalid @enderror" wire:model.live="registration_id">
                                    <option value="">Pilih Penghuni</option>
                                    @foreach($registrations as $reg)
                                        <option value="{{ $reg->id }}">{{ $reg->user->name }} (Kamar {{ $reg->room->room_number }})</option>
                                    @endforeach
                                </select>
                                @error('registration_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Pembayaran</label>
                                <input type="text" class="form-control bg-light" wire:model="payment_number" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Metode Pembayaran</label>
                                <select class="form-select @error('payment_method_id') is-invalid @enderror" wire:model="payment_method_id">
                                    <option value="">Pilih Metode</option>
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Tanggal Bayar</label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror" wire:model="payment_date">
                                @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Jumlah Bayar</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" wire:model.live="amount">
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bukti Pembayaran (Opsional)</label>
                                <input type="file" class="form-control @error('proof_of_payment') is-invalid @enderror" wire:model="proof_of_payment">
                                @if($proof_of_payment && method_exists($proof_of_payment, 'temporaryUrl'))
                                    <div class="mt-2 text-center">
                                        <img src="{{ $proof_of_payment->temporaryUrl() }}" style="height: 150px;" class="border rounded">
                                    </div>
                                @endif
                                @error('proof_of_payment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" rows="3" wire:model="notes"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control bg-light" wire:model="status" readonly>
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0 mt-3">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto" wire:loading.attr="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                                <span wire:loading.remove>Simpan Pembayaran</span>
                                <span wire:loading>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
