<div>
    <div class="row mb-3 align-items-center d-print-none">
        <div class="col">
            <h2 class="page-title">Jurnal Umum (General Journal)</h2>
        </div>
        <div class="col-auto ms-auto">
            <button wire:click="openModal()" class="btn btn-primary d-none d-sm-inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="0 0 24 24" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                Input Jurnal Manual
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari No. Jurnal, Keterangan, atau Nama Akun...">
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model.live="filterDateStart" class="form-control" placeholder="Tgl Mulai">
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model.live="filterDateEnd" class="form-control" placeholder="Tgl Selesai">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-bordered">
                <thead>
                    <tr class="bg-light">
                        <th>No. Jurnal / Tgl</th>
                        <th>Keterangan / Transaksi</th>
                        <th>Kode & Nama Akun</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @foreach($entry->items as $index => $item)
                            <tr>
                                @if($index === 0)
                                    <td rowspan="{{ $entry->items->count() }}" class="align-top font-weight-bold">
                                        <code>{{ $entry->entry_number }}</code>
                                        <div class="small text-muted">{{ $entry->entry_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td rowspan="{{ $entry->items->count() }}" class="align-top">
                                        <div class="font-weight-bold">{{ $entry->description }}</div>
                                        @if($entry->reference_type)
                                            <div class="small text-muted">Ref: {{ class_basename($entry->reference_type) }} #{{ $entry->reference_id }}</div>
                                        @endif
                                    </td>
                                @endif
                                <td class="{{ $item->credit > 0 ? 'ps-4' : '' }}">
                                    <code>{{ $item->chartOfAccount ? $item->chartOfAccount->code : '-' }}</code>
                                    <span class="ms-1">{{ $item->chartOfAccount ? $item->chartOfAccount->name : '-' }}</span>
                                    @if($item->memo)
                                        <div class="small text-muted italic">Memo: {{ $item->memo }}</div>
                                    @endif
                                </td>
                                <td class="text-end font-mono">
                                    {{ $item->debit > 0 ? 'Rp ' . number_format($item->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end font-mono">
                                    {{ $item->credit > 0 ? 'Rp ' . number_format($item->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada catatan jurnal umum.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $entries->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

    {{-- Modal Jurnal Manual --}}
    @if($isModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Jurnal Manual (Double-Entry)</h5>
                        <button type="button" class="btn-close" wire:click="closeModal()"></button>
                    </div>
                    <form wire:submit.prevent="saveJournal">
                        <div class="modal-body">
                            @error('general')
                                <div class="alert alert-danger mb-3">{{ $message }}</div>
                            @enderror

                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Tanggal Jurnal</label>
                                    <input type="date" wire:model="entry_date" class="form-control">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label required">Keterangan / Transaksi</label>
                                    <input type="text" wire:model="description" class="form-control" placeholder="Pencatatan penyesuaian / modal awal">
                                </div>
                            </div>

                            <label class="form-label font-weight-bold">Item Jurnal (Minimal 1 Debit & 1 Kredit)</label>
                            @foreach($items as $idx => $item)
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-5">
                                        <select wire:model="items.{{ $idx }}.chart_of_account_id" class="form-select">
                                            <option value="">-- Pilih Akun --</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" wire:model="items.{{ $idx }}.debit" class="form-control" placeholder="Debit (Rp)">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" wire:model="items.{{ $idx }}.credit" class="form-control" placeholder="Kredit (Rp)">
                                    </div>
                                    <div class="col-md-1">
                                        @if(count($items) > 2)
                                            <button type="button" wire:click="removeItem({{ $idx }})" class="btn btn-icon btn-outline-danger btn-sm">&times;</button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <button type="button" wire:click="addItem()" class="btn btn-sm btn-outline-secondary mt-2">
                                + Tambah Baris Akun
                            </button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Jurnal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
