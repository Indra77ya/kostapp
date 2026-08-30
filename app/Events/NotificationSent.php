<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $type;
    public $userId;
    public $hideInBell;
    public $url;

    public function __construct($message, $type = 'info', $userId = null, $hideInBell = false, $url = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->userId = $userId;
        $this->hideInBell = $hideInBell;
        $this->url = $url;

        if (!$hideInBell) {
            \App\Models\AppNotification::createForUser(
                userId: $this->userId,
                message: $this->message,
                type: $this->type,
                url: $this->url
            );
        }
    }

    public function broadcastOn(): array
    {
        if ($this->userId) {
            return [
                new PrivateChannel('App.Models.User.' . $this->userId),
            ];
        }

        return [
            new Channel('notifications'),
        ];
    }
}
