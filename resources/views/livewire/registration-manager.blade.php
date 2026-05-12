<div>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">Manajemen Check In</h2>
        </div>
        <div class="col-auto ms-auto">
            <button class="btn btn-primary" wire:click="openModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Check In Baru
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" class="form-control" placeholder="Cari nama, email, no. registrasi..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterLocation">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text small">Tgl Daftar:</span>
                        <input type="date" class="form-control" wire:model.live="filterDateStart">
                        <span class="input-group-text small">-</span>
                        <input type="date" class="form-control" wire:model.live="filterDateEnd">
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-icon w-100" title="Reset Filter" wire:click="resetFilters">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-rotate" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.95 11a8 8 0 1 0 -.5 4m.5 5v-5h-5" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Registrasi</th>
                        <th>Penghuni</th>
                        <th>Lokasi / Kamar</th>
                        <th>Tgl Daftar</th>
                        <th>Tgl Mulai Inap</th>
                        <th>Total Harga</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    <tr>
                        <td><code>{{ $reg->registration_number }}</code></td>
                        <td>
                            <div class="font-weight-medium">{{ $reg->user->name }}</div>
                            <div class="text-secondary small">{{ $reg->user->email }}</div>
                        </td>
                        <td>
                            <div>{{ $reg->location->name }}</div>
                            <div class="text-secondary small">Kamar {{ $reg->room->room_number }}</div>
                        </td>
                        <td>{{ $reg->registration_date->format('d M Y') }}</td>
                        <td>{{ $reg->stay_start_date->format('d M Y') }}</td>
                        <td>Rp {{ number_format($reg->total_price, 0, ',', '.') }}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <a href="{{ route('registrations.invoice', $reg->id) }}" target="_blank" class="btn btn-white btn-sm" title="Cetak Data Diri">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                </a>
                                <button class="btn btn-white btn-sm" wire:click="openModal({{ $reg->id }})">Edit</button>
                                <button class="btn btn-white btn-sm text-danger" wire:click="deleteRegistration({{ $reg->id }})" wire:confirm="Yakin ingin menghapus data check in ini?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Tidak ada pendaftaran ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $registrations->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div class="modal modal-blur fade {{ $isModalOpen ? 'show d-block' : '' }}" tabindex="-1" role="dialog" aria-hidden="true" style="{{ $isModalOpen ? 'background: rgba(0,0,0,0.5)' : '' }}">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">{{ $registrationId ? 'Edit Check In' : 'Check In Penghuni Baru' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal()"></button>
                </div>
                <div class="modal-body pt-0">
                    <form wire:submit.prevent="saveRegistration">
                        <div class="row">
                            <!-- Section 1: Data Kamar & Registrasi -->
                            <div class="col-md-12 mb-3 mt-4">
                                <div class="hr-text text-uppercase fw-bold text-muted small">Data Kamar & Registrasi</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required small fw-bold">Lokasi</label>
                                <select class="form-select @error('location_id') is-invalid @enderror" wire:model.live="location_id">
                                    <option value="">Pilih Lokasi</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required small fw-bold">Kamar</label>
                                <select class="form-select @error('room_id') is-invalid @enderror" wire:model.live="room_id">
                                    <option value="">Pilih Kamar</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->room_number }} ({{ $room->room_type ?? $room->type ?? 'Room' }})</option>
                                    @endforeach
                                </select>
                                @error('room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-muted">No. Registrasi (Auto-generated)</label>
                                <input type="text" class="form-control bg-light" wire:model="registration_number" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold text-muted">Harga Kamar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">Rp</span>
                                    <input type="text" class="form-control bg-light" wire:model="room_price" readonly>
                                </div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label small fw-bold text-muted">Fasilitas Kamar</label>
                                <input type="text" class="form-control bg-light" wire:model="room_facilities" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold">Tipe Diskon</label>
                                <select class="form-select" wire:model.live="discount_type">
                                    <option value="fixed">Fixed Price (Rp)</option>
                                    <option value="percent">Persentase (%)</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold">Nilai Diskon</label>
                                <input type="number" class="form-control" wire:model.live="discount_value">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold text-muted">Total Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">Rp</span>
                                    <input type="text" class="form-control fw-bold bg-light" value="{{ number_format($total_price, 0, ',', '.') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label required small fw-bold">Tgl Daftar</label>
                                <input type="date" class="form-control @error('registration_date') is-invalid @enderror" wire:model="registration_date">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label required small fw-bold">Tgl Mulai Inap</label>
                                <input type="date" class="form-control @error('stay_start_date') is-invalid @enderror" wire:model="stay_start_date">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label required small fw-bold">Jenis Sewa</label>
                                <select class="form-select" wire:model.live="duration_type">
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label required small fw-bold">Durasi</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model.live="duration_value" min="1">
                                    <span class="input-group-text">
                                        @if($duration_type == 'daily') Hari
                                        @elseif($duration_type == 'weekly') Minggu
                                        @elseif($duration_type == 'monthly') Bulan
                                        @elseif($duration_type == 'yearly') Tahun
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Section 2: Data Penghuni -->
                            <div class="col-md-12 mb-3 mt-4">
                                <div class="hr-text text-uppercase fw-bold text-muted small">Data Diri Penghuni</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required small fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required small fw-bold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">No. Telepon</label>
                                <input type="text" class="form-control" wire:model="phone_number">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Jenis Kelamin</label>
                                <select class="form-select" wire:model="gender">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Tipe Identitas</label>
                                <select class="form-select" wire:model="identity_type">
                                    <option value="KTP">KTP</option>
                                    <option value="SIM">SIM</option>
                                    <option value="NIM">NIM (Mahasiswa)</option>
                                    <option value="Paspor">Paspor</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required small fw-bold">No. Identitas</label>
                                <input type="text" class="form-control @error('identity_number') is-invalid @enderror" wire:model="identity_number">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Tempat Lahir</label>
                                <input type="text" class="form-control" wire:model="birth_place">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required small fw-bold">Tanggal Lahir</label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" wire:model="birth_date">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label small fw-bold">Alamat Lengkap</label>
                                <textarea class="form-control" rows="2" wire:model="address"></textarea>
                            </div>

                            <!-- Section 3: Foto & Dokumen -->
                            <div class="col-md-12 mb-3 mt-4">
                                <div class="hr-text text-uppercase fw-bold text-muted small">Foto & Dokumen</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required small fw-bold">Foto Diri</label>
                                <input type="file" class="form-control @error('photo_self') is-invalid @enderror" wire:model="photo_self">
                                @if($photo_self && method_exists($photo_self, 'temporaryUrl'))
                                    <div class="mt-2">
                                        <a href="{{ $photo_self->temporaryUrl() }}" target="_blank">
                                            <img src="{{ $photo_self->temporaryUrl() }}" style="height: 100px;" class="border rounded" title="Klik untuk memperbesar (Tab Baru)">
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required small fw-bold">Foto Identitas</label>
                                <input type="file" class="form-control @error('photo_identity') is-invalid @enderror" wire:model="photo_identity">
                                @if($photo_identity && method_exists($photo_identity, 'temporaryUrl'))
                                    <div class="mt-2">
                                        <a href="{{ $photo_identity->temporaryUrl() }}" target="_blank">
                                            <img src="{{ $photo_identity->temporaryUrl() }}" style="height: 100px;" class="border rounded" title="Klik untuk memperbesar (Tab Baru)">
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Foto Kartu Keluarga</label>
                                <input type="file" class="form-control" wire:model="photo_family_card">
                                @if($photo_family_card && method_exists($photo_family_card, 'temporaryUrl'))
                                    <div class="mt-2">
                                        <a href="{{ $photo_family_card->temporaryUrl() }}" target="_blank">
                                            <img src="{{ $photo_family_card->temporaryUrl() }}" style="height: 100px;" class="border rounded" title="Klik untuk memperbesar (Tab Baru)">
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">No. Kartu Keluarga</label>
                                <input type="text" class="form-control" wire:model="family_card_number">
                            </div>

                            <!-- Section 4: Data Instansi -->
                            <div class="col-md-12 mb-3 mt-4">
                                <div class="hr-text text-uppercase fw-bold text-muted small">Data Instansi (Sekolah/Kampus/Kantor)</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Nama Instansi</label>
                                <input type="text" class="form-control" wire:model="institution_name">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">No. Telp Instansi</label>
                                <input type="text" class="form-control" wire:model="institution_phone">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Alamat Instansi</label>
                                <textarea class="form-control" rows="1" wire:model="institution_address"></textarea>
                            </div>

                            <!-- Section 5: Kontak Darurat -->
                            <div class="col-md-12 mb-3 mt-4 d-flex justify-content-between align-items-center">
                                <div class="hr-text text-uppercase fw-bold text-muted small flex-grow-1">Orang yang Bisa Dihubungi (Kontak Darurat) - Opsional</div>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-3" wire:click="addEmergencyContact">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                                    Tambah Kontak
                                </button>
                            </div>
                            @foreach($emergency_contacts as $index => $contact)
                            <div class="col-md-12 mb-3 border p-3 rounded position-relative bg-surface">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2" wire:click="removeEmergencyContact({{ $index }})"></button>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required small fw-bold">Nama Lengkap</label>
                                        <input type="text" class="form-control" wire:model="emergency_contacts.{{ $index }}.name">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required small fw-bold">Hubungan</label>
                                        <input type="text" class="form-control" placeholder="Ayah, Ibu, Kakak, dll" wire:model="emergency_contacts.{{ $index }}.relationship">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required small fw-bold">No. Telepon</label>
                                        <input type="text" class="form-control" wire:model="emergency_contacts.{{ $index }}.phone_number">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">No. Identitas</label>
                                        <input type="text" class="form-control" wire:model="emergency_contacts.{{ $index }}.identity_number">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Email</label>
                                        <input type="email" class="form-control" wire:model="emergency_contacts.{{ $index }}.email">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Jenis Kelamin</label>
                                        <select class="form-select" wire:model="emergency_contacts.{{ $index }}.gender">
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Tempat Lahir</label>
                                        <input type="text" class="form-control" wire:model="emergency_contacts.{{ $index }}.birth_place">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Tanggal Lahir</label>
                                        <input type="date" class="form-control" wire:model="emergency_contacts.{{ $index }}.birth_date">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Alamat</label>
                                        <textarea class="form-control" rows="1" wire:model="emergency_contacts.{{ $index }}.address"></textarea>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="modal-footer px-0 pb-0 mt-3">
                            <button type="button" class="btn btn-link link-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto" wire:loading.attr="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                                <span wire:loading.remove>Simpan Check In</span>
                                <span wire:loading>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
