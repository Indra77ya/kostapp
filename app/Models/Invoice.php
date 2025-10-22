<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number','tenant_id','stay_id','issue_date','due_date',
        'subtotal','discount','tax','total','status'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'subtotal'   => 'decimal:2',
        'discount'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    /* Relations */
    public function tenant(){ return $this->belongsTo(Tenant::class); }
    public function stay(){ return $this->belongsTo(Stay::class); }
    public function payments(){ return $this->hasMany(Payment::class); }
    public function reminders(){ return $this->hasMany(Reminder::class); }

    /* Accessors */
    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAmountAttribute()
    {
        return (float) $this->total - (float) $this->paid_amount;
    }

    /* Scopes */
    public function scopeUnpaid($q){ return $q->whereIn('status',['unpaid','partial','overdue']); }
    public function scopeDueInDays($q, int $days = 3)
    {
        return $q->whereBetween('due_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()]);
    }
}
