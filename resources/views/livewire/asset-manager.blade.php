<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">
                Manajemen Aset
            </h2>
            <div class="text-secondary mt-1">Kelola inventaris fisik barang, fasilitas, lokasi, dan perhitungan penyusutan aset.</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <button type="button" class="btn btn-primary" wire:click="openModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Tambah Aset
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Cari Aset</label>
                    <input type="text" class="form-control" placeholder="Cari kode, nama, kategori..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Lokasi</label>
                    <select class="form-select" wire:model.live="filterLocationId">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kondisi</label>
                    <select class="form-select" wire:model.live="filterCondition">
                        <option value="">Semua Kondisi</option>
                        <option value="Baik">Baik</option>
                        <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                        <option value="Rusak">Rusak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                        <option value="Afkir">Afkir / Scrap</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Asset List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th>Kode Aset</th>
                        <th>Nama Aset & Kategori</th>
                        <th>Penempatan</th>
                        <th>Tgl Beli</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Nilai Buku Saat Ini</th>
                        <th class="text-center">Kondisi</th>
                        <th class="text-center">Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $item)
                        <tr>
                            <td class="font-monospace fw-bold">{{ $item->code }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->name }}</div>
                                <div class="small text-secondary"><span class="badge bg-blue-lt">{{ $item->category }}</span></div>
                            </td>
                            <td>
                                @if($item->location)
                                    <div><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg> {{ $item->location->name }}</div>
                                @endif
                                @if($item->room)
                                    <div class="small text-secondary">Kamar: {{ $item->room->room_number }}</div>
                                @elseif(!$item->location)
                                    <span class="text-muted small">- Umum / Seluruh Area -</span>
                                @endif
                            </td>
                            <td>{{ $item->purchase_date ? $item->purchase_date->format('d/m/Y') : '-' }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($item->purchase_cost, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-primary">Rp {{ number_format($item->book_value, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($item->condition === 'Baik')
                                    <span class="badge bg-success-lt">Baik</span>
                                @elseif($item->condition === 'Perlu Perbaikan')
                                    <span class="badge bg-warning-lt">Perlu Perbaikan</span>
                                @else
                                    <span class="badge bg-danger-lt">Rusak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->status === 'Aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @elseif($item->status === 'Non-Aktif')
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @else
                                    <span class="badge bg-danger">Afkir</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <button class="btn btn-white btn-sm" wire:click="openDetailModal({{ $item->id }})" title="Detail & Riwayat">
                                        Detail
                                    </button>
                                    @if($item->useful_life_months > 0 && $item->status === 'Aktif')
                                        <button class="btn btn-white btn-sm text-primary" wire:click="openDepreciationModal({{ $item->id }})" title="Proses Penyusutan">
                                            Susutkan
                                        </button>
                                    @endif
                                    <button class="btn btn-white btn-sm" wire:click="openModal({{ $item->id }})">
                                        Edit
                                    </button>
                                    <button class="btn btn-white btn-sm text-danger" wire:click="delete({{ $item->id }})" wire:confirm="Apakah Anda yakin ingin menghapus data aset ini?">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Belum ada data aset yang tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $assets->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    <!-- Modal Form Asset (modal-xl) -->
    @if($isModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $assetId ? 'Edit Data Aset' : 'Tambah Aset Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal()"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row">
                                <!-- Data Utama -->
                                <div class="col-md-6 border-end pe-md-4">
                                    <h4 class="subheader mb-3 text-primary">Informasi Fisik & Penempatan Aset</h4>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label required">Kode Aset</label>
                                            <input type="text" class="form-control @error('code') is-invalid @enderror" wire:model="code" placeholder="AST-YYYYMMDD-XXXX">
                                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Tanggal Pembelian</label>
                                            <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" wire:model="purchase_date">
                                            @error('purchase_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label required">Nama Aset</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Contoh: AC Sharp 1 PK, Kasur Springbed Single">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label required">Kategori</label>
                                            <select class="form-select @error('category') is-invalid @enderror" wire:model.live="category">
                                                <option value="">-- Pilih Kategori --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                                @endforeach
                                                <option value="NEW">+ Tambah Kategori Baru</option>
                                            </select>
                                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        @if($category === 'NEW')
                                            <div class="col-md-6">
                                                <label class="form-label required">Nama Kategori Baru</label>
                                                <input type="text" class="form-control @error('customCategory') is-invalid @enderror" wire:model="customCategory" placeholder="Kategori baru...">
                                                @error('customCategory') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Lokasi Kost</label>
                                            <select class="form-select @error('location_id') is-invalid @enderror" wire:model.live="location_id">
                                                <option value="">-- Pilih Lokasi --</option>
                                                @foreach($locations as $loc)
                                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Kamar Spesifik (Opsional)</label>
                                            <select class="form-select @error('room_id') is-invalid @enderror" wire:model="room_id">
                                                <option value="">-- Tidak Terhubung ke Kamar --</option>
                                                @foreach($rooms as $rm)
                                                    <option value="{{ $rm->id }}">Kamar {{ $rm->room_number }}</option>
                                                @endforeach
                                            </select>
                                            @error('room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label required">Kondisi Fisik</label>
                                            <select class="form-select @error('condition') is-invalid @enderror" wire:model="condition">
                                                <option value="Baik">Baik</option>
                                                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                                                <option value="Rusak">Rusak</option>
                                            </select>
                                            @error('condition') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Status Aset</label>
                                            <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                                <option value="Aktif">Aktif</option>
                                                <option value="Non-Aktif">Non-Aktif</option>
                                                <option value="Afkir">Afkir / Scrap</option>
                                            </select>
                                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Keuangan & Depresiasi -->
                                <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                                    <h4 class="subheader mb-3 text-primary">Perhitungan Nilai & Integrasi Akuntansi</h4>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label required">Harga Beli / Nilai Perolehan</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" step="0.01" class="form-control @error('purchase_cost') is-invalid @enderror" wire:model="purchase_cost" placeholder="0">
                                            </div>
                                            @error('purchase_cost') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nilai Residu / Sisa (Opsional)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" step="0.01" class="form-control @error('salvage_value') is-invalid @enderror" wire:model="salvage_value" placeholder="0">
                                            </div>
                                            @error('salvage_value') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Masa Manfaat (Bulan)</label>
                                        <input type="number" class="form-control @error('useful_life_months') is-invalid @enderror" wire:model.live="useful_life_months" placeholder="Contoh: 36 bulan (3 tahun)">
                                        <div class="form-hint">Kosongkan jika aset ini tidak disusutkan.</div>
                                        @error('useful_life_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    @if($useful_life_months > 0 && $purchase_cost > 0)
                                        <div class="alert alert-info py-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Estimasi Penyusutan Bulanan:</span>
                                                <strong class="text-primary">
                                                    Rp {{ number_format(max(0, ($purchase_cost - $salvage_value) / $useful_life_months), 0, ',', '.') }} / bulan
                                                </strong>
                                            </div>
                                        </div>
                                    @endif

                                    <hr class="my-3">
                                    <div class="mb-2 text-secondary small fw-bold">Pemetaan Akun (COA) Jurnal Penyusutan</div>

                                    <div class="mb-2">
                                        <label class="form-label small">Akun Aset Tetap</label>
                                        <select class="form-select form-select-sm" wire:model="chart_of_account_id">
                                            <option value="">-- Pilih Akun Aset Tetap --</option>
                                            @foreach($assetAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small">Akun Akumulasi Penyusutan</label>
                                        <select class="form-select form-select-sm" wire:model="accumulated_depreciation_account_id">
                                            <option value="">-- Pilih Akun Akumulasi Penyusutan --</option>
                                            @foreach($assetAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small">Akun Beban Penyusutan</label>
                                        <select class="form-select form-select-sm" wire:model="depreciation_expense_account_id">
                                            <option value="">-- Pilih Akun Beban Penyusutan --</option>
                                            @foreach($expenseAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan Tambahan</label>
                                        <textarea class="form-control" wire:model="notes" rows="2" placeholder="Nomor serial, garansi, spesifikasi..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto">
                                {{ $assetId ? 'Simpan Perubahan' : 'Tambah Aset' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Detail Asset & Riwayat Penyusutan -->
    @if($isDetailModalOpen && $selectedAsset)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Aset: {{ $selectedAsset->name }} ({{ $selectedAsset->code }})</h5>
                        <button type="button" class="btn-close" wire:click="closeDetailModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr><td class="text-secondary">Kode Aset</td><td>: <strong>{{ $selectedAsset->code }}</strong></td></tr>
                                    <tr><td class="text-secondary">Nama Aset</td><td>: {{ $selectedAsset->name }}</td></tr>
                                    <tr><td class="text-secondary">Kategori</td><td>: <span class="badge bg-blue-lt">{{ $selectedAsset->category }}</span></td></tr>
                                    <tr><td class="text-secondary">Lokasi / Kamar</td><td>: {{ optional($selectedAsset->location)->name ?? '-' }} {{ $selectedAsset->room ? '(Kamar ' . $selectedAsset->room->room_number . ')' : '' }}</td></tr>
                                    <tr><td class="text-secondary">Tgl Beli</td><td>: {{ $selectedAsset->purchase_date ? $selectedAsset->purchase_date->format('d F Y') : '-' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr><td class="text-secondary">Harga Perolehan</td><td>: <strong>Rp {{ number_format($selectedAsset->purchase_cost, 0, ',', '.') }}</strong></td></tr>
                                    <tr><td class="text-secondary">Masa Manfaat</td><td>: {{ $selectedAsset->useful_life_months ? $selectedAsset->useful_life_months . ' Bulan' : 'Tidak disusutkan' }}</td></tr>
                                    <tr><td class="text-secondary">Penyusutan / Bln</td><td>: Rp {{ number_format($selectedAsset->monthly_depreciation, 0, ',', '.') }}</td></tr>
                                    <tr><td class="text-secondary">Total Terakumulasi</td><td>: <span class="text-danger fw-bold">Rp {{ number_format($selectedAsset->total_accumulated_depreciation, 0, ',', '.') }}</span></td></tr>
                                    <tr><td class="text-secondary">Nilai Buku Saat Ini</td><td>: <span class="text-primary fw-bold">Rp {{ number_format($selectedAsset->book_value, 0, ',', '.') }}</span></td></tr>
                                </table>
                            </div>
                        </div>

                        <h4 class="subheader mb-2">Riwayat Log Penyusutan Bulanan</h4>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-vcenter table-sm">
                                <thead>
                                    <tr>
                                        <th>Periode</th>
                                        <th class="text-end">Jumlah Penyusutan</th>
                                        <th>No. Jurnal Akuntansi</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedAsset->depreciations as $dep)
                                        <tr>
                                            <td>{{ $dep->period_date ? $dep->period_date->format('M Y') : '-' }}</td>
                                            <td class="text-end text-danger fw-bold">Rp {{ number_format($dep->depreciation_amount, 0, ',', '.') }}</td>
                                            <td>
                                                @if($dep->journalEntry)
                                                    <span class="badge bg-blue-lt">{{ $dep->journalEntry->entry_number }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="small text-secondary">{{ $dep->notes ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Belum ada catatan penyusutan yang diproses.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" wire:click="closeDetailModal()">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Process Depreciation -->
    @if($isDepreciationModalOpen && $selectedAsset)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Proses Penyusutan Bulanan</h5>
                        <button type="button" class="btn-close" wire:click="closeDepreciationModal()"></button>
                    </div>
                    <form wire:submit.prevent="processDepreciation">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Aset</label>
                                <input type="text" class="form-control" value="{{ $selectedAsset->name }} ({{ $selectedAsset->code }})" disabled>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Periode Bulan Penyusutan</label>
                                    <input type="date" class="form-control @error('depreciation_period_date') is-invalid @enderror" wire:model="depreciation_period_date">
                                    @error('depreciation_period_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Nominal Penyusutan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="0.01" class="form-control @error('depreciation_amount') is-invalid @enderror" wire:model="depreciation_amount">
                                    </div>
                                    @error('depreciation_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan Jurnal</label>
                                <input type="text" class="form-control @error('depreciation_notes') is-invalid @enderror" wire:model="depreciation_notes">
                                @error('depreciation_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="alert alert-warning small mb-0">
                                <strong>Catatan:</strong> Memproses penyusutan ini akan otomatis membuat entri Jurnal Umum:
                                <ul class="mb-0 ps-3 mt-1">
                                    <li><strong>Debet:</strong> Beban Penyusutan ({{ optional($selectedAsset->depreciationExpenseAccount)->code ?? '5-7000' }})</li>
                                    <li><strong>Kredit:</strong> Akumulasi Penyusutan ({{ optional($selectedAsset->accumulatedDepreciationAccount)->code ?? '1-7900' }})</li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" wire:click="closeDepreciationModal()">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto">Proses & Buat Jurnal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
