<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Konfirmasi Pembayaran</h2>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="input-icon">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                </span>
                <input type="text" class="form-control" placeholder="Cari nama penghuni..." wire:model.live.debounce.300ms="search">
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
                                <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-icon btn-sm btn-outline-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.42 -.893 3.348 0l4.152 4.152" /><path d="M14 14l1 -1c.928 -.893 2.42 -.893 3.348 0l2.652 2.652" /></svg>
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
                                    <img src="{{ asset('storage/' . $selectedPayment->proof_of_payment) }}" class="img-fluid border rounded shadow-sm" style="max-height: 400px;">
                                </div>
                            @else
                                <div class="alert alert-warning">Tidak ada bukti pembayaran yang diunggah.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Tutup</button>
                    <button type="button" class="btn btn-success ms-auto" wire:click="approve({{ $selectedPayment->id }}); closeModal()">Setujui Pembayaran</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
