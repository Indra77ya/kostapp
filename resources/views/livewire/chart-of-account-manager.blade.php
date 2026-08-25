<div>
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Bagan Akun (Chart of Accounts)</h2>
            </div>
            <div class="col-auto ms-auto d-print-none btn-list">
                <button wire:click="seedDefaultAccounts()" wire:confirm="Muat ulang bagan akun standar?" class="btn btn-outline-secondary d-none d-sm-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="0 0 24 24" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                    Muat Akun Standar
                </button>
                <button wire:click="openModal()" class="btn btn-primary d-none d-sm-inline-block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="0 0 24 24" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Tambah Akun
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari Kode, Nama Akun, atau Kategori...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Semua Tipe Akun</option>
                        <option value="asset">Aset (Asset)</option>
                        <option value="liability">Liabilitas (Liability)</option>
                        <option value="equity">Ekuitas (Equity)</option>
                        <option value="revenue">Pendapatan (Revenue)</option>
                        <option value="expense">Beban (Expense)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Tipe Akun</th>
                        <th>Saldo Normal</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $acc)
                        <tr>
                            <td class="font-weight-bold"><code>{{ $acc->code }}</code></td>
                            <td>{{ $acc->name }}</td>
                            <td>
                                @if($acc->type === 'asset')
                                    <span class="badge bg-primary-lt">Aset</span>
                                @elseif($acc->type === 'liability')
                                    <span class="badge bg-warning-lt">Liabilitas</span>
                                @elseif($acc->type === 'equity')
                                    <span class="badge bg-info-lt">Ekuitas</span>
                                @elseif($acc->type === 'revenue')
                                    <span class="badge bg-success-lt">Pendapatan</span>
                                @else
                                    <span class="badge bg-danger-lt">Beban</span>
                                @endif
                            </td>
                            <td><span class="text-uppercase small font-weight-bold">{{ $acc->normal_balance }}</span></td>
                            <td>{{ $acc->category ?: '-' }}</td>
                            <td>
                                @if($acc->is_active)
                                    <span class="badge bg-success-lt">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <button wire:click="openModal({{ $acc->id }})" class="btn btn-sm btn-outline-primary">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data bagan akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $accounts->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

    {{-- Modal --}}
    @if($isModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $accountId ? 'Edit Bagan Akun' : 'Tambah Bagan Akun Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal()"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Kode Akun</label>
                                    <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror" placeholder="1-1000">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label required">Nama Akun</label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Beban Listrik & Air">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tipe Akun</label>
                                    <select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
                                        <option value="asset">Aset (Asset)</option>
                                        <option value="liability">Liabilitas (Liability)</option>
                                        <option value="equity">Ekuitas (Equity)</option>
                                        <option value="revenue">Pendapatan (Revenue)</option>
                                        <option value="expense">Beban (Expense)</option>
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Saldo Normal</label>
                                    <select wire:model="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror">
                                        <option value="debit">Debit</option>
                                        <option value="credit">Kredit</option>
                                    </select>
                                    @error('normal_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <input type="text" wire:model="category" class="form-control" placeholder="Beban Operasional">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi / Keterangan</label>
                                <textarea wire:model="description" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActiveSwitch">
                                <label class="form-check-label" for="isActiveSwitch">Status Aktif</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
