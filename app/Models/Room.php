<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id','room_type_id','number','status','floor','amenities'
    ];

    protected $casts = [
        'floor'     => 'integer',
        'amenities' => 'array',
    ];

    public function location(){ return $this->belongsTo(Location::class); }
    public function roomType(){ return $this->belongsTo(RoomType::class); }

    public function bookings(){ return $this->hasMany(Booking::class); }
    public function stays(){ return $this->hasMany(Stay::class); }
    public function tickets(){ return $this->hasMany(Ticket::class); }

    /* Helper */
    public function scopeAvailable($q){ return $q->where('status','available'); }
}
