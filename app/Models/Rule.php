<?php

namespace App\Models;

use App\Events\DatabaseUpdated;
use App\Events\NotificationSent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'location_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($rule) {
            DatabaseUpdated::dispatch();
        });

        static::updated(function ($rule) {
            DatabaseUpdated::dispatch();
        });

        static::deleted(function ($rule) {
            DatabaseUpdated::dispatch();
        });
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
