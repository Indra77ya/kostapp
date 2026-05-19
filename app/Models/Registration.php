<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'room_id',
        'registration_number',
        'registration_date',
        'stay_start_date',
        'duration_type',
        'duration_value',
        'is_open_ended',
        'room_price',
        'discount_type',
        'discount_value',
        'total_price',
        'identity_type',
        'identity_number',
        'gender',
        'birth_place',
        'birth_date',
        'photo_self',
        'photo_identity',
        'family_card_number',
        'photo_family_card',
        'institution_name',
        'institution_address',
        'institution_phone',
        'status',
        'check_out_date',
        'check_out_notes',
    ];

    protected $casts = [
        'registration_date' => 'date:Y-m-d',
        'stay_start_date' => 'date:Y-m-d',
        'birth_date' => 'date:Y-m-d',
        'check_out_date' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
