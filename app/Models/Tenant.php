<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name','phone','email','national_id','notes'
    ];

    public function bookings(){ return $this->hasMany(Booking::class); }
    public function stays(){ return $this->hasMany(Stay::class); }
    public function invoices(){ return $this->hasMany(Invoice::class); }
    public function tickets(){ return $this->hasMany(Ticket::class); }
}
