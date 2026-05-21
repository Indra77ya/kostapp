<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabaseUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    public function broadcastOn(): array
    {
        $channels = [new Channel('stats')];

        if ($this->userId) {
            $channels[] = new PrivateChannel('App.Models.User.' . $this->userId);
        }

        return $channels;
    }
}
