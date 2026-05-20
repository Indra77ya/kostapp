<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'bill_id',
        'payment_method_id',
        'payment_number',
        'payment_date',
        'amount',
        'proof_of_payment',
        'notes',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
