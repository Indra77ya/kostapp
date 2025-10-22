<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id','room_id','start_date','end_date','status','rate'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'rate'       => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /* Scope helper */
    public function scopeConfirmed($q){ return $q->where('status','confirmed'); }
    public function scopePending($q){ return $q->where('status','pending'); }
}
