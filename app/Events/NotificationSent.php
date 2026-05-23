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

    public function __construct($message, $type = 'info', $userId = null, $hideInBell = false)
    {
        $this->message = $message;
        $this->type = $type;
        $this->userId = $userId;
        $this->hideInBell = $hideInBell;
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
