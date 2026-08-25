<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Metode Pembayaran</h2>
        </div>
        <div class="col-12 col-md-auto ms-md-auto">
            <div class="btn-list justify-content-md-end">
                <div class="btn-group">
                    <button type="button" class="btn {{ $viewType === 'grid' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setView('grid')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-grid" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M14 4m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M4 14m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M14 14m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
                        Grid
                    </button>
                    <button type="button" class="btn {{ $viewType === 'table' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setView('table')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l11 0" /><path d="M9 12l11 0" /><path d="M9 18l11 0" /><path d="M5 6l0 .01" /><path d="M5 12l0 .01" /><path d="M5 18l0 .01" /></svg>
                        Table
                    </button>
                </div>
                <button class="btn btn-success" wire:click="openModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    Tambah Metode Pembayaran
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama, nomor rekening, atau pemilik..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non-aktif</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-icon w-100" title="Reset Filter" wire:click="resetFilters">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-rotate" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-secondary small">
            Menampilkan {{ $paymentMethods->firstItem() }} sampai {{ $paymentMethods->lastItem() }} dari {{ $paymentMethods->total() }} metode pembayaran
        </div>
        <div>
            {{$paymentMethods->links(data: ['scrollTo' => false])}}
        </div>
    </div>

    @if($viewType === 'grid')
    <div class="row row-cards">
        @forelse($paymentMethods as $pm)
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            @if($pm->logo)
                                <span class="avatar avatar-md" style="background-image: url({{ asset('storage/' . $pm->logo) }})"></span>
                            @else
                                <span class="avatar avatar-md"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg></span>
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold">{{ $pm->name }}</div>
                            <div class="text-secondary small">
                                <span class="badge bg-blue-lt">{{ $pm->category }}</span>
                            </div>
                        </div>
                        <div class="ms-auto">
                            <label class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" {{ $pm->is_active ? 'checked' : '' }} wire:click="toggleStatus({{ $pm->id }})">
                            </label>
                        </div>
                    </div>
                    <div class="mt-3">
                        @if($pm->account_number)
                        <div class="text-secondary small">No. Rekening:</div>
                        <div class="fw-bold">{{ $pm->account_number }}</div>
                        @endif
                        @if($pm->account_name)
                        <div class="text-secondary small mt-2">Atas Nama:</div>
                        <div class="fw-bold">{{ $pm->account_name }}</div>
                        @endif
                        <div class="text-secondary small mt-2">Akun Akuntansi:</div>
                        <div class="fw-bold text-primary">
                            @if($pm->account)
                                {{ $pm->account->code }} - {{ $pm->account->name }}
                            @else
                                <span class="text-danger small">Belum terhubung</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" wire:click="openModal({{ $pm->id }})">Edit</button>
                        <button class="btn btn-outline-danger btn-sm flex-fill" wire:click="deletePaymentMethod({{ $pm->id }})" wire:confirm="Yakin ingin menghapus metode pembayaran ini?">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card card-md">
                <div class="card-body text-center py-4">
                    <div class="text-secondary mb-3">Tidak ada metode pembayaran yang sesuai dengan kriteria pencarian.</div>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    @else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Akun Akuntansi</th>
                        <th>No. Rekening</th>
                        <th>Atas Nama</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentMethods as $pm)
                    <tr>
                        <td>
                            @if($pm->logo)
                                <span class="avatar avatar-sm" style="background-image: url({{ asset('storage/' . $pm->logo) }})"></span>
                            @else
                                <span class="avatar avatar-sm"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg></span>
                            @endif
                        </td>
                        <td><div class="fw-bold">{{ $pm->name }}</div></td>
                        <td><span class="badge bg-blue-lt">{{ $pm->category }}</span></td>
                        <td>
                            @if($pm->account)
                                <span class="badge bg-purple-lt">{{ $pm->account->code }} - {{ $pm->account->name }}</span>
                            @else
                                <span class="badge bg-danger-lt">Belum terhubung</span>
                            @endif
                        </td>
                        <td>{{ $pm->account_number ?: '-' }}</td>
                        <td>{{ $pm->account_name ?: '-' }}</td>
                        <td>
                            <label class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" {{ $pm->is_active ? 'checked' : '' }} wire:click="toggleStatus({{ $pm->id }})">
                            </label>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $pm->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deletePaymentMethod({{ $pm->id }})" wire:confirm="Yakin ingin menghapus metode pembayaran ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">
                            Tidak ada metode pembayaran yang sesuai dengan kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Modal -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $paymentMethodId ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="savePaymentMethod">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3 text-center">
                                    <label class="form-label">Logo / Gambar</label>
                                    @if($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="img-fluid rounded border mb-2" style="max-height: 100px;">
                                    @elseif($oldLogo)
                                        <img src="{{ asset('storage/' . $oldLogo) }}" class="img-fluid rounded border mb-2" style="max-height: 100px;">
                                    @else
                                        <div class="border rounded d-flex align-items-center justify-content-center mb-2" style="height: 100px; background: var(--tblr-bg-surface-secondary);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo text-secondary" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" wire:model="logo">
                                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="small text-secondary mt-1">Maks. 1MB (JPG, PNG, WEBP)</div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Metode Pembayaran</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Transfer BCA">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <input type="text" class="form-control @error('category') is-invalid @enderror" wire:model="category" list="category-options" placeholder="Pilih atau ketik kategori">
                                    <datalist id="category-options">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">
                                        @endforeach
                                    </datalist>
                                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Akun Akuntansi (Chart of Accounts)</label>
                                    <select class="form-select @error('chart_of_account_id') is-invalid @enderror" wire:model="chart_of_account_id">
                                        <option value="">-- Pilih Akun COA --</option>
                                        @foreach($chartOfAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->category }})</option>
                                        @endforeach
                                    </select>
                                    @error('chart_of_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Rekening / ID</label>
                                    <input type="text" class="form-control @error('account_number') is-invalid @enderror" wire:model="account_number" placeholder="e.g. 1234567890">
                                    @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Atas Nama</label>
                                    <input type="text" class="form-control @error('account_name') is-invalid @enderror" wire:model="account_name" placeholder="e.g. John Doe">
                                    @error('account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3" wire:ignore>
                            <label class="form-label">Instruksi Pembayaran</label>
                            <textarea id="payment-method-instructions" class="form-control @error('instructions') is-invalid @enderror" rows="3" wire:model="instructions" placeholder="Jelaskan cara melakukan pembayaran..."></textarea>
                            @error('instructions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" wire:model="is_active">
                                <span class="form-check-label">Status Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            Simpan Metode Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        let editorInstance;

        const initEditor = () => {
            if (typeof ClassicEditor === 'undefined') return;

            ClassicEditor
                .create(document.querySelector('#payment-method-instructions'), {
                    toolbar: [ 'undo', 'redo', '|', 'heading', '|', 'bold', 'italic', '|', 'bulletedList', 'numberedList', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'mediaEmbed', 'help' ]
                })
                .then(editor => {
                    editorInstance = editor;

                    // Set initial content
                    editor.setData($wire.instructions || '');

                    // Sync with Livewire on change
                    editor.model.document.on('change:data', () => {
                        $wire.instructions = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        };

        // Initialize on first load
        initEditor();

        // Re-initialize or update content when modal opens
        $wire.on('isModalOpenChanged', () => {
            if ($wire.isModalOpen) {
                setTimeout(() => {
                    if (editorInstance) {
                        editorInstance.setData($wire.instructions || '');
                    } else {
                        initEditor();
                    }
                }, 100);
            }
        });

        // Cleanup on component destroy
        return () => {
            if (editorInstance) {
                editorInstance.destroy()
                    .then(() => editorInstance = null)
                    .catch(error => console.error(error));
            }
        }
    </script>
    @endscript
</div>
