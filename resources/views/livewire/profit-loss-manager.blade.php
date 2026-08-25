<div>
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Laba Rugi (Profit & Loss Statement)</h2>
                <div class="text-muted mt-1">Laporan Keuangan Format Skontro</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="0 0 24 24" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                    Cetak Laporan
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3 d-print-none">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label">Tipe Periode</label>
                    <select wire:model.live="periodType" class="form-select">
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                        <option value="custom">Rentang Tanggal Custom</option>
                    </select>
                </div>

                @if($periodType === 'monthly')
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select wire:model.live="month" class="form-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select wire:model.live="year" class="form-select">
                            @foreach(range(now()->year - 5, now()->year + 1) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($periodType === 'yearly')
                    <div class="col-md-4">
                        <label class="form-label">Tahun</label>
                        <select wire:model.live="year" class="form-select">
                            @foreach(range(now()->year - 5, now()->year + 1) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" wire:model.live="dateStart" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" wire:model.live="dateEnd" class="form-control">
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h3 class="card-title">
                Laporan Laba Rugi Format Skontro:
                <span class="text-primary font-weight-bold">
                    {{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}
                </span>
            </h3>
        </div>
        <div class="card-body p-0">
            {{-- Skontro Layout (2 Column T-Account View) --}}
            <div class="row g-0">
                {{-- Sisi Kiri: Pendapatan --}}
                <div class="col-md-6 border-end">
                    <div class="bg-success-lt p-2 border-bottom font-weight-bold text-center">
                        PENDAPATAN (REVENUE)
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped card-table">
                            <thead>
                                <tr>
                                    <th>Kode & Nama Akun</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenues as $rev)
                                    <tr>
                                        <td>
                                            <code>{{ $rev['code'] }}</code> {{ $rev['name'] }}
                                        </td>
                                        <td class="text-end font-mono text-success">
                                            Rp {{ number_format($rev['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted">Tidak ada pendapatan pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-active font-weight-bold">
                                    <td>TOTAL PENDAPATAN:</td>
                                    <td class="text-end font-mono text-success fs-5">
                                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Sisi Kanan: Beban & Pengeluaran --}}
                <div class="col-md-6">
                    <div class="bg-danger-lt p-2 border-bottom font-weight-bold text-center">
                        BEBAN & OPERASIONAL (EXPENSES)
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped card-table">
                            <thead>
                                <tr>
                                    <th>Kode & Nama Akun</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $exp)
                                    <tr>
                                        <td>
                                            <code>{{ $exp['code'] }}</code> {{ $exp['name'] }}
                                        </td>
                                        <td class="text-end font-mono text-danger">
                                            Rp {{ number_format($exp['amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted">Tidak ada beban operasional pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-active font-weight-bold">
                                    <td>TOTAL BEBAN & OPERASIONAL:</td>
                                    <td class="text-end font-mono text-danger fs-5">
                                        Rp {{ number_format($totalExpense, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fs-3 font-weight-bold">
                    HASIL OPERASIONAL (LABA / RUGI BERSIH):
                </div>
                <div class="fs-2 font-weight-bold font-mono {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $netProfit >= 0 ? 'LABA BERSIH: Rp ' . number_format($netProfit, 0, ',', '.') : 'RUGI BERSIH: Rp ' . number_format(abs($netProfit), 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>
