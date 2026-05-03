<div>
    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-status-top bg-blue"></div>
                <div class="card-body">
                    <h3 class="card-title">Backup Sistem</h3>
                    <p class="text-secondary">Unduh salinan data sistem termasuk database dan file media (foto lokasi & kamar).</p>
                </div>
                <div class="card-footer">
                    <button wire:click="downloadBackup" class="btn btn-primary w-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 11l5 5l5 -5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                        Unduh Backup
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-status-top bg-green"></div>
                <div class="card-body">
                    <h3 class="card-title">Restore Sistem</h3>
                    <p class="text-secondary">Pulihkan data sistem dari file backup (.zip) yang telah diunduh sebelumnya.</p>
                    <div class="mb-3">
                        <input type="file" wire:model="backupFile" class="form-control @error('backupFile') is-invalid @enderror">
                        @error('backupFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button wire:click="restore" class="btn btn-success w-100" wire:loading.attr="disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 9l5 -5l5 5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                        Restore Data
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-status-top bg-danger"></div>
                <div class="card-body">
                    <h3 class="card-title text-danger">Reset Sistem</h3>
                    <p class="text-secondary">Hapus semua data operasional sistem. Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="card-footer">
                    <button wire:click="confirmReset" class="btn btn-danger w-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 7l16 0"></path>
                            <path d="M10 11l0 6"></path>
                            <path d="M14 11l0 6"></path>
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                        </svg>
                        Reset Sekarang
                    </button>
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
                    <div class="text-secondary">Semua data akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.</div>
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
