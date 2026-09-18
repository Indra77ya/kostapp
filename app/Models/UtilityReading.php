<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'room_id',
        'registration_id',
        'utility_type_id',
        'reading_date',
        'period_month',
        'period_year',
        'previous_reading',
        'current_reading',
        'usage_amount',
        'rate_per_unit',
        'total_amount',
        'image_path',
        'status',
        'bill_id',
        'notes',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'period_month' => 'integer',
        'period_year' => 'integer',
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'usage_amount' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function utilityType(): BelongsTo
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
