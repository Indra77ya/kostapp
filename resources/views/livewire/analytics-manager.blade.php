<div>
    <!-- Top Filter Controls -->
    <div class="row mb-3 align-items-center d-print-none">
        <div class="col">
            <h2 class="page-title">
                Analisis &amp; Laporan Eksekutif
            </h2>
            <div class="text-secondary mt-1">
                Ikhtisar performa bisnis, tingkat okupansi, pendapatan, dan analisis tunggakan
            </div>
        </div>
        <div class="col-auto ms-auto d-flex gap-2">
            <div>
                <select wire:model.live="location_id" class="form-select">
                    <option value="all">Semua Lokasi Kost</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="period_type" class="form-select">
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                    <option value="all">Semua Periode</option>
                </select>
            </div>
            @if($period_type === 'monthly')
            <div>
                <select wire:model.live="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            @endif
            @if($period_type !== 'all')
            <div>
                <select wire:model.live="year" class="form-select">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            @endif
            <button onclick="window.print()" class="btn btn-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                Cetak / PDF
            </button>
        </div>
    </div>

    <!-- Executive KPI Cards -->
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
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
                                Okupansi: {{ $occupancyRate }}%
                            </div>
                            <div class="text-secondary small">
                                {{ $occupiedRooms }} / {{ $totalRooms }} Kamar Terisi ({{ $availableRooms }} Kosong)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-green text-white avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-currency-dollar" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2" /><path d="M12 3v3m0 12v3" /></svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">
                                Rp {{ number_format($revenueRealized, 0, ',', '.') }}
                            </div>
                            <div class="text-secondary small">
                                Realisasi Pendapatan (Diterima)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-warning text-white avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium text-warning">
                                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                            </div>
                            <div class="text-secondary small">
                                Total Tunggakan (Outstanding)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-teal text-white avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M4 20l14 0" /></svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium {{ $netOperatingIncome >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($netOperatingIncome, 0, ',', '.') }}
                            </div>
                            <div class="text-secondary small">
                                Pendapatan Bersih Operasional
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-4">
        <!-- Breakdown per Lokasi -->
        <div class="col-lg-7">
            <div class="card overflow-hidden h-100">
                <div class="card-header">
                    <h3 class="card-title">Ikhtisar Per Lokasi Properti</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Lokasi</th>
                                <th class="text-center">Total Kamar</th>
                                <th class="text-center">Terisi</th>
                                <th class="text-center">Okupansi</th>
                                <th class="text-end">Pendapatan</th>
                                <th class="text-end">Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locationBreakdown as $row)
                            <tr>
                                <td>
                                    <div class="font-weight-medium">{{ $row['location']->name }}</div>
                                    <div class="text-secondary small">{{ $row['location']->address }}</div>
                                </td>
                                <td class="text-center">{{ $row['total_rooms'] }}</td>
                                <td class="text-center">{{ $row['occupied_rooms'] }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $row['occupancy_rate'] >= 80 ? 'bg-success-lt' : ($row['occupancy_rate'] >= 50 ? 'bg-warning-lt' : 'bg-danger-lt') }}">
                                        {{ $row['occupancy_rate'] }}%
                                    </span>
                                </td>
                                <td class="text-end text-success font-weight-medium">
                                    Rp {{ number_format($row['revenue'], 0, ',', '.') }}
                                </td>
                                <td class="text-end text-warning font-weight-medium">
                                    Rp {{ number_format($row['outstanding'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-3">Belum ada data lokasi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Executive Summary Stats -->
        <div class="col-lg-5">
            <div class="card overflow-hidden h-100">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Keuangan Periode Ini</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Realisasi Pendapatan:</span>
                            <span class="font-weight-bold text-success">Rp {{ number_format($revenueRealized, 0, ',', '.') }}</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Pengeluaran Operasional:</span>
                            <span class="font-weight-bold text-danger">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                        </div>
                        @php
                            $expRatio = $revenueRealized > 0 ? min(100, round(($totalExpenses / $revenueRealized) * 100)) : 0;
                        @endphp
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-danger" style="width: {{ $expRatio }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Total Tagihan Diterbitkan:</span>
                            <span class="font-weight-bold">Rp {{ number_format($totalBillsAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="font-weight-medium">Pendapatan Operasional Bersih</div>
                                <div class="text-secondary small">Pendapatan Diterima - Pengeluaran</div>
                            </div>
                            <div class="h3 mb-0 {{ $netOperatingIncome >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($netOperatingIncome, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend Bar Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h3 class="card-title">Tren Bulanan Pendapatan &amp; Pengeluaran (Tahun {{ $year }})</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-end">Pendapatan Diterima</th>
                                <th class="text-end">Pengeluaran Operasional</th>
                                <th class="text-end">Laba / (Rugi) Operasional</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyTrend as $trend)
                            <tr>
                                <td class="font-weight-medium">{{ $trend['month'] }}</td>
                                <td class="text-end text-success">Rp {{ number_format($trend['revenue'], 0, ',', '.') }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($trend['expense'], 0, ',', '.') }}</td>
                                <td class="text-end font-weight-bold {{ $trend['net'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                    Rp {{ number_format($trend['net'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Outstanding / Tunggakan Tagihan -->
    <div class="row">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Rincian Tunggakan Tagihan (Outstanding Bills)</h3>
                        <div class="text-secondary small">Daftar tagihan yang belum lunas atau menunggak</div>
                    </div>
                    <span class="badge bg-warning text-dark">{{ $outstandingBillsList->count() }} Tagihan Pending</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>No. Tagihan</th>
                                <th>Penghuni</th>
                                <th>Kamar / Lokasi</th>
                                <th>Jatuh Tempo</th>
                                <th>Status Overdue</th>
                                <th class="text-end">Total Tagihan</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-end">Sisa Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingBillsList as $bill)
                            @php
                                $isOverdue = $bill->due_date && Carbon\Carbon::parse($bill->due_date)->isPast() && !$bill->due_date->isToday();
                                $daysOverdue = $isOverdue ? Carbon\Carbon::parse($bill->due_date)->diffInDays(now()) : 0;
                            @endphp
                            <tr>
                                <td class="font-weight-medium">{{ $bill->bill_number }}</td>
                                <td>{{ $bill->registration->user->name ?? '-' }}</td>
                                <td>
                                    <div>Kamar {{ $bill->registration->room->room_number ?? '-' }}</div>
                                    <div class="text-secondary small">{{ $bill->registration->room->location->name ?? '-' }}</div>
                                </td>
                                <td>{{ $bill->due_date ? $bill->due_date->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($isOverdue)
                                        <span class="badge bg-danger">Telat {{ $daysOverdue }} Hari</span>
                                    @else
                                        <span class="badge bg-info">Belum Jatuh Tempo</span>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                                <td class="text-end text-success">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
                                <td class="text-end font-weight-bold text-danger">Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-3">Tidak ada tunggakan tagihan untuk periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="7" class="text-end">Total Sisa Tunggakan:</td>
                                <td class="text-end text-danger h4 mb-0">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
