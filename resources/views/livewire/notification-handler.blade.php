<div wire:ignore>
    @script
    <script>
        const showNotification = (message, type) => {
            if (type === 'error' || type === 'danger') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: message,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#d63939'
                });
            } else {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: type,
                    title: message
                });
            }
        };

        // Handle Global Livewire events (for faster response)
        Livewire.on('notify', (data) => {
            const payload = Array.isArray(data) ? data[0] : data;
            showNotification(payload.message, payload.type || 'info');
        });

        // Handle Session Flash and generic browser events
        window.addEventListener('DOMContentLoaded', () => {
            @if(session('success')) showNotification(@json(session('success')), 'success'); @endif
            @if(session('error')) showNotification(@json(session('error')), 'error'); @endif
            @if(session('info')) showNotification(@json(session('info')), 'info'); @endif
            @if(session('warning')) showNotification(@json(session('warning')), 'warning'); @endif

            // Handle Echo for toasts
            if (window.Echo) {
                window.Echo.channel('notifications')
                    .listen('NotificationSent', (e) => {
                        showNotification(e.message, e.type);
                    });

                @if(auth()->check())
                window.Echo.private("App.Models.User.{{ auth()->id() }}")
                    .listen('NotificationSent', (e) => {
                        showNotification(e.message, e.type);
                    });
                @endif
            }
        });
    </script>
    @endscript
</div>
