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

        // Handle Livewire events
        $wire.on('notification-received', (event) => {
            showNotification(event.message, event.type);
        });

        // Handle Session Flash
        const sessionData = @js($sessionData);
        if (sessionData) {
            showNotification(sessionData.message, sessionData.type);
        }
    </script>
    @endscript
</div>
