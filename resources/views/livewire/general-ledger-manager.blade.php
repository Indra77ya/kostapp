<div>
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Buku Besar (General Ledger)</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="0 0 24 24" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                    Cetak Buku Besar
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3 d-print-none">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label">Pilih Akun</label>
                    <select wire:model.live="selectedAccountId" class="form-select">
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" wire:model.live="filterDateStart" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" wire:model.live="filterDateEnd" class="form-control">
                </div>
            </div>
        </div>
    </div>

    @if($account)
        <div class="card">
            <div class="card-header bg-light">
                <h3 class="card-title">
                    Buku Besar: <code>{{ $account->code }}</code> - {{ $account->name }}
                </h3>
                <div class="card-actions">
                    <span class="badge bg-blue-lt">Saldo Normal: {{ strtoupper($account->normal_balance) }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Jurnal</th>
                            <th>Keterangan</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Kredit</th>
                            <th class="text-end">Saldo Akumulasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-info font-weight-bold">
                            <td colspan="3">SALDO AWAL PERIODE</td>
                            <td class="text-end">-</td>
                            <td class="text-end">-</td>
                            <td class="text-end">Rp {{ number_format($openingBalance, 0, ',', '.') }}</td>
                        </tr>

                        @php $runningBalance = $openingBalance; @endphp
                        @forelse($items as $item)
                            @php
                                if ($account->normal_balance === 'debit') {
                                    $runningBalance += ($item->debit - $item->credit);
                                } else {
                                    $runningBalance += ($item->credit - $item->debit);
                                }
                            @endphp
                            <tr>
                                <td>{{ $item->journalEntry ? $item->journalEntry->entry_date->format('d/m/Y') : '-' }}</td>
                                <td><code>{{ $item->journalEntry ? $item->journalEntry->entry_number : '-' }}</code></td>
                                <td>
                                    {{ $item->journalEntry ? $item->journalEntry->description : '-' }}
                                    @if($item->memo) <span class="small text-muted italic">({{ $item->memo }})</span> @endif
                                </td>
                                <td class="text-end text-success font-mono">
                                    {{ $item->debit > 0 ? 'Rp ' . number_format($item->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end text-danger font-mono">
                                    {{ $item->credit > 0 ? 'Rp ' . number_format($item->credit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end font-weight-bold font-mono">
                                    Rp {{ number_format($runningBalance, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Tidak ada mutasi transaksi untuk akun ini pada periode terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($items, 'links'))
                <div class="card-footer d-flex align-items-center d-print-none">
                    {{ $items->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-info text-center py-4">
            Silakan pilih akun terlebih dahulu untuk melihat histori Buku Besar.
        </div>
    @endif
</div>
