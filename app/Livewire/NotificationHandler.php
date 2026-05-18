<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationHandler extends Component
{
    protected $listeners = ['echo:notifications,NotificationSent' => 'handleNotification'];

    public function handleNotification($event)
    {
        $this->dispatch('notify', message: $event['message'], type: $event['type'] ?? 'info');
    }

    public function render()
    {
        return view('livewire.notification-handler');
    }
}
