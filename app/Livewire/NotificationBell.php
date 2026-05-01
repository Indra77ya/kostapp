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
        array_unshift($this->notifications, [
            'message' => $event['message'],
            'time' => now()->diffForHumans(),
            'type' => $event['type'] ?? 'info'
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
