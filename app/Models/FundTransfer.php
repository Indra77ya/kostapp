<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'transfer_date',
        'from_account_id',
        'to_account_id',
        'amount',
        'admin_fee',
        'admin_fee_account_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'to_account_id');
    }

    public function adminFeeAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'admin_fee_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate unique transfer number (TRF-YYYYMMDD-XXXX)
     */
    public static function generateTransferNumber(?string $date = null): string
    {
        $dateObj = $date ? \Carbon\Carbon::parse($date) : now();
        $dateStr = $dateObj->format('Ymd');
        $prefix = "TRF-{$dateStr}-";

        $lastRecord = self::where('transfer_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord) {
            $lastSeq = (int) substr($lastRecord->transfer_number, -4);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }
}
