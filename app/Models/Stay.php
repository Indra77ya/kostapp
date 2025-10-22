<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stay extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id','room_id','checkin_date','checkout_date','billing_cycle','rate','status'
    ];

    protected $casts = [
        'checkin_date'  => 'date',
        'checkout_date' => 'date',
        'rate'          => 'decimal:2',
    ];

    public function tenant(){ return $this->belongsTo(Tenant::class); }
    public function room(){ return $this->belongsTo(Room::class); }
    public function invoices(){ return $this->hasMany(Invoice::class); }

    public function scopeActive($q){ return $q->where('status','active'); }
}
