<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'unit',
        'rate_per_unit',
        'is_active',
    ];

    protected $casts = [
        'rate_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(UtilityReading::class);
    }
}
