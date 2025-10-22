<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['code','name','type'];

    protected $casts = [
        'code' => 'string',
        'type' => 'string', // asset|liability|equity|revenue|expense
    ];

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }
}
