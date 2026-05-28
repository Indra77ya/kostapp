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
                <div class="row g-2">
                    @if($registration->total_debt > 0)
                    <div class="{{ $registration->deposit_balance > 0 ? 'col-6' : 'col-12' }}">
                        <div class="card h-100">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-danger text-white avatar avatar-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary small">Sisa Tagihan</div>
                                        <div class="h4 mb-0 text-danger">Rp {{ number_format($registration->total_debt, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($registration->deposit_balance > 0)
                    <div class="{{ $registration->total_debt > 0 ? 'col-6' : 'col-12' }}">
                        <div class="card h-100">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-primary text-white avatar avatar-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-piggy-bank" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21l0 -4" /><path d="M19 21l0 -4" /><path d="M9 21l1 0" /><path d="M14 21l1 0" /><path d="M3.96 15.22c0 -3.41 2.72 -6.16 6.04 -6.16c.13 0 .26 0 .39 .01c.21 -1.41 1.48 -2.49 3 -2.49c1.66 0 3 1.34 3 3c0 .12 0 .23 -.01 .34c2.21 .5 3.61 2.45 3.61 4.54a5 5 0 0 1 -5 5h-6a5 5 0 0 1 -5 -4.24z" /><path d="M12 9v3" /><path d="M10 11l4 0" /></svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary small">Saldo Deposit</div>
                                        <div class="h4 mb-0 text-primary">Rp {{ number_format($registration->deposit_balance, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($registration->total_debt <= 0 && $registration->deposit_balance <= 0)
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-success text-white avatar avatar-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary small">Status Pembayaran</div>
                                        <div class="h4 mb-0 text-success">Lunas</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-receipt" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2m4 -14h6m-6 4h6m-2 4h2" /></svg>
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
                                <option value="umum">Pembayaran Umum</option>
                                <option value="deposit">Setor Deposit</option>
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
                                <select class="form-select @error('payment_method_id') is-invalid @enderror" wire:model.live="payment_method_id">
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

                        @if($selectedPm)
                            <div class="card bg-light-lt mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-blue text-white me-2">{{ $selectedPm->category }}</span>
                                        <strong class="text-primary">Tujuan Pembayaran:</strong>
                                    </div>
                                    @if($selectedPm->category !== 'Tunai')
                                        <div class="row g-2 small">
                                        @if($selectedPm->name !== 'Saldo Deposit')
                                            <div class="col-4 text-dark">Nama Bank/App:</div>
                                            <div class="col-8 fw-bold text-dark">{{ $selectedPm->name }}</div>
                                            <div class="col-4 text-dark">No. Rekening/ID:</div>
                                            <div class="col-8 fw-bold text-dark">{{ $selectedPm->account_number }}</div>
                                            <div class="col-4 text-dark">Atas Nama:</div>
                                            <div class="col-8 fw-bold text-dark">{{ $selectedPm->account_name }}</div>
                                        @else
                                            <div class="col-12 text-dark">
                                                Gunakan saldo deposit Anda untuk melunasi tagihan. Sisa deposit: <strong>Rp {{ number_format($registration->deposit_balance, 0, ',', '.') }}</strong>
                                            </div>
                                        @endif
                                        </div>
                                    @else
                                        <div class="small text-dark">
                                            Silakan serahkan pembayaran tunai langsung kepada pengelola kost.
                                        </div>
                                    @endif
                                    @if($selectedPm->instructions)
                                        <div class="mt-2 small border-top pt-2">
                                            <div class="text-dark fw-bold mb-1">Instruksi:</div>
                                            <div class="instructions-content text-dark">{!! $selectedPm->instructions !!}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label required">Jumlah Bayar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" wire:model.live="amount">
                            </div>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($status && $bill_id !== 'umum')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" wire:model="status" readonly>
                        </div>
                        @endif

                        @if($excess_amount > 0)
                        <div class="alert alert-info mb-3 py-2">
                            <div class="d-flex">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                                </div>
                                <div>
                                    Kelebihan pembayaran sebesar <strong>Rp {{ number_format($excess_amount, 0, ',', '.') }}</strong> akan otomatis tercatat sebagai Saldo Deposit Anda.
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($selectedPm && $selectedPm->category !== 'Tunai' && $selectedPm->name !== 'Saldo Deposit')
                        <div class="card border-dashed mb-3">
                            <div class="card-body p-3">
                                <div class="mb-2"><strong>Data {{ $selectedPm->category === 'E-Wallet' ? 'E-Wallet' : 'Bank' }}/Pengirim Anda:</strong></div>
                                <div class="mb-2">
                                    <label class="form-label required small mb-1">{{ $selectedPm->category === 'E-Wallet' ? 'Nama Aplikasi E-Wallet' : 'Nama Bank Asal' }}</label>
                                    <input type="text" class="form-control form-control-sm @error('sender_bank_name') is-invalid @enderror" wire:model="sender_bank_name" placeholder="{{ $selectedPm->category === 'E-Wallet' ? 'Contoh: GoPay, OVO, Dana' : 'Contoh: BCA, Mandiri, BNI' }}">
                                    @error('sender_bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="row g-2">
                                    <div class="col-6 mb-2">
                                        <label class="form-label required small mb-1">{{ $selectedPm->category === 'E-Wallet' ? 'No. HP / ID E-Wallet' : 'No. Rekening Asal' }}</label>
                                        <input type="text" class="form-control form-control-sm @error('sender_account_number') is-invalid @enderror" wire:model="sender_account_number" placeholder="{{ $selectedPm->category === 'E-Wallet' ? 'Nomor HP anda' : 'Nomor rekening anda' }}">
                                        @error('sender_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="form-label required small mb-1">Nama Pemilik {{ $selectedPm->category === 'E-Wallet' ? 'Akun' : 'Rekening' }}</label>
                                        <input type="text" class="form-control form-control-sm @error('sender_account_name') is-invalid @enderror" wire:model="sender_account_name" placeholder="Nama anda">
                                        @error('sender_account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(!$selectedPm || ($selectedPm->category !== 'Tunai' && $selectedPm->name !== 'Saldo Deposit'))
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
                        @endif
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
