<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Konfirmasi Pembayaran</h2>
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
                        <input type="text" class="form-control" placeholder="Cari nama penghuni..." wire:model.live.debounce.300ms="search">
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
                    <select class="form-select" wire:model.live="filterPaymentMethod">
                        <option value="">Semua Metode</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text small">Tgl Bayar:</span>
                        <input type="date" class="form-control px-2" wire:model.live="filterDateStart">
                        <span class="input-group-text small px-1">-</span>
                        <input type="date" class="form-control px-2" wire:model.live="filterDateEnd">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="sort">
                        <option value="latest">Terbaru</option>
                        <option value="oldest">Terlama</option>
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
                        <th>Tagihan / Periode</th>
                        <th>Tanggal Bayar</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Bukti</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>
                            <div class="font-weight-medium">{{ $payment->registration->user->name }}</div>
                            <div class="text-secondary small">{{ $payment->registration->user->email }}</div>
                        </td>
                        <td>
                            @if($payment->bill)
                                <div>{{ $payment->bill->description }}</div>
                                <div class="text-secondary small">{{ $payment->bill->bill_number }}</div>
                            @else
                                <span class="text-secondary">Pembayaran Umum</span>
                            @endif
                        </td>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="fw-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>{{ $payment->paymentMethod->name }}</td>
                        <td>
                            @if($payment->proof_of_payment)
                                <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-white btn-sm" title="Bukti Pembayaran">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-receipt" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2m4 -14h6m-6 4h6m-2 4h2" /></svg>
                                </a>
                            @else
                                <span class="text-secondary small">Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-success btn-sm" wire:click="approve({{ $payment->id }})">Setuju</button>
                                <button class="btn btn-danger btn-sm" wire:click="reject({{ $payment->id }})" wire:confirm="Yakin ingin menolak dan menghapus pembayaran ini?">Tolak</button>
                                <button class="btn btn-white btn-sm" wire:click="showDetail({{ $payment->id }})">Detail</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Tidak ada pembayaran yang menunggu konfirmasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $payments->links() }}
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal modal-blur fade {{ $isDetailModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isDetailModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                @if($selectedPayment)
                <div class="modal-header">
                    <h5 class="modal-title">Detail Konfirmasi Pembayaran</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Penghuni</label>
                            <div class="h3">{{ $selectedPayment->registration->user->name }}</div>
                            <div class="text-secondary">Kamar {{ $selectedPayment->registration->room->room_number }} - {{ $selectedPayment->registration->location->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-bold">No. Pembayaran</label>
                            <div class="h3">{{ $selectedPayment->payment_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Tanggal Bayar</label>
                            <div class="h4">{{ $selectedPayment->payment_date->format('d F Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Jumlah Bayar</label>
                            <div class="h2 text-primary">Rp {{ number_format($selectedPayment->amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Metode Pembayaran</label>
                            <div class="h4">{{ $selectedPayment->paymentMethod->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Peruntukan Tagihan</label>
                            <div class="h4">{{ $selectedPayment->bill ? $selectedPayment->bill->description : 'Pembayaran Umum' }}</div>
                        </div>

                        @if($selectedPayment->sender_bank_name)
                        <div class="col-12">
                            <div class="card bg-azure-lt border-0">
                                <div class="card-body p-3">
                                    @php
                                        $pmCategory = $selectedPayment->paymentMethod->category;
                                        $isEwallet = $pmCategory === 'E-Wallet';
                                    @endphp
                                    <label class="form-label text-azure small text-uppercase fw-bold mb-2">Informasi Pengirim ({{ $isEwallet ? 'E-Wallet' : 'Bank Asal' }})</label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="text-secondary small">{{ $isEwallet ? 'Nama Aplikasi' : 'Nama Bank' }}:</div>
                                            <div class="fw-bold">{{ $selectedPayment->sender_bank_name }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-secondary small">{{ $isEwallet ? 'No. HP / ID' : 'No. Rekening' }}:</div>
                                            <div class="fw-bold">{{ $selectedPayment->sender_account_number }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-secondary small">Atas Nama:</div>
                                            <div class="fw-bold">{{ $selectedPayment->sender_account_name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($selectedPayment->bill)
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="text-secondary small text-uppercase mb-1">Total Tagihan</div>
                                            <div class="h4 mb-0">Rp {{ number_format($selectedPayment->bill->amount, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col border-start">
                                            <div class="text-secondary small text-uppercase mb-1">Terbayar</div>
                                            <div class="h4 mb-0 text-success">Rp {{ number_format($selectedPayment->bill->paid_amount, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col border-start">
                                            <div class="text-secondary small text-uppercase mb-1">Sisa</div>
                                            @if($selectedPayment->bill->remaining_amount > 0)
                                                <div class="h4 mb-0 text-danger">Rp {{ number_format($selectedPayment->bill->remaining_amount, 0, ',', '.') }}</div>
                                            @elseif($selectedPayment->bill->remaining_amount < 0)
                                                <div class="h4 mb-0 text-primary">Deposit: Rp {{ number_format(abs($selectedPayment->bill->remaining_amount), 0, ',', '.') }}</div>
                                            @else
                                                <div class="h4 mb-0 text-success">Lunas</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($selectedPayment->notes)
                        <div class="col-12">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Catatan Penghuni</label>
                            <div class="p-2 bg-light rounded">{{ $selectedPayment->notes }}</div>
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label text-secondary small text-uppercase fw-bold">Bukti Pembayaran</label>
                            @if($selectedPayment->proof_of_payment)
                                <div class="text-center mt-2">
                                    <a href="{{ asset('storage/' . $selectedPayment->proof_of_payment) }}" target="_blank" class="d-block mb-2 text-decoration-none">
                                        <small class="text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                            Klik untuk memperbesar (Tab Baru)
                                        </small>
                                    </a>
                                    <a href="{{ asset('storage/' . $selectedPayment->proof_of_payment) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $selectedPayment->proof_of_payment) }}"
                                             class="img-fluid border rounded shadow-sm cursor-pointer"
                                             style="max-height: 400px;"
                                             title="Klik untuk memperbesar (Tab Baru)">
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-warning">Tidak ada bukti pembayaran yang diunggah.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Tutup</button>
                    <div class="ms-auto btn-list">
                        <a href="{{ route('payments.invoice', $selectedPayment->id) }}" target="_blank" class="btn btn-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                            Cetak Kuitansi
                        </a>
                        @if($selectedPayment->status === 'Menunggu Konfirmasi')
                        <button type="button" class="btn btn-success" wire:click="approve({{ $selectedPayment->id }})">Setujui Pembayaran</button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
