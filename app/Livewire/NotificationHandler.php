<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationHandler extends Component
{
    protected $listeners = ['echo:notifications,NotificationSent' => 'handleNotification'];

    public function handleNotification($event)
    {
        $this->dispatch('notification-received', $event);
    }

    public function render()
    {
        return <<<'HTML'
            <div wire:ignore>
                <!-- Toasts Container -->
                <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060;">
                    <div id="notification-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <span id="toast-icon-container" class="me-2"></span>
                            <strong class="me-auto" id="toast-title">Notifikasi</strong>
                            <small class="text-secondary">Baru saja</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body" id="toast-message"></div>
                    </div>
                </div>

                <!-- Error Modal -->
                <div class="modal modal-blur fade" id="notification-error-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="modal-status bg-danger"></div>
                            <div class="modal-body text-center py-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                                <h3>Gagal!</h3>
                                <div class="text-secondary" id="error-modal-message"></div>
                            </div>
                            <div class="modal-footer">
                                <div class="w-100">
                                    <div class="row">
                                        <div class="col">
                                            <button href="#" class="btn btn-danger w-100" data-bs-dismiss="modal">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @script
                <script>
                    const toastEl = document.getElementById('notification-toast');
                    const errorModalEl = document.getElementById('notification-error-modal');

                    let toast = null;
                    let errorModal = null;

                    const initTabler = () => {
                        if (window.bootstrap) {
                            toast = new bootstrap.Toast(toastEl);
                            errorModal = new bootstrap.Modal(errorModalEl);
                        }
                    };

                    initTabler();

                    $wire.on('notification-received', (event) => {
                        const data = event[0];
                        const message = data.message;
                        const type = data.type || 'info';

                        if (type === 'error' || type === 'danger') {
                            document.getElementById('error-modal-message').innerText = message;
                            if (errorModal) errorModal.show();
                        } else {
                            document.getElementById('toast-message').innerText = message;

                            const titleEl = document.getElementById('toast-title');
                            const iconContainer = document.getElementById('toast-icon-container');

                            // Reset classes
                            toastEl.classList.remove('bg-success-lt', 'bg-info-lt', 'bg-warning-lt');

                            if (type === 'success') {
                                titleEl.innerText = 'Berhasil';
                                iconContainer.innerHTML = '<span class="status-dot status-dot-animated bg-success"></span>';
                                // toastEl.classList.add('bg-success-lt');
                            } else if (type === 'warning') {
                                titleEl.innerText = 'Peringatan';
                                iconContainer.innerHTML = '<span class="status-dot status-dot-animated bg-warning"></span>';
                                // toastEl.classList.add('bg-warning-lt');
                            } else {
                                titleEl.innerText = 'Informasi';
                                iconContainer.innerHTML = '<span class="status-dot status-dot-animated bg-info"></span>';
                                // toastEl.classList.add('bg-info-lt');
                            }

                            if (toast) toast.show();
                        }
                    });
                </script>
                @endscript
            </div>
        HTML;
    }
}
