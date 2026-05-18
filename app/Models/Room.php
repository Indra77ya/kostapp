<?php

namespace App\Models;

use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'room_number',
        'price_monthly',
        'price_daily',
        'price_weekly',
        'price_yearly',
        'status',
        'description',
        'image',
        'facilities',
        'room_type',
        'floor',
    ];

    protected static function booted()
    {
        static::created(function ($room) {
            DatabaseUpdated::dispatch();
        });

        static::updated(function ($room) {
            DatabaseUpdated::dispatch();
        });

        static::deleted(function ($room) {
            DatabaseUpdated::dispatch();
        });
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }
}
