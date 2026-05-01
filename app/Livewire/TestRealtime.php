<?php

namespace App\Livewire;

use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use App\Models\Room;
use Livewire\Component;

class TestRealtime extends Component
{
    public function triggerUpdate()
    {
        // Event ini sekarang juga dikirim otomatis oleh Model observer
        DatabaseUpdated::dispatch();
        NotificationSent::dispatch('Manual trigger: Data dashboard diperbarui!', 'info');
    }

    public function addRoom()
    {
        // Room::create akan memicu event di model secara otomatis
        Room::create([
            'room_number' => 'R' . rand(100, 999),
            'price' => rand(1000000, 2000000),
            'status' => 'available'
        ]);
    }

    public function render()
    {
        return view('livewire.test-realtime');
    }
}
