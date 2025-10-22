<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id','room_id','subject','description','priority','status','assigned_to'
    ];

    public function tenant(){ return $this->belongsTo(Tenant::class); }
    public function room(){ return $this->belongsTo(Room::class); }
    public function assignee(){ return $this->belongsTo(\App\Models\User::class, 'assigned_to'); }
}
