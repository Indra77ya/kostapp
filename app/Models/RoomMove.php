<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomMove extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'user_id',
        'old_room_id',
        'new_room_id',
        'move_date',
        'reason',
    ];

    protected $casts = [
        'move_date' => 'date',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oldRoom()
    {
        return $this->belongsTo(Room::class, 'old_room_id');
    }

    public function newRoom()
    {
        return $this->belongsTo(Room::class, 'new_room_id');
    }
}
