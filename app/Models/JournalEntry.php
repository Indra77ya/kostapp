<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = ['date','ref','memo'];

    protected $casts = [
        'date' => 'date',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    public function getDebitAttribute()
    {
        return $this->lines()->sum('debit');
    }

    public function getCreditAttribute()
    {
        return $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return bccomp((string)$this->debit, (string)$this->credit, 2) === 0;
    }
}
