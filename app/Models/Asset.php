<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'location_id',
        'room_id',
        'purchase_date',
        'purchase_cost',
        'purchase_source_type',
        'payment_method_id',
        'purchase_journal_entry_id',
        'condition',
        'status',
        'useful_life_months',
        'salvage_value',
        'chart_of_account_id',
        'accumulated_depreciation_account_id',
        'depreciation_expense_account_id',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'useful_life_months' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function purchaseJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'purchase_journal_entry_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'depreciation_expense_account_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    /**
     * Calculate monthly depreciation amount using straight-line method.
     */
    public function getMonthlyDepreciationAttribute(): float
    {
        if (!$this->useful_life_months || $this->useful_life_months <= 0) {
            return 0;
        }

        $depreciableAmount = max(0, $this->purchase_cost - $this->salvage_value);
        return round($depreciableAmount / $this->useful_life_months, 2);
    }

    /**
     * Get total accumulated depreciation so far.
     */
    public function getTotalAccumulatedDepreciationAttribute(): float
    {
        return (float) $this->depreciations()->sum('depreciation_amount');
    }

    /**
     * Get net book value of the asset.
     */
    public function getBookValueAttribute(): float
    {
        return max(0, $this->purchase_cost - $this->total_accumulated_depreciation);
    }
}
