<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Transaksi Pembayaran</h2>
        </div>
        @if($viewMode === 'history')
        <div class="col-auto ms-auto btn-list">
            <button class="btn btn-white" wire:click="backToList()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                Kembali
            </button>
            <button class="btn btn-white" wire:click="openBillModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-invoice" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 7l1 0" /><path d="M9 13l6 0" /><path d="M13 17l2 0" /></svg>
                Tambah Tagihan
            </button>
            <button class="btn btn-primary" wire:click="openModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Tambah Pembayaran
            </button>
        </div>
        @endif
    </div>

    @if($viewMode === 'residents')
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
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterLocation">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterDurationType">
                        <option value="">Semua Jenis</option>
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterPaymentStatus">
                        <option value="">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="tunggakan">Ada Tunggakan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="sort">
                        <option value="name_asc">Nama (A-Z)</option>
                        <option value="name_desc">Nama (Z-A)</option>
                        <option value="balance_desc">Sisa Terbanyak</option>
                        <option value="balance_asc">Sisa Terkecil</option>
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
                        <th>Jenis Sewa</th>
                        <th>Total Tagihan</th>
                        <th>Total Bayar</th>
                        <th>Sisa</th>
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
                            <span class="text-capitalize">{{ $reg->duration_type }}</span>
                        </td>
                        <td>Rp {{ number_format($reg->total_bill, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($reg->paid_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($reg->total_debt > 0)
                                <div class="text-danger fw-bold">Hutang: Rp {{ number_format($reg->total_debt, 0, ',', '.') }}</div>
                            @endif
                            @if($reg->deposit_balance > 0)
                                <div class="text-primary fw-bold">Deposit: Rp {{ number_format($reg->deposit_balance, 0, ',', '.') }}</div>
                            @endif
                            @if($reg->total_debt <= 0 && $reg->deposit_balance <= 0)
                                <span class="text-success fw-bold">Lunas</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" wire:click="selectRegistration({{ $reg->id }})">
                                Bayar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Tidak ada data penghuni ditemukan.</td>
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
                <div class="col-auto">
                    @if($registration->user->avatar)
                        <span class="avatar avatar-lg rounded" style="background-image: url({{ asset('storage/' . $registration->user->avatar) }})"></span>
                    @else
                        <span class="avatar avatar-lg rounded">{{ substr($registration->user->name, 0, 2) }}</span>
                    @endif
                </div>
                <div class="col">
                    <h3 class="mb-0">{{ $registration->user->name }}</h3>
                    <div class="text-secondary">
                        Kamar {{ $registration->room->room_number }} - {{ $registration->location->name }}
                    </div>
                </div>
                <div class="col-auto text-end">
                    <div class="text-secondary small">Sisa Tagihan</div>
                    <div class="h2 mb-0 text-danger">Rp {{ number_format($registration->total_debt, 0, ',', '.') }}</div>
                </div>
                <div class="col-auto text-end border-start ps-3">
                    <div class="text-secondary small">Saldo Deposit</div>
                    <div class="h2 mb-0 text-primary">Rp {{ number_format($registration->deposit_balance, 0, ',', '.') }}</div>
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
                        <th>No. Tagihan</th>
                        <th>Keterangan</th>
                        <th>Jatuh Tempo</th>
                        <th>Diskon</th>
                        <th>Jumlah</th>
                        <th>Terbayar</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr>
                        <td><code>{{ $bill->bill_number }}</code></td>
                        <td>{{ $bill->description }}</td>
                        <td>{{ $bill->due_date->format('d M Y') }}</td>
                        <td class="text-secondary">Rp {{ number_format($bill->discount, 0, ',', '.') }}</td>
                        <td class="fw-bold text-primary">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($bill->remaining_amount > 0)
                                <span class="text-danger fw-bold">Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}</span>
                            @elseif($bill->remaining_amount < 0)
                                <span class="text-primary fw-bold">Deposit: Rp {{ number_format(abs($bill->remaining_amount), 0, ',', '.') }}</span>
                            @else
                                <span class="text-success fw-bold">Lunas</span>
                            @endif
                        </td>
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
                            <div class="btn-list flex-nowrap">
                                <a href="{{ route('bills.invoice', $bill->id) }}" target="_blank" class="btn btn-white btn-sm" title="Cetak Invoice">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                </a>
                                <button class="btn btn-white btn-sm" wire:click="openBillModal({{ $bill->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteBill({{ $bill->id }})" wire:confirm="Yakin ingin menghapus tagihan ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-secondary">Belum ada daftar tagihan.</td>
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
                        <td>{{ $payment->paymentMethod->name }}</td>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @if(strpos($payment->status, 'Belum Lunas') !== false)
                                <span class="badge bg-warning text-white">{{ $payment->status }}</span>
                            @else
                                <span class="badge bg-success text-white">{{ $payment->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <a href="{{ route('payments.invoice', $payment->id) }}" target="_blank" class="btn btn-white btn-sm" title="Cetak Kuitansi">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                </a>
                                @if($payment->proof_of_payment)
                                <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-white btn-sm" title="Bukti Pembayaran">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-receipt" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2m4 -14h6m-6 4h6m-2 4h2" /></svg>
                                </a>
                                @endif
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $payment->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deletePayment({{ $payment->id }})" wire:confirm="Yakin ingin menghapus data pembayaran ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">Belum ada riwayat pembayaran.</td>
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
                                @if($viewMode === 'history')
                                    <input type="text" class="form-control bg-light" value="{{ $registration->user->name }}" readonly>
                                    <input type="hidden" wire:model="registration_id">
                                @else
                                    <select class="form-select @error('registration_id') is-invalid @enderror" wire:model.live="registration_id">
                                        <option value="">Pilih Penghuni</option>
                                        @foreach($registrations as $reg)
                                            <option value="{{ $reg->id }}">{{ $reg->user->name }} (Kamar {{ $reg->room->room_number }})</option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('registration_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Pembayaran</label>
                                <input type="text" class="form-control bg-light" wire:model="payment_number" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Pilih Tagihan (Opsional)</label>
                                <select class="form-select @error('bill_id') is-invalid @enderror" wire:model.live="bill_id">
                                    <option value="umum">Pembayaran Umum</option>
                                    <option value="deposit">Setor Deposit</option>
                                    @if($viewMode === 'history')
                                        @foreach($bills as $b)
                                            <option value="{{ $b->id }}">{{ $b->bill_number }} - {{ $b->description }} (Sisa: Rp {{ number_format($b->remaining_amount, 0, ',', '.') }})</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('bill_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="small text-secondary mt-1">Pilih tagihan spesifik untuk melunasi tagihan tersebut secara otomatis.</div>
                            </div>
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
                            @if($status && $bill_id !== 'umum')
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control bg-light font-weight-bold" wire:model="status" readonly>
                            </div>
                            @endif

                            @if($excess_amount > 0)
                            <div class="col-12 mb-3">
                                <div class="alert alert-info py-2 mb-0">
                                    <div class="d-flex">
                                        <div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                                        </div>
                                        <div>
                                            Kelebihan pembayaran sebesar <strong>Rp {{ number_format($excess_amount, 0, ',', '.') }}</strong> akan otomatis tercatat sebagai Saldo Deposit penghuni.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @php
                                $selectedPm = \App\Models\PaymentMethod::find($payment_method_id);
                            @endphp

                            @if($selectedPm && $selectedPm->category !== 'Tunai')
                                <div class="col-12 mt-2">
                                    <div class="card bg-azure-lt border-0 shadow-none">
                                        <div class="card-body p-3">
                                            <div class="mb-3"><strong>Data {{ $selectedPm->category === 'E-Wallet' ? 'E-Wallet' : 'Bank' }} / Pengirim:</strong></div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">{{ $selectedPm->category === 'E-Wallet' ? 'Nama Aplikasi' : 'Nama Bank Asal' }}</label>
                                                    <input type="text" class="form-control form-control-sm @error('sender_bank_name') is-invalid @enderror" wire:model="sender_bank_name">
                                                    @error('sender_bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">{{ $selectedPm->category === 'E-Wallet' ? 'No. HP / ID' : 'No. Rekening Asal' }}</label>
                                                    <input type="text" class="form-control form-control-sm @error('sender_account_number') is-invalid @enderror" wire:model="sender_account_number">
                                                    @error('sender_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Nama Pemilik {{ $selectedPm->category === 'E-Wallet' ? 'Akun' : 'Rekening' }}</label>
                                                    <input type="text" class="form-control form-control-sm @error('sender_account_name') is-invalid @enderror" wire:model="sender_account_name">
                                                    @error('sender_account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
    <!-- Bill Modal -->
    <div class="modal modal-blur fade {{ $isBillModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isBillModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $billId ? 'Edit Tagihan' : 'Tambah Tagihan' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeBillModal()"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveBill">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label required">No. Tagihan</label>
                                <input type="text" class="form-control bg-light" wire:model="bill_number" readonly>
                                @error('bill_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label required">Keterangan</label>
                                <input type="text" class="form-control @error('bill_description') is-invalid @enderror" wire:model="bill_description" placeholder="Contoh: Tagihan Listrik Juni">
                                @error('bill_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Diskon</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" wire:model="bill_discount">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required small fw-bold">Jumlah Tagihan (Net)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('bill_amount') is-invalid @enderror" wire:model="bill_amount">
                                </div>
                                @error('bill_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label required">Jatuh Tempo</label>
                                <input type="date" class="form-control @error('bill_due_date') is-invalid @enderror" wire:model="bill_due_date">
                                @error('bill_due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0 mt-3">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeBillModal()">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto" wire:loading.attr="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                                <span wire:loading.remove>Simpan Tagihan</span>
                                <span wire:loading>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
