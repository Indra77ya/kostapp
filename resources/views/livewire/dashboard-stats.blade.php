<div>
    @if(auth()->user()->hasRole('tenant'))
        {{-- Tenant Dashboard View --}}
        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $tenantRegistration ? 'Kamar ' . $tenantRegistration->room->room_number : 'Belum Terdaftar' }}
                                </div>
                                <div class="text-secondary">
                                    {{ $tenantRegistration ? $tenantRegistration->location->name : 'Status Hunian Nonaktif' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-danger text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 14l6 0" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    Rp {{ number_format($tenantTotalOutstanding, 0, ',', '.') }}
                                </div>
                                <div class="text-secondary">
                                    {{ $tenantUnpaidBillsCount }} Tagihan Belum Lunas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-success text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    Rp {{ number_format($tenantDepositBalance, 0, ',', '.') }}
                                </div>
                                <div class="text-secondary">
                                    Saldo Deposit Anda
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tenant Quick Action & Tenant Bills --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title">Ringkasan Tagihan Aktif Anda</h3>
                <a href="{{ route('tenant.payments') }}" class="btn btn-primary btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    Kelola & Bayar Tagihan Saya
                </a>
            </div>
            <div class="card-table table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. Tagihan</th>
                            <th>Keterangan</th>
                            <th>Jatuh Tempo</th>
                            <th>Total Tagihan</th>
                            <th>Terbayar</th>
                            <th>Sisa Tagihan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenantBills as $bill)
                            <tr>
                                <td><span class="font-weight-medium">{{ $bill->bill_number }}</span></td>
                                <td>{{ $bill->description }}</td>
                                <td>{{ \Carbon\Carbon::parse($bill->due_date)->format('d/m/Y') }}</td>
                                <td>Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
                                <td class="font-weight-bold text-danger">
                                    Rp {{ number_format(max(0, $bill->amount - $bill->paid_amount), 0, ',', '.') }}
                                </td>
                                <td>
                                    @if($bill->status === 'Lunas')
                                        <span class="badge bg-success text-white">Lunas</span>
                                    @elseif($bill->status === 'Cicilan')
                                        <span class="badge bg-warning text-dark">Cicilan</span>
                                    @else
                                        <span class="badge bg-danger text-white">Belum Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-secondary">
                                    Tidak ada tagihan aktif yang belum lunas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- Admin / Owner Dashboard View --}}
        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-4 col-xl-2-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $totalRooms }} Total Kamar
                                </div>
                                <div class="text-secondary">
                                    {{ $availableRooms }} Tersedia | {{ $occupiedRooms }} Terisi
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4 col-xl-2-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-azure text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M3 12h18" /><path d="M12 3a12 12 0 0 1 0 18" stroke-dasharray="1 2" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    Okupansi {{ $occupancyRate }}%
                                </div>
                                <div class="text-secondary">
                                    {{ $activeTenantsCount }} Penyewa Aktif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4 col-xl-2-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-success text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
                                </div>
                                <div class="text-secondary">
                                    Pendapatan Bulan Ini
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4 col-xl-2-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-warning text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.48 0l-7.1 12.25a2 2 0 0 0 1.74 2.75z" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    Rp {{ number_format($outstandingBillsAmount, 0, ',', '.') }}
                                </div>
                                <div class="text-secondary">
                                    {{ $outstandingBillsCount }} Tagihan Belum Lunas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4 col-xl-2-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-cyan text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 7v5l3 3" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $pendingConfirmationsCount }} Pembayaran
                                </div>
                                <div class="text-secondary">
                                    Menunggu Konfirmasi
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions Bar --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="font-weight-bold text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 2l0 9l5 0l-8 11l0 -9l-5 0z" /></svg>
                        Pintasan Cepat:
                    </div>
                    <div class="btn-list flex-wrap">
                        <a href="{{ route('registrations.index') }}" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 11h6m-3 -3v6" /></svg>
                            Pendaftaran Baru
                        </a>
                        <a href="{{ route('payments.confirmation') }}" class="btn btn-warning btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                            Konfirmasi Pembayaran
                            @if($pendingConfirmationsCount > 0)
                                <span class="badge bg-white text-warning ms-1">{{ $pendingConfirmationsCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('payments.index') }}" class="btn btn-success btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                            Catat Pembayaran
                        </a>
                        @if(auth()->user()->hasAnyRole(['owner', 'developer']))
                            <a href="{{ route('accounting.expenses') }}" class="btn btn-outline-danger btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 14h6" /></svg>
                                Catat Pengeluaran
                            </a>
                            <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                                Kelola Kamar
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Tables Section --}}
        <div class="row row-cards">
            {{-- Pending Confirmations --}}
            <div class="col-lg-6">
                <div class="card card-table">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-warning me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 7v5l3 3" /></svg>
                            Konfirmasi Pembayaran Pending
                        </h3>
                        <a href="{{ route('payments.confirmation') }}" class="btn btn-link btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Penyewa / Kamar</th>
                                    <th>Nominal</th>
                                    <th>Tgl Bayar</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingPayments as $payment)
                                    <tr>
                                        <td>
                                            <div class="font-weight-medium">{{ $payment->registration->user->name ?? '-' }}</div>
                                            <div class="text-secondary small">
                                                Kamar {{ $payment->registration->room->room_number ?? '-' }} ({{ $payment->registration->location->name ?? '-' }})
                                            </div>
                                        </td>
                                        <td class="font-weight-bold text-success">
                                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="small">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-list flex-nowrap justify-content-end">
                                                <button wire:click="approvePayment({{ $payment->id }})" wire:confirm="Setujui pembayaran ini?" class="btn btn-success btn-sm p-1" title="Setujui">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon m-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                                </button>
                                                <button wire:click="rejectPayment({{ $payment->id }})" wire:confirm="Tolak dan hapus pembayaran ini?" class="btn btn-danger btn-sm p-1" title="Tolak">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon m-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">
                                            Tidak ada pembayaran yang menunggu konfirmasi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Upcoming / Overdue Bills --}}
            <div class="col-lg-6">
                <div class="card card-table">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 14l6 0" /></svg>
                            Tagihan Belum Lunas Terdekat
                        </h3>
                        <a href="{{ route('payments.index') }}" class="btn btn-link btn-sm">Kelola Tagihan</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Penyewa / Kamar</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Sisa Tagihan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingBills as $bill)
                                    <tr>
                                        <td>
                                            <div class="font-weight-medium">{{ $bill->registration->user->name ?? '-' }}</div>
                                            <div class="text-secondary small">
                                                Kamar {{ $bill->registration->room->room_number ?? '-' }} ({{ $bill->registration->location->name ?? '-' }})
                                            </div>
                                        </td>
                                        <td class="small">
                                            @php
                                                $dueDate = \Carbon\Carbon::parse($bill->due_date);
                                                $isOverdue = $dueDate->isPast() && !$dueDate->isToday();
                                            @endphp
                                            <span class="{{ $isOverdue ? 'text-danger font-weight-bold' : '' }}">
                                                {{ $dueDate->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="font-weight-bold text-danger">
                                            Rp {{ number_format(max(0, $bill->amount - $bill->paid_amount), 0, ',', '.') }}
                                        </td>
                                        <td>
                                            @if($bill->status === 'Cicilan')
                                                <span class="badge bg-warning text-dark">Cicilan</span>
                                            @else
                                                <span class="badge bg-danger text-white">Belum Lunas</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-secondary">
                                            Semua tagihan tergolong lunas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
