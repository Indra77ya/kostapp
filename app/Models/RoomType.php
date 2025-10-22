<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = ['name','billing_cycle','base_price'];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
