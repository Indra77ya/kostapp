<div>
    <div class="row mb-3 align-items-center d-print-none">
        <div class="col">
            <h2 class="page-title">Bagan Akun (Chart of Accounts)</h2>
        </div>
        <div class="col-auto ms-auto btn-list">
            <div class="btn-group me-2">
                <button type="button" class="btn {{ $viewType === 'tree' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setView('tree')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-git-fork" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M7 6m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 6m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M7 8v2a2 2 0 0 0 2 2h6a2 2 0 0 0 2 -2v-2" /><path d="M12 12v4" /></svg>
                    Tree
                </button>
                <button type="button" class="btn {{ $viewType === 'table' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setView('table')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l11 0" /><path d="M9 12l11 0" /><path d="M9 18l11 0" /><path d="M5 6l0 .01" /><path d="M5 12l0 .01" /><path d="M5 18l0 .01" /></svg>
                    Table
                </button>
            </div>
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

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari Kode, Nama Akun, Sub Tipe, atau Kategori...">
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

    @if($viewType === 'table')
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Tipe Akun</th>
                            <th>Sub Tipe Akun</th>
                            <th>Saldo Normal</th>
                            <th class="text-end">Saldo Saat Ini</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th class="w-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                            <tr>
                                <td class="font-weight-bold"><code>{{ $acc->code }}</code></td>
                                <td>
                                    <div class="{{ $acc->parent_id ? 'ps-3' : '' }}">
                                        @if($acc->parent_id)
                                            <span class="text-muted me-1">↳</span>
                                        @endif
                                        <span class="fw-semibold">{{ $acc->name }}</span>
                                        @if($acc->parent)
                                            <div class="small text-muted"><span class="text-primary me-1">Induk:</span>{{ $acc->parent->code }} - {{ $acc->parent->name }}</div>
                                        @endif
                                    </div>
                                </td>
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
                                <td>
                                    @if($acc->sub_type)
                                        <span class="badge bg-blue-lt">{{ $acc->sub_type }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><span class="text-uppercase small font-weight-bold">{{ $acc->normal_balance }}</span></td>
                                <td class="text-end fw-bold {{ $acc->current_balance < 0 ? 'text-danger' : 'text-dark' }}">
                                    Rp {{ number_format($acc->current_balance ?? 0, 0, ',', '.') }}
                                </td>
                                <td>{{ $acc->category ?: '-' }}</td>
                                <td>
                                    @if($acc->is_active)
                                        <span class="badge bg-success-lt">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-lt">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <button wire:click="openModal({{ $acc->id }})" class="btn btn-white btn-sm">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data bagan akun.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center">
                {{ $accounts->links(data: ['scrollTo' => false]) }}
            </div>
        </div>
    @else
        {{-- Tree View --}}
        <div class="accordion" id="coaAccordion">
            @forelse($groupedAccounts as $typeName => $subTypes)
                <div class="accordion-item mb-3 border rounded">
                    <h2 class="accordion-header" id="heading-{{ Str::slug($typeName) }}">
                        <button class="accordion-button bg-light font-weight-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ Str::slug($typeName) }}" aria-expanded="true">
                            <span class="me-2">📁</span> {{ $typeName }}
                        </button>
                    </h2>
                    <div id="collapse-{{ Str::slug($typeName) }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ Str::slug($typeName) }}">
                        <div class="accordion-body p-0">
                            @foreach($subTypes as $subTypeName => $accList)
                                <div class="p-3 border-bottom bg-white">
                                    <div class="fw-bold mb-2 text-primary small text-uppercase">
                                        <span class="me-1">📂</span> Sub Tipe: {{ $subTypeName }}
                                    </div>
                                    <div class="table-responsive ms-3">
                                        <table class="table table-vcenter table-sm mb-0" style="table-layout: fixed; width: 100%;">
                                            <thead>
                                                <tr class="text-muted">
                                                    <th style="width: 15%;">Kode Akun</th>
                                                    <th style="width: 35%;">Nama Akun</th>
                                                    <th style="width: 15%;">Saldo Normal</th>
                                                    <th style="width: 15%;" class="text-end">Saldo Saat Ini</th>
                                                    <th style="width: 10%;">Status</th>
                                                    <th style="width: 10%;" class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($accList as $acc)
                                                    <tr>
                                                        <td><code>{{ $acc->code }}</code></td>
                                                        <td class="fw-medium text-truncate" title="{{ $acc->name }}">
                                                            <div class="{{ $acc->parent_id ? 'ps-3' : '' }}">
                                                                @if($acc->parent_id)
                                                                    <span class="text-muted me-1">↳</span>
                                                                @endif
                                                                <span>{{ $acc->name }}</span>
                                                                @if($acc->parent)
                                                                    <span class="badge bg-secondary-lt ms-1" style="font-size: 0.7rem;">Induk: {{ $acc->parent->code }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td><span class="text-uppercase small font-weight-bold">{{ $acc->normal_balance }}</span></td>
                                                        <td class="text-end fw-bold {{ $acc->current_balance < 0 ? 'text-danger' : 'text-dark' }}">
                                                            Rp {{ number_format($acc->current_balance ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td>
                                                            @if($acc->is_active)
                                                                <span class="badge bg-success-lt">Aktif</span>
                                                            @else
                                                                <span class="badge bg-secondary-lt">Non-Aktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="btn-list flex-nowrap justify-content-end">
                                                                <button wire:click="openModal({{ $acc->id }})" class="btn btn-white btn-sm">Edit</button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="card p-4 text-center text-muted">
                    Tidak ada data bagan akun untuk ditampilkan dalam bentuk pohon.
                </div>
            @endforelse
        </div>
    @endif

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

                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sub Tipe Akun</label>
                                    <select wire:model="sub_type" class="form-select @error('sub_type') is-invalid @enderror">
                                        <option value="">-- Pilih Sub Tipe --</option>
                                        @foreach($this->availableSubTypes as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                    @error('sub_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Akun Induk (Parent Account)</label>
                                    <select wire:model="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                        <option value="">-- Tanpa Induk (Akun Utama) --</option>
                                        @foreach($parentAccounts as $parentAcc)
                                            <option value="{{ $parentAcc->id }}">{{ $parentAcc->code }} - {{ $parentAcc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
