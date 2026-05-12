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
        'price',
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
            NotificationSent::dispatch("Kamar baru #{$room->room_number} telah ditambahkan.", 'success');
        });

        static::updated(function ($room) {
            DatabaseUpdated::dispatch();
            NotificationSent::dispatch("Kamar #{$room->room_number} telah diperbarui.", 'info');
        });

        static::deleted(function ($room) {
            DatabaseUpdated::dispatch();
            NotificationSent::dispatch("Kamar #{$room->room_number} telah dihapus.", 'warning');
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
