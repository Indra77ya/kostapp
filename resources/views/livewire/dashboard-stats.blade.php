<div>
    @if(auth()->user()->hasRole('tenant'))
        {{-- Tenant Dashboard View --}}
        @if($tenantPendingConfirmations > 0)
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.48 0l-7.1 12.25a2 2 0 0 0 1.74 2.75z" /></svg>
                <div>
                    <strong>Konfirmasi Pembayaran Diproses:</strong> Anda memiliki <strong>{{ $tenantPendingConfirmations }}</strong> pembayaran yang sedang menunggu konfirmasi admin.
                </div>
            </div>
        @endif

        @if($tenantRegistration)
            <div class="card mb-3 bg-primary-lt border-0">
                <div class="card-body p-3">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <div class="text-secondary small font-weight-bold text-uppercase">Lokasi & Kamar</div>
                            <div class="h4 mb-0 text-primary">Kamar {{ $tenantRegistration->room->room_number }}</div>
                            <div class="small text-secondary">{{ $tenantRegistration->location->name }}</div>
                        </div>
                        <div class="col-md-3 border-start-md">
                            <div class="text-secondary small font-weight-bold text-uppercase">Mulai Sewa</div>
                            <div class="h4 mb-0 text-dark">{{ \Carbon\Carbon::parse($tenantRegistration->stay_start_date)->format('d F Y') }}</div>
                            <div class="small text-secondary">Tipe: {{ ucfirst($tenantRegistration->duration_type) }} {{ $tenantRegistration->is_open_ended ? '(Berlanjut)' : '' }}</div>
                        </div>
                        <div class="col-md-3 border-start-md">
                            <div class="text-secondary small font-weight-bold text-uppercase">Total Tagihan Belum Lunas</div>
                            <div class="h4 mb-0 text-danger">Rp {{ number_format($tenantTotalOutstanding, 0, ',', '.') }}</div>
                            <div class="small text-secondary">{{ $tenantUnpaidBillsCount }} Tagihan Aktif</div>
                        </div>
                        <div class="col-md-3 border-start-md">
                            <div class="text-secondary small font-weight-bold text-uppercase">Saldo Deposit</div>
                            <div class="h4 mb-0 text-success">Rp {{ number_format($tenantDepositBalance, 0, ',', '.') }}</div>
                            <div class="small text-secondary">Saldo Tersimpan</div>
                        </div>
                    </div>
                </div>
            </div>
        @else
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
                                    <div class="font-weight-medium">Belum Terdaftar</div>
                                    <div class="text-secondary">Status Hunian Nonaktif</div>
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
                                    <div class="font-weight-medium">Rp {{ number_format($tenantTotalOutstanding, 0, ',', '.') }}</div>
                                    <div class="text-secondary">{{ $tenantUnpaidBillsCount }} Tagihan Belum Lunas</div>
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
                                    <div class="font-weight-medium">Rp {{ number_format($tenantDepositBalance, 0, ',', '.') }}</div>
                                    <div class="text-secondary">Saldo Deposit Anda</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
        @if($locations->count() > 0)
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <div class="text-secondary small font-weight-bold text-uppercase">Filter Lokasi Kost:</div>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
                        </span>
                        <select class="form-select font-weight-bold" wire:model.live="selectedLocationId">
                            <option value="">Semua Lokasi ({{ $locations->count() }} Lokasi)</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endif

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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
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

        {{-- Visual Room Map Section --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                    Peta Status Kamar Real-time
                </h3>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="badge bg-success text-white">Tersedia</span>
                    <span class="badge bg-primary text-white">Terisi</span>
                    <span class="badge bg-secondary text-white">Maintenance</span>
                </div>
            </div>
            <div class="card-body">
                @forelse($roomsMap as $locationName => $roomsList)
                    <div class="mb-3">
                        <div class="font-weight-bold text-secondary mb-2 small text-uppercase d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin me-1" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
                            {{ $locationName }} ({{ $roomsList->count() }} Kamar)
                        </div>
                        <div class="row g-2">
                            @foreach($roomsList as $rm)
                                @php
                                    $activeReg = $rm->registrations->first();
                                @endphp
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    @if($rm->status === 'available')
                                        <div class="card card-sm border-success bg-success-lt hover-shadow cursor-pointer transition-all h-100">
                                            <div class="card-body p-2 text-center">
                                                <div class="badge bg-success text-white mb-1">Tersedia</div>
                                                <div class="font-weight-bold h4 mb-0 text-dark">Kamar {{ $rm->room_number }}</div>
                                                <div class="text-secondary x-small mb-2">Rp {{ number_format($rm->price_monthly, 0, ',', '.') }}/bln</div>
                                                <a href="{{ route('registrations.index', ['room_id' => $rm->id]) }}" class="btn btn-success btn-xs w-100 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-plus" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 11h6m-3 -3v6" /></svg>
                                                    + Daftar
                                                </a>
                                            </div>
                                        </div>
                                    @elseif($rm->status === 'occupied')
                                        <div wire:click="showOccupiedRoomDetail({{ $rm->id }})" class="card card-sm border-primary bg-primary-lt hover-shadow cursor-pointer transition-all h-100">
                                            <div class="card-body p-2 text-center">
                                                <div class="badge bg-primary text-white mb-1">Terisi</div>
                                                <div class="font-weight-bold h4 mb-0 text-dark">Kamar {{ $rm->room_number }}</div>
                                                <div class="text-primary font-weight-medium small text-truncate mt-1" title="{{ $activeReg->user->name ?? '-' }}">
                                                    {{ $activeReg->user->name ?? 'Terisi' }}
                                                </div>
                                                <div class="text-secondary x-small">Klik untuk Detail</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="card card-sm border-secondary bg-light h-100">
                                            <div class="card-body p-2 text-center">
                                                <div class="badge bg-secondary text-white mb-1">Maintenance</div>
                                                <div class="font-weight-bold h4 mb-0 text-secondary">Kamar {{ $rm->room_number }}</div>
                                                <div class="text-secondary x-small">Tidak Aktif</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-secondary">
                        Belum ada kamar yang terdaftar.
                    </div>
                @endforelse
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
                                                <button wire:click="showPaymentDetail({{ $payment->id }})" class="btn btn-white btn-sm p-1" title="Lihat Detail">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye m-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                                </button>
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

        {{-- Payment Detail Modal --}}
        <div class="modal modal-blur fade {{ $isDetailModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isDetailModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    @if($selectedPayment)
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Konfirmasi Pembayaran</h5>
                            <button type="button" class="btn-close" wire:click="closeDetailModal()"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Penghuni</label>
                                    <div class="h3 mb-0">{{ $selectedPayment->registration->user->name ?? '-' }}</div>
                                    <div class="text-secondary small">Kamar {{ $selectedPayment->registration->room->room_number ?? '-' }} - {{ $selectedPayment->registration->location->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">No. Pembayaran</label>
                                    <div class="h3 mb-0">{{ $selectedPayment->payment_number }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Tanggal Bayar</label>
                                    <div class="h4 mb-0">{{ \Carbon\Carbon::parse($selectedPayment->payment_date)->format('d F Y') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Jumlah Bayar</label>
                                    <div class="h2 text-primary mb-0">Rp {{ number_format($selectedPayment->amount, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Metode Pembayaran</label>
                                    <div class="h4 mb-0">{{ $selectedPayment->paymentMethod->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small text-uppercase fw-bold">Peruntukan Tagihan</label>
                                    <div class="h4 mb-0">{{ $selectedPayment->bill ? $selectedPayment->bill->description : 'Pembayaran Umum' }}</div>
                                </div>

                                @if($selectedPayment->sender_bank_name)
                                    <div class="col-12">
                                        <div class="card bg-azure-lt border-0">
                                            <div class="card-body p-3">
                                                @php
                                                    $pmCategory = $selectedPayment->paymentMethod->category ?? '';
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
                                    @php
                                        $projectedRemaining = $selectedPayment->bill->remaining_amount - $selectedPayment->amount;
                                    @endphp
                                    @if($projectedRemaining < 0)
                                        <div class="col-12">
                                            <div class="alert alert-info py-2 mb-2">
                                                <div class="d-flex">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                                                    </div>
                                                    <div class="small">
                                                        Menyetujui pembayaran ini akan menghasilkan kelebihan bayar sebesar <strong>Rp {{ number_format(abs($projectedRemaining), 0, ',', '.') }}</strong> yang akan otomatis tercatat sebagai Saldo Deposit penghuni.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
                                                     style="max-height: 350px;"
                                                     title="Klik untuk memperbesar (Tab Baru)">
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning py-2">Tidak ada bukti pembayaran yang diunggah.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeDetailModal()">Tutup</button>
                            <div class="ms-auto btn-list">
                                <button type="button" class="btn btn-danger" wire:click="rejectPayment({{ $selectedPayment->id }})" wire:confirm="Tolak dan hapus pembayaran ini?">Tolak</button>
                                <button type="button" class="btn btn-success" wire:click="approvePayment({{ $selectedPayment->id }})" wire:confirm="Setujui pembayaran ini?">Setujui Pembayaran</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Occupied Room Detail Modal --}}
        <div class="modal modal-blur fade {{ $isOccupiedRoomModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isOccupiedRoomModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    @if($selectedOccupiedRoom)
                        @php
                            $activeReg = $selectedOccupiedRoom->registrations->first();
                            $unpaidBills = $activeReg ? $activeReg->bills->whereIn('status', ['Belum Lunas', 'Cicilan']) : collect();
                            $totalUnpaid = $unpaidBills->sum(function($b) { return max(0, $b->amount - $b->paid_amount); });
                        @endphp
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                                Informasi Kamar {{ $selectedOccupiedRoom->room_number }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeOccupiedRoomModal()"></button>
                        </div>
                        <div class="modal-body">
                            <div class="card bg-primary-lt border-0 mb-3">
                                <div class="card-body p-3">
                                    <div class="text-secondary small text-uppercase font-weight-bold">Lokasi</div>
                                    <div class="h4 mb-0">{{ $selectedOccupiedRoom->location->name ?? '-' }}</div>
                                </div>
                            </div>

                            @if($activeReg)
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label text-secondary small text-uppercase fw-bold mb-1">Nama Penghuni</label>
                                        <div class="h3 text-primary mb-0">{{ $activeReg->user->name ?? '-' }}</div>
                                        <div class="text-secondary small">{{ $activeReg->user->phone_number ?? 'No HP tidak tersedia' }}</div>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label text-secondary small text-uppercase fw-bold mb-1">Tgl Mulai Sewa</label>
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($activeReg->stay_start_date)->format('d F Y') }}</div>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label text-secondary small text-uppercase fw-bold mb-1">Durasi Sewa</label>
                                        <div class="fw-bold">{{ $activeReg->duration_value }} {{ ucfirst($activeReg->duration_type) }}</div>
                                    </div>

                                    <div class="col-12">
                                        <div class="p-3 rounded bg-light border">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="text-secondary small font-weight-bold">Status Tagihan Aktif:</span>
                                                @if($totalUnpaid > 0)
                                                    <span class="badge bg-danger text-white">Tunggakan: Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="badge bg-success text-white">Lunas</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info mb-0">Tidak ada pendaftaran aktif pada kamar ini.</div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeOccupiedRoomModal()">Tutup</button>
                            <a href="{{ route('registrations.index') }}" class="btn btn-primary ms-auto">
                                Kelola Check-in
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
