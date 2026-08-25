<div>
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Pengeluaran Operasional</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button wire:click="openModal()" class="btn btn-primary d-none d-sm-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="0 0 24 24" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Catat Pengeluaran
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari No. Transaksi, Judul, Keterangan...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filterAccountId" class="form-select">
                        <option value="">Semua Akun Beban</option>
                        @foreach($expenseAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" wire:model.live="filterDateStart" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" wire:model.live="filterDateEnd" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Akun Beban</th>
                        <th>Judul / Keterangan</th>
                        <th>Jumlah</th>
                        <th>Metode Bayar</th>
                        <th>Nota / Bukti</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        <tr>
                            <td><code>{{ $exp->expense_number }}</code></td>
                            <td>{{ $exp->expense_date ? $exp->expense_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="badge bg-danger-lt me-1">{{ $exp->account ? $exp->account->code : '-' }}</span>
                                {{ $exp->account ? $exp->account->name : '-' }}
                            </td>
                            <td class="font-weight-bold">{{ $exp->title }}</td>
                            <td class="text-danger font-weight-bold">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                            <td>{{ $exp->paymentMethod ? $exp->paymentMethod->name : 'Kas Utama' }}</td>
                            <td>
                                @if($exp->attachment_path)
                                    <a href="{{ Storage::url($exp->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        Lihat Nota
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button wire:click="openModal({{ $exp->id }})" class="btn btn-sm btn-outline-primary">Edit</button>
                                    <button wire:click="delete({{ $exp->id }})" wire:confirm="Yakin ingin menghapus pengeluaran ini?" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada catatan pengeluaran operasional.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $expenses->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

    {{-- Modal --}}
    @if($isModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $expenseId ? 'Edit Pengeluaran Operasional' : 'Catat Pengeluaran Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal()"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Transaksi Pengeluaran</label>
                                    <input type="text" wire:model="expense_number" class="form-control @error('expense_number') is-invalid @enderror" readonly>
                                    @error('expense_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Transaksi</label>
                                    <input type="date" wire:model="expense_date" class="form-control @error('expense_date') is-invalid @enderror">
                                    @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Akun Beban / Kategori</label>
                                    <select wire:model="chart_of_account_id" class="form-select @error('chart_of_account_id') is-invalid @enderror">
                                        <option value="">-- Pilih Akun Beban --</option>
                                        @foreach($expenseAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('chart_of_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sumber Kas / Metode Bayar</label>
                                    <select wire:model="payment_method_id" class="form-select @error('payment_method_id') is-invalid @enderror">
                                        <option value="">Kas Utama / Tunai</option>
                                        @foreach($paymentMethods as $pm)
                                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label required">Judul Pengeluaran</label>
                                    <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror" placeholder="Bayar Token PLN Gedung A">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Nominal (Rp)</label>
                                    <input type="number" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="500000">
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan Tambahan</label>
                                <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unggah Nota / Bukti Pembayaran (JPG/PNG/PDF, Max 3MB)</label>
                                <input type="file" wire:model="attachment" class="form-control @error('attachment') is-invalid @enderror">
                                @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan & Posting Jurnal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
