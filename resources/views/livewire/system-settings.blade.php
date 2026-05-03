<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                </div>
                <div>{{ session('success') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <!-- Statistik Sistem -->
    <div class="card mb-3 shadow-sm border-0 bg-primary text-primary-fg">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="card-title mb-1">Status Data Saat Ini</h3>
                    <div class="text-secondary-fg small">
                        Gunakan data ini untuk memverifikasi apakah proses restore berhasil mengubah jumlah data sistem.
                    </div>
                </div>
                <div class="col-auto">
                    <div class="row g-2 text-center">
                        <div class="col-auto">
                            <div class="h3 mb-0">{{ $stats['users'] }}</div>
                            <div class="small">User</div>
                        </div>
                        <div class="col-auto border-start ps-3 ms-3">
                            <div class="h3 mb-0">{{ $stats['locations'] }}</div>
                            <div class="small">Lokasi</div>
                        </div>
                        <div class="col-auto border-start ps-3 ms-3">
                            <div class="h3 mb-0">{{ $stats['rooms'] }}</div>
                            <div class="small">Kamar</div>
                        </div>
                        <div class="col-auto border-start ps-3 ms-3">
                            <div class="h3 mb-0">{{ $stats['facilities'] }}</div>
                            <div class="small">Fasilitas</div>
                        </div>
                        <div class="col-auto border-start ps-3 ms-3">
                            <div class="h3 mb-0">{{ $stats['rules'] }}</div>
                            <div class="small">Peraturan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                </div>
                <div>{{ session('error') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <!-- Backup & Restore Database -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-header bg-white">
            <h3 class="card-title">Backup & Restore Sistem</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-end">
                    <h4 class="fw-bold mb-1">Backup Sistem</h4>
                    <p class="text-secondary small mb-3">Unduh salinan data sistem lengkap (database dan file media) dalam format ZIP. Gunakan fitur ini secara berkala untuk mengamankan data Anda.</p>
                    <button wire:click="downloadBackup" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 11l5 5l5 -5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                        Download Backup (.zip)
                    </button>
                </div>
                <div class="col-md-6 ps-md-4">
                    <h4 class="fw-bold mb-1">Restore Sistem</h4>
                    <p class="text-danger small mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle inline-block me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                            <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path>
                        </svg>
                        <strong>PERINGATAN:</strong> Proses ini akan <strong>MENIMPA SELURUH DATA</strong> sistem dengan data dari file backup. Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="mb-3">
                        <input type="file" wire:model="backupFile" class="form-control @error('backupFile') is-invalid @enderror">
                        @error('backupFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button wire:click="restore" class="btn btn-outline-danger" wire:loading.attr="disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 9l5 -5l5 5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                        Upload & Restore (.zip)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Sistem -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h3 class="card-title text-danger">Reset Sistem</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning border-0 shadow-none mb-0" style="background-color: #fff9e6;">
                <div class="d-flex">
                    <div class="me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                            <path d="M12 8v4"></path>
                            <path d="M12 16h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-1"><strong>PERINGATAN:</strong> Tindakan ini akan menghapus data operasional secara permanen. Pastikan Anda telah memiliki backup sebelum melanjutkan. Sistem akan melakukan backup otomatis ke folder storage sebelum reset dijalankan.</p>
                        <button wire:click="confirmReset" class="btn btn-danger btn-sm mt-2">Reset Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($confirmingReset)
    <div class="modal modal-blur fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" wire:click="cancelReset"></button>
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                        <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path>
                    </svg>
                    <h3>Apakah Anda yakin?</h3>
                    <div class="text-secondary">Semua data operasional akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.</div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button class="btn w-100" wire:click="cancelReset">
                                    Batal
                                </button>
                            </div>
                            <div class="col">
                                <button class="btn btn-danger w-100" wire:click="resetSystem">
                                    Ya, Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
