<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','code','address','type','default_room_quota','wa_group_link'
    ];

    protected $casts = [
        'default_room_quota' => 'integer',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
