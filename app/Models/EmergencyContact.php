<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'name',
        'relationship',
        'identity_number',
        'phone_number',
        'email',
        'gender',
        'birth_place',
        'birth_date',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
