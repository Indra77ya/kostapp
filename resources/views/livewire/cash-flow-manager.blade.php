<div>
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Arus Kas (Cash Flow)</h2>
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
                Laporan Arus Kas Periode:
                <span class="text-primary font-weight-bold">
                    {{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}
                </span>
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-bordered">
                    <tbody>
                        <tr class="table-info font-weight-bold">
                            <td>SALDO KAS AWAL PERIODE</td>
                            <td class="text-end font-mono">Rp {{ number_format($initialBalance, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="table-success font-weight-bold">
                            <td colspan="2">1. ARUS KAS MASUK (INFLOW)</td>
                        </tr>
                        @forelse($inflowItems as $item)
                            <tr>
                                <td class="ps-4">
                                    {{ $item->journalEntry ? $item->journalEntry->description : 'Penerimaan Kas' }}
                                    <div class="small text-muted">{{ $item->journalEntry ? $item->journalEntry->entry_date->format('d/m/Y') : '' }} | {{ $item->memo }}</div>
                                </td>
                                <td class="text-end font-mono text-success">
                                    Rp {{ number_format($item->debit, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="ps-4 text-muted">Tidak ada arus kas masuk pada periode ini.</td>
                            </tr>
                        @endforelse
                        <tr class="font-weight-bold">
                            <td class="text-end">TOTAL ARUS KAS MASUK:</td>
                            <td class="text-end font-mono text-success fs-5">Rp {{ number_format($totalInflow, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="table-danger font-weight-bold">
                            <td colspan="2">2. ARUS KAS KELUAR (OUTFLOW)</td>
                        </tr>
                        @forelse($outflowItems as $item)
                            <tr>
                                <td class="ps-4">
                                    {{ $item->journalEntry ? $item->journalEntry->description : 'Pengeluaran Kas' }}
                                    <div class="small text-muted">{{ $item->journalEntry ? $item->journalEntry->entry_date->format('d/m/Y') : '' }} | {{ $item->memo }}</div>
                                </td>
                                <td class="text-end font-mono text-danger">
                                    Rp {{ number_format($item->credit, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="ps-4 text-muted">Tidak ada arus kas keluar pada periode ini.</td>
                            </tr>
                        @endforelse
                        <tr class="font-weight-bold">
                            <td class="text-end">TOTAL ARUS KAS KELUAR:</td>
                            <td class="text-end font-mono text-danger fs-5">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="table-active font-weight-bold">
                            <td>KENAIKAN / (PENURUNAN) BERSIH KAS:</td>
                            <td class="text-end font-mono fs-5 {{ $netCashFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="table-primary font-weight-bold fs-4">
                            <td>SALDO KAS AKHIR PERIODE:</td>
                            <td class="text-end font-mono text-primary">
                                Rp {{ number_format($endingBalance, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
