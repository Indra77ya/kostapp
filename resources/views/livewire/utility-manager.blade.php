<div class="container-xl">
    <!-- Page Header -->
    <div class="row mb-3 align-items-center">
        <div class="col">
            <div class="page-pretitle">Manajemen Utilitas & Meteran</div>
            <h2 class="page-title">Pencatatan & Tarif Utilitas (Listrik & Air)</h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                @if($activeTab === 'readings')
                    <button class="btn btn-primary d-none d-sm-inline-block" wire:click="openReadingModal">
                        + Catat Meteran Baru
                    </button>
                @else
                    <button class="btn btn-primary d-none d-sm-inline-block" wire:click="openTariffModal">
                        + Tambah Tarif Utilitas
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-3">
        <div class="btn-group w-100 w-sm-auto">
            <button type="button" class="btn {{ $activeTab === 'readings' ? 'btn-primary' : 'btn-white' }}" wire:click="setTab('readings')">
                Pencatatan Meteran Kamar
            </button>
            <button type="button" class="btn {{ $activeTab === 'tariffs' ? 'btn-primary' : 'btn-white' }}" wire:click="setTab('tariffs')">
                Pengaturan Tarif per Lokasi
            </button>
        </div>
    </div>

    @if($activeTab === 'readings')
        <!-- Filter Controls -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Filter Lokasi</label>
                        <select class="form-select" wire:model.live="filterLocation">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Bulan Periode</label>
                        <select class="form-select" wire:model.live="filterMonth">
                            <option value="">Semua Bulan</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ sprintf('%02d', $m) }}">{{ \Carbon\Carbon::createFromDate(null, $m, 1)->locale('id')->isoFormat('MMMM') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Tahun</label>
                        <select class="form-select" wire:model.live="filterYear">
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Cari Kamar</label>
                        <input type="text" class="form-control" placeholder="Nomor Kamar..." wire:model.live.debounce.300ms="searchRoom">
                    </div>
                </div>
            </div>
        </div>

        <!-- Readings Table -->
        <div class="card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th>Kamar & Lokasi</th>
                            <th>Penghuni</th>
                            <th>Utilitas</th>
                            <th>Periode</th>
                            <th class="text-end">Meter Awal &rarr; Akhir</th>
                            <th class="text-end">Pemakaian</th>
                            <th class="text-end">Total Biaya</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($readings as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold">Kamar {{ $item->room->room_number ?? '-' }}</div>
                                    <div class="text-muted small">{{ $item->location->name ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($item->registration && $item->registration->user)
                                        <div class="fw-semibold">{{ $item->registration->user->name }}</div>
                                        <span class="badge bg-green-lt text-uppercase" style="font-size: 10px;">Aktif</span>
                                    @else
                                        <span class="text-muted fst-italic">Kosong</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-blue-lt">
                                        {{ $item->utilityType->name ?? 'Utilitas' }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::createFromDate($item->period_year, $item->period_month, 1)->locale('id')->isoFormat('MMMM YYYY') }}</div>
                                    <div class="text-muted small">Catat: {{ $item->reading_date ? $item->reading_date->format('d/m/Y') : '-' }}</div>
                                </td>
                                <td class="text-end font-monospace">
                                    {{ number_format($item->previous_reading, 2, ',', '.') }} &rarr; {{ number_format($item->current_reading, 2, ',', '.') }}
                                </td>
                                <td class="text-end font-monospace fw-bold">
                                    <div>{{ number_format($item->usage_amount, 2, ',', '.') }} {{ $item->utilityType->unit ?? '' }}</div>
                                    <div class="text-muted small font-weight-normal">@ Rp {{ number_format($item->rate_per_unit, 0, ',', '.') }}</div>
                                </td>
                                <td class="text-end font-monospace fw-bold text-primary">
                                    Rp {{ number_format($item->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        @if($item->bill)
                                            @if($item->bill->status === 'Lunas')
                                                <span class="badge bg-success-lt">Lunas</span>
                                            @elseif($item->bill->status === 'Cicilan')
                                                <span class="badge bg-info-lt">Cicilan</span>
                                            @else
                                                <span class="badge bg-primary-lt">Terbit Tagihan</span>
                                            @endif
                                        @elseif($item->status === 'billed')
                                            <span class="badge bg-primary-lt">Terbit Tagihan</span>
                                        @else
                                            <span class="badge bg-warning-lt">Draft</span>
                                        @endif

                                        @if($item->image_path)
                                            <a href="{{ Storage::url($item->image_path) }}" target="_blank" class="badge bg-info-lt text-decoration-none">
                                                Lihat Bukti
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-list flex-nowrap justify-content-end">
                                        @if($item->bill)
                                            @php
                                                $latestPayment = $item->bill->payments->where('status', '!=', 'Ditolak')->last();
                                            @endphp

                                            @if($latestPayment)
                                                <a href="{{ route('payments.invoice', $latestPayment->id) }}" target="_blank" class="btn btn-white btn-icon btn-sm text-primary" title="Cetak Kuitansi Pembayaran" data-bs-toggle="tooltip">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                                </a>
                                            @else
                                                <a href="{{ route('bills.invoice', $item->bill->id) }}" target="_blank" class="btn btn-white btn-icon btn-sm text-secondary" title="Cetak Invoice Tagihan" data-bs-toggle="tooltip">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                                </a>
                                            @endif
                                        @elseif($item->registration_id)
                                            <button class="btn btn-white btn-sm text-success" title="Terbitkan Tagihan" wire:click="generateBillForReading({{ $item->id }})">
                                                Terbitkan Tagihan
                                            </button>
                                        @endif

                                        <button class="btn btn-white btn-sm" wire:click="openReadingModal({{ $item->id }})" title="Edit">
                                            Edit
                                        </button>
                                        <button class="btn btn-white btn-sm text-danger" onclick="confirm('Apakah Anda yakin ingin menghapus catatan meteran ini?') || event.stopImmediatePropagation()" wire:click="deleteReading({{ $item->id }})" title="Hapus">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    Belum ada catatan meteran utilitas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($readings->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $readings->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    @else
        <!-- Tariffs View -->
        <div class="card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th>Nama Utilitas</th>
                            <th>Satuan</th>
                            <th class="text-end">Tarif / Unit</th>
                            <th>Status</th>
                            <th class="text-end" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tariffs as $t)
                            <tr>
                                <td class="fw-bold">{{ $t->location->name ?? '-' }}</td>
                                <td>{{ $t->name }}</td>
                                <td><span class="badge bg-secondary-lt">{{ $t->unit }}</span></td>
                                <td class="text-end font-monospace fw-bold">Rp {{ number_format($t->rate_per_unit, 0, ',', '.') }}</td>
                                <td>
                                    @if($t->is_active)
                                        <span class="badge bg-success-lt">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-lt">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-list flex-nowrap justify-content-end">
                                        <button class="btn btn-white btn-sm" wire:click="openTariffModal({{ $t->id }})">Edit</button>
                                        <button class="btn btn-white btn-sm text-danger" onclick="confirm('Yakin ingin menghapus tarif ini?') || event.stopImmediatePropagation()" wire:click="deleteTariff({{ $t->id }})">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada pengaturan tarif utilitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tariffs->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $tariffs->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    @endif

    <!-- Tariff Modal -->
    @if($isTariffModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $tariffId ? 'Edit Tarif Utilitas' : 'Tambah Tarif Utilitas' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeTariffModal"></button>
                    </div>
                    <form wire:submit.prevent="saveTariff">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Lokasi</label>
                                <select class="form-select @error('tariff_location_id') is-invalid @enderror" wire:model="tariff_location_id">
                                    <option value="">-- Pilih Lokasi --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                @error('tariff_location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Nama Utilitas</label>
                                <input type="text" class="form-control @error('tariff_name') is-invalid @enderror" placeholder="Contoh: Listrik PLN, Air PAM" wire:model="tariff_name">
                                @error('tariff_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-2">
                                <div class="col-6 mb-3">
                                    <label class="form-label required">Satuan</label>
                                    <input type="text" class="form-control @error('tariff_unit') is-invalid @enderror" placeholder="kWh, m3, Unit" wire:model="tariff_unit">
                                    @error('tariff_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label required">Tarif / Unit (Rp)</label>
                                    <input type="number" step="0.01" class="form-control @error('tariff_rate_per_unit') is-invalid @enderror" placeholder="1800" wire:model="tariff_rate_per_unit">
                                    @error('tariff_rate_per_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="tariff_is_active">
                                    <span class="form-check-label">Status Aktif</span>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeTariffModal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto">Simpan Tarif</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Reading Modal -->
    @if($isReadingModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $readingId ? 'Edit Catatan Meteran' : 'Input Catatan Meteran Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="saveReading">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Lokasi</label>
                                    <select class="form-select @error('reading_location_id') is-invalid @enderror" wire:model.live="reading_location_id">
                                        <option value="">-- Pilih Lokasi --</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('reading_location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Kamar</label>
                                    <select class="form-select @error('reading_room_id') is-invalid @enderror" wire:model.live="reading_room_id">
                                        <option value="">-- Pilih Kamar --</option>
                                        @foreach($modalRooms as $rm)
                                            @php
                                                $activeReg = $rm->registrations->first();
                                                $tenantName = $activeReg && $activeReg->user ? $activeReg->user->name : null;
                                            @endphp
                                            <option value="{{ $rm->id }}">
                                                Kamar {{ $rm->room_number }} {{ $tenantName ? "({$tenantName})" : '(Kosong)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('reading_room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Jenis Utilitas</label>
                                    <select class="form-select @error('reading_utility_type_id') is-invalid @enderror" wire:model.live="reading_utility_type_id">
                                        <option value="">-- Pilih Utilitas --</option>
                                        @foreach($modalUtilityTypes as $ut)
                                            <option value="{{ $ut->id }}">{{ $ut->name }} (Rp {{ number_format($ut->rate_per_unit, 0, ',', '.') }}/{{ $ut->unit }})</option>
                                        @endforeach
                                    </select>
                                    @error('reading_utility_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Tanggal Pencatatan</label>
                                    <input type="date" class="form-control @error('reading_date') is-invalid @enderror" wire:model="reading_date">
                                    @error('reading_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Bulan Periode</label>
                                    <select class="form-select @error('period_month') is-invalid @enderror" wire:model="period_month">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(null, $m, 1)->locale('id')->isoFormat('MMMM') }}</option>
                                        @endfor
                                    </select>
                                    @error('period_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Tahun Periode</label>
                                    <select class="form-select @error('period_year') is-invalid @enderror" wire:model="period_year">
                                        @for($y = date('Y'); $y >= 2024; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                    @error('period_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Angka Meteran Awal</label>
                                    <input type="number" step="0.01" class="form-control @error('previous_reading') is-invalid @enderror" wire:model.live="previous_reading">
                                    @error('previous_reading') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Angka Meteran Akhir</label>
                                    <input type="number" step="0.01" class="form-control @error('current_reading') is-invalid @enderror" wire:model.live="current_reading">
                                    @error('current_reading') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required">Tarif per Unit (Rp)</label>
                                    <input type="number" step="0.01" class="form-control @error('rate_per_unit') is-invalid @enderror" wire:model.live="rate_per_unit">
                                    @error('rate_per_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Summary Card Inside Modal -->
                                <div class="col-12">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <div class="row text-center g-2">
                                                <div class="col-md-6">
                                                    <div class="text-muted small">Total Pemakaian</div>
                                                    <div class="h3 mb-0 font-monospace text-primary">{{ number_format($usage_amount, 2, ',', '.') }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-muted small">Total Biaya Tagihan</div>
                                                    <div class="h3 mb-0 font-monospace text-success">Rp {{ number_format($total_amount, 0, ',', '.') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Foto Bukti Meteran</label>
                                    <input type="file" class="form-control @error('reading_image') is-invalid @enderror" wire:model="reading_image" accept="image/*">
                                    @error('reading_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($existing_image_path)
                                        <div class="mt-2 small text-muted">
                                            Foto saat ini: <a href="{{ Storage::url($existing_image_path) }}" target="_blank">Lihat Bukti Foto</a>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Catatan Tambahan</label>
                                    <input type="text" class="form-control" placeholder="Opsional" wire:model="notes">
                                </div>

                                @if(!$readingId)
                                    <div class="col-12">
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model="auto_generate_bill" {{ !$this->activeTenant ? 'disabled' : '' }}>
                                            <span class="form-check-label fw-semibold">
                                                Otomatis buat & terbitkan data Tagihan (Bill) ke penghuni aktif:
                                                @if($this->activeTenant)
                                                    <span class="badge bg-blue-lt ms-1">{{ $this->activeTenant->name }}</span>
                                                @else
                                                    <span class="text-danger small ms-1">(Kamar tidak ada penghuni aktif)</span>
                                                @endif
                                            </span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto">Simpan Catatan Meteran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
