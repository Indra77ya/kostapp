<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'payment_id',
        'amount',
        'type',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
