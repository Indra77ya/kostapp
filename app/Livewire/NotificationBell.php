<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    protected $listeners = ['echo:notifications,NotificationSent' => 'addNotification'];

    public function addNotification($event)
    {
        $type = $event['type'] ?? 'info';

        // Filter out success and error/danger from notification bell history
        if (in_array($type, ['success', 'error', 'danger'])) {
            return;
        }

        array_unshift($this->notifications, [
            'message' => $event['message'],
            'timestamp' => now()->toIso8601String(),
            'type' => $type
        ]);
        $this->unreadCount++;
    }

    public function clearNotifications()
    {
        $this->notifications = [];
        $this->unreadCount = 0;
    }

    public function render()
    {
        return view('livewire/notification-bell');
    }
}
