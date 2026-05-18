<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationHandler extends Component
{
    protected $listeners = ['echo:notifications,NotificationSent' => 'handleNotification'];

    public function handleNotification($event)
    {
        $this->dispatch('notification-received', message: $event['message'], type: $event['type'] ?? 'info');
    }

    public function render()
    {
        $sessionData = null;
        if (session()->has('success')) {
            $sessionData = ['message' => session('success'), 'type' => 'success'];
        } elseif (session()->has('error')) {
            $sessionData = ['message' => session('error'), 'type' => 'error'];
        } elseif (session()->has('info')) {
            $sessionData = ['message' => session('info'), 'type' => 'info'];
        } elseif (session()->has('warning')) {
            $sessionData = ['message' => session('warning'), 'type' => 'warning'];
        }

        return view('livewire.notification-handler', [
            'sessionData' => $sessionData
        ]);
    }
}
