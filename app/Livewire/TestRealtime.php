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
        // Simulate a database change
        DatabaseUpdated::dispatch();
        NotificationSent::dispatch('Data dashboard telah diperbarui!', 'success');
    }

    public function addRoom()
    {
        Room::create([
            'room_number' => 'R' . rand(100, 999),
            'price' => rand(1000000, 2000000),
            'status' => 'available'
        ]);

        DatabaseUpdated::dispatch();
        NotificationSent::dispatch('Kamar baru telah ditambahkan!', 'info');
    }

    public function render()
    {
        return view('livewire.test-realtime');
    }
}
