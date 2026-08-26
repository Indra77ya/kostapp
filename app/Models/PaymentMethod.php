<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'chart_of_account_id',
        'account_number',
        'account_name',
        'instructions',
        'logo',
        'is_active',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
