<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'google_maps_link',
        'phone',
        'description',
        'image',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
