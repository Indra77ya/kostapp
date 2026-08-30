<div class="nav-item dropdown d-none d-md-flex me-3">
    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
        @if($unreadCount > 0)
            <span class="badge bg-red"></span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card" style="width: 380px; max-width: 90vw;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notifikasi Terbaru</h3>
                <div class="card-actions">
                    <a href="#" wire:click.prevent="clearNotifications">Hapus Semua</a>
                </div>
            </div>
            <div class="list-group list-group-flush list-group-hoverable" style="max-height: 400px; overflow-y: auto;">
                @forelse($notifications as $notification)
                    <div class="list-group-item {{ !$notification->is_read ? 'bg-light-subtle font-weight-bold' : '' }}"
                         wire:key="notification-{{ $notification->id }}"
                         @if($notification->url || !$notification->is_read)
                             wire:click="markAsRead({{ $notification->id }})"
                             style="cursor: pointer;"
                         @endif>
                        <div class="row align-items-start">
                            <div class="col-auto pt-1">
                                <span class="status-dot {{ !$notification->is_read ? 'status-dot-animated' : '' }} bg-{{ $notification->type }} d-block"></span>
                            </div>
                            <div class="col">
                                <div class="text-body d-block text-wrap text-break {{ !$notification->is_read ? 'fw-bold' : '' }}">
                                    {{ $notification->message }}
                                </div>
                                <div class="d-flex align-items-center justify-content-between text-secondary mt-1" style="font-size: 0.75rem;">
                                    <span class="timeago" data-timestamp="{{ $notification->created_at->toIso8601String() }}">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if($notification->url)
                                        <span class="text-primary text-decoration-underline ms-2">
                                            Lihat detail &rarr;
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item">
                        <div class="text-secondary text-center">Tidak ada notifikasi</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @script
    <script>
        const updateTimeago = () => {
            document.querySelectorAll('.timeago').forEach(el => {
                const timestamp = el.getAttribute('data-timestamp');
                if (!timestamp) return;

                const date = new Date(timestamp);
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                let text = '';
                if (diffInSeconds < 5) {
                    text = 'Just now';
                } else if (diffInSeconds < 60) {
                    text = `${diffInSeconds} second${diffInSeconds > 1 ? 's' : ''} ago`;
                } else if (diffInSeconds < 3600) {
                    const minutes = Math.floor(diffInSeconds / 60);
                    text = `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
                } else if (diffInSeconds < 86400) {
                    const hours = Math.floor(diffInSeconds / 3600);
                    text = `${hours} hour${hours > 1 ? 's' : ''} ago`;
                } else {
                    const days = Math.floor(diffInSeconds / 86400);
                    text = `${days} day${days > 1 ? 's' : ''} ago`;
                }

                el.innerText = text;
            });
        };

        // Initial call
        updateTimeago();

        // Update every 5 seconds
        const interval = setInterval(updateTimeago, 5000);

        // Cleanup on component destroy
        $wire.on('livewire:navigating', () => {
            clearInterval(interval);
        });
    </script>
    @endscript
</div>
