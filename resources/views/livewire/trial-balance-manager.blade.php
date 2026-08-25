<div>
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Neraca Saldo (Trial Balance)</h2>
                <div class="text-muted mt-1">Ringkasan keseimbangan saldo Debit & Kredit seluruh akun</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="0 0 24 24" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                    Cetak Neraca Saldo
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
                Neraca Saldo Periode:
                <span class="text-primary font-weight-bold">
                    {{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}
                </span>
            </h3>
            <div class="card-actions">
                @if($isBalanced)
                    <span class="badge bg-success-lt fs-6">STATUS: SEIMBANG (BALANCED)</span>
                @else
                    <span class="badge bg-danger-lt fs-6">STATUS: TIDAK SEIMBANG</span>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-bordered table-striped">
                <thead>
                    <tr class="bg-light">
                        <th rowspan="2" class="align-middle text-center">Kode Akun</th>
                        <th rowspan="2" class="align-middle">Nama Akun</th>
                        <th colspan="2" class="text-center">Mutasi Periode</th>
                        <th colspan="2" class="text-center">Saldo Akhir</th>
                    </tr>
                    <tr class="bg-light">
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="text-center font-mono"><code>{{ $row['code'] }}</code></td>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end font-mono">
                                {{ $row['mutation_debit'] > 0 ? 'Rp ' . number_format($row['mutation_debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end font-mono">
                                {{ $row['mutation_credit'] > 0 ? 'Rp ' . number_format($row['mutation_credit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end font-mono font-weight-bold text-success">
                                {{ $row['ending_debit'] > 0 ? 'Rp ' . number_format($row['ending_debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end font-mono font-weight-bold text-primary">
                                {{ $row['ending_credit'] > 0 ? 'Rp ' . number_format($row['ending_credit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi akuntansi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-active font-weight-bold">
                        <td colspan="4" class="text-end">TOTAL SALDO AKHIR:</td>
                        <td class="text-end text-success font-mono">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="text-end text-primary font-mono">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
