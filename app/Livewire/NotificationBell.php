<?php

namespace App\Livewire;

use App\Models\AppNotification;
use Livewire\Component;

class NotificationBell extends Component
{
    public function getListeners()
    {
        $userId = auth()->id();
        return [
            "echo:notifications,NotificationSent" => '$refresh',
            "echo-private:App.Models.User.{$userId},NotificationSent" => '$refresh',
            'notify' => 'addNotification'
        ];
    }

    public function addNotification($message = null, $type = 'info', $hideInBell = false, $url = null, $userId = null)
    {
        if (is_array($message)) {
            $hideInBell = $message['hideInBell'] ?? $hideInBell;
            $type = $message['type'] ?? $type;
            $url = $message['url'] ?? $url;
            $userId = $message['userId'] ?? $userId;
            $message = $message['message'] ?? '';
        }

        if ($hideInBell) {
            return;
        }

        $targetUserId = $userId ?: auth()->id();

        AppNotification::createForUser(
            userId: $targetUserId,
            message: $message,
            type: $type,
            url: $url
        );
    }

    public function markAsRead($notificationId)
    {
        $notification = AppNotification::where('user_id', auth()->id())
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);

            if ($notification->url) {
                return redirect()->to($notification->url);
            }
        }
    }

    public function clearNotifications()
    {
        if (auth()->check()) {
            AppNotification::where('user_id', auth()->id())->delete();
        }
    }

    public function render()
    {
        $userId = auth()->id();

        $notifications = auth()->check()
            ? AppNotification::where('user_id', $userId)
                ->latest()
                ->take(100)
                ->get()
            : collect();

        $unreadCount = auth()->check()
            ? AppNotification::where('user_id', $userId)
                ->where('is_read', false)
                ->count()
            : 0;

        return view('livewire/notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
