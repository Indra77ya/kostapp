<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    protected $listeners = [
        'echo:notifications,NotificationSent' => 'addNotification',
        'notify' => 'addNotification'
    ];

    public function addNotification($data)
    {
        $event = is_array($data) ? $data : (array) $data;
        $type = $event['type'] ?? 'info';

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
