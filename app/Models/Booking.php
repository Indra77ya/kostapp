<?php

namespace App\Models;

use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
    ];

    protected static function booted()
    {
        static::created(function ($booking) {
            DatabaseUpdated::dispatch();
            NotificationSent::dispatch("Pemesanan baru dibuat oleh {$booking->user->name}.", 'success');
        });

        static::updated(function ($booking) {
            DatabaseUpdated::dispatch();
            NotificationSent::dispatch("Status pemesanan #{$booking->id} berubah menjadi {$booking->status}.", 'info');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
