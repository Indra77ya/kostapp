<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Pembayaran Saya</h2>
        </div>
        <div class="col-auto ms-auto">
            <button class="btn btn-primary" wire:click="openModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Lapor Pembayaran
            </button>
        </div>
    </div>

    @if(!$registration)
        <div class="alert alert-info">
            Anda tidak memiliki data registrasi aktif. Silakan hubungi admin jika ini adalah kesalahan.
        </div>
    @else
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar avatar-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-home" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Kamar {{ $registration->room->room_number }}</div>
                                <div class="text-secondary">{{ $registration->location->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-danger text-white avatar avatar-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                @php
                                    $totalBill = $bills->sum('amount');
                                    $totalPaid = $payments->where('status', '!=', 'Menunggu Konfirmasi')->sum('amount');
                                    $balance = $totalBill - $totalPaid;
                                @endphp
                                <div class="font-weight-medium text-danger">Sisa Tagihan</div>
                                <div class="h3 mb-0">Rp {{ number_format($balance, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Daftar Tagihan</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Keterangan</th>
                            <th>Jatuh Tempo</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                        <tr>
                            <td>
                                <div>{{ $bill->description }}</div>
                                <div class="text-secondary small">{{ $bill->bill_number }}</div>
                            </td>
                            <td>{{ $bill->due_date->format('d M Y') }}</td>
                            <td class="fw-bold">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($bill->status === 'Belum Lunas')
                                    <span class="badge bg-danger text-white">Belum Lunas</span>
                                @elseif($bill->status === 'Cicilan')
                                    <span class="badge bg-warning text-white">Cicilan</span>
                                @else
                                    <span class="badge bg-success text-white">Lunas</span>
                                @endif
                            </td>
                            <td>
                                @if($bill->status !== 'Lunas')
                                    <button class="btn btn-white btn-sm" wire:click="openModal({{ $bill->id }})">Bayar</button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-secondary">Belum ada daftar tagihan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Riwayat Pembayaran</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. Pembayaran</th>
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
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($payment->status === 'Menunggu Konfirmasi')
                                    <span class="badge bg-info text-white">{{ $payment->status }}</span>
                                @elseif(strpos($payment->status, 'Belum Lunas') !== false)
                                    <span class="badge bg-warning text-white">{{ $payment->status }}</span>
                                @else
                                    <span class="badge bg-success text-white">{{ $payment->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('payments.invoice', $payment->id) }}" target="_blank" class="btn btn-white btn-sm @if($payment->status === 'Menunggu Konfirmasi') disabled @endif" title="Cetak Kuitansi">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                    </a>
                                    @if($payment->proof_of_payment)
                                    <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-white btn-sm" title="Bukti Pembayaran">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.42 -.893 3.348 0l4.152 4.152" /><path d="M14 14l1 -1c.928 -.893 2.42 -.893 3.348 0l2.652 2.652" /></svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-secondary">Belum ada riwayat pembayaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                {{ $payments->links() }}
            </div>
        </div>
    @endif

    <!-- Modal Lapor Pembayaran -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lapor Pembayaran</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="savePayment">
                        <div class="mb-3">
                            <label class="form-label">Peruntukan Tagihan (Opsional)</label>
                            <select class="form-select" wire:model.live="bill_id">
                                <option value="">Pembayaran Umum</option>
                                @foreach($bills as $b)
                                    @if($b->status !== 'Lunas')
                                        <option value="{{ $b->id }}">{{ $b->description }} (Rp {{ number_format($b->amount - $b->paid_amount, 0, ',', '.') }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
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
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Jumlah Bayar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" wire:model="amount">
                            </div>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Bukti Pembayaran (Foto/Screenshot)</label>
                            <input type="file" class="form-control @error('proof_of_payment') is-invalid @enderror" wire:model="proof_of_payment">
                            @error('proof_of_payment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if($proof_of_payment)
                                <div class="mt-2 text-center">
                                    <img src="{{ $proof_of_payment->temporaryUrl() }}" style="height: 100px;" class="border rounded">
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Tambahan</label>
                            <textarea class="form-control" rows="3" wire:model="notes" placeholder="Contoh: Pembayaran melalui ATM Bank BCA a/n John Doe"></textarea>
                        </div>

                        <div class="modal-footer px-0 pb-0 mt-3">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto" wire:loading.attr="disabled">
                                <span wire:loading.remove>Kirim Laporan</span>
                                <span wire:loading>Mengirim...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
