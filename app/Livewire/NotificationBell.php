<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;

    public function getListeners()
    {
        $userId = auth()->id();
        return [
            "echo:notifications,NotificationSent" => 'addNotification',
            "echo-private:App.Models.User.{$userId},NotificationSent" => 'addNotification',
            'notify' => 'addNotification'
        ];
    }

    public function addNotification($message = null, $type = 'info')
    {
        if (is_array($message)) {
            $type = $message['type'] ?? $type;
            $message = $message['message'] ?? '';
        }

        // Only store info and warning notifications in the bell
        // EXCEPTION: Allow success notifications for payment confirmations
        if (in_array($type, ['success', 'error', 'danger'])) {
            if ($type !== 'success' || !str_contains(strtolower($message), 'pembayaran')) {
                return;
            }
        }

        array_unshift($this->notifications, [
            'message' => $message,
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
